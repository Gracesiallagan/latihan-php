<?php
require_once(__DIR__ . '/../config.php');

class TodoModel
{
    private $conn;

    public function __construct()
    {
        // === CONNECT POSTGRES ===
        $this->conn = pg_connect(
            "host=" . DB_HOST .
            " port=" . DB_PORT .
            " dbname=" . DB_NAME .
            " user=" . DB_USER .
            " password=" . DB_PASSWORD
        );

        if (!$this->conn) {
            die('Koneksi database gagal: ' . pg_last_error());
        }
    }

    // ======================================================
    // GET TODOS (FILTER + SEARCH)
    // ======================================================
    public function getTodos($filter = 'all', $search = '')
    {
        $where = [];
        $params = [];
        $paramIndex = 1;

        // PERBAIKAN: Filter done/pending dengan kondisi yang benar
        if ($filter === 'done') {
            $where[] = "is_finished = TRUE";
        } elseif ($filter === 'pending') {
            $where[] = "is_finished = FALSE";
        }
        // Jika 'all', tidak perlu WHERE untuk is_finished

        // PERBAIKAN: Search mencari di judul, deskripsi, DAN status
        if (!empty($search)) {
            $searchLower = strtolower(trim($search));
            
            // Cek apakah user mencari berdasarkan status
            $statusSearch = "";
            if (strpos($searchLower, 'selesai') !== false || strpos($searchLower, 'done') !== false) {
                $statusSearch = " OR is_finished = TRUE";
            } elseif (strpos($searchLower, 'belum') !== false || strpos($searchLower, 'pending') !== false) {
                $statusSearch = " OR is_finished = FALSE";
            }
            
            $where[] = "(LOWER(title) LIKE LOWER($" . $paramIndex . ") OR LOWER(description) LIKE LOWER($" . $paramIndex . ")" . $statusSearch . ")";
            $params[] = '%' . $search . '%';
            $paramIndex++;
        }

        $whereSQL = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

        $query = "
            SELECT *
            FROM todo
            $whereSQL
            ORDER BY sort_order ASC, id ASC
        ";

        $result = pg_query_params($this->conn, $query, $params);
        
        if (!$result) {
            return [];
        }

        $data = pg_fetch_all($result);
        return $data ? $data : [];
    }

    // ======================================================
    // TITLE EXISTS (FOR CREATE) - Case Insensitive
    // ======================================================
    public function existsTitle($title)
    {
        $query = "SELECT COUNT(*) as count FROM todo WHERE LOWER(TRIM(title)) = LOWER(TRIM($1))";
        $result = pg_query_params($this->conn, $query, [$title]);
        
        if (!$result) {
            return false;
        }
        
        $row = pg_fetch_assoc($result);
        return (int)$row['count'] > 0;
    }

    // ======================================================
    // TITLE EXISTS EXCEPT SELF (FOR UPDATE) - Case Insensitive
    // ======================================================
    public function existsTitleExcept($title, $id)
    {
        $query = "
            SELECT COUNT(*) as count
            FROM todo
            WHERE LOWER(TRIM(title)) = LOWER(TRIM($1)) AND id != $2
        ";
        $result = pg_query_params($this->conn, $query, [$title, $id]);
        
        if (!$result) {
            return false;
        }
        
        $row = pg_fetch_assoc($result);
        return (int)$row['count'] > 0;
    }

    // ======================================================
    // CREATE TODO
    // ======================================================
    public function createTodo($title, $description)
    {
        // validasi duplikat judul
        if ($this->existsTitle($title)) {
            return false;
        }

        // Tentukan urutan baru
        $maxOrderRes = pg_query($this->conn, "SELECT COALESCE(MAX(sort_order), 0) + 1 as next_order FROM todo");
        $nextOrder = $maxOrderRes ? pg_fetch_result($maxOrderRes, 0, 0) : 1;

        $query = "
            INSERT INTO todo (title, description, sort_order, is_finished, created_at, updated_at)
            VALUES ($1, $2, $3, FALSE, NOW(), NOW())
        ";

        return pg_query_params($this->conn, $query, [
            trim($title),
            trim($description),
            $nextOrder
        ]) !== false;
    }

    // ======================================================
    // UPDATE TODO
    // ======================================================
    public function updateTodo($id, $title, $description, $is_finished)
    {
        // validasi judul unik kecuali dirinya
        if ($this->existsTitleExcept($title, $id)) {
            return false;
        }

        $is_finished = ($is_finished == 1 || $is_finished === true || $is_finished === 't') ? 'TRUE' : 'FALSE';

        $query = "
            UPDATE todo
            SET title = $1,
                description = $2,
                is_finished = $3,
                updated_at = NOW()
            WHERE id = $4
        ";

        return pg_query_params($this->conn, $query, [
            trim($title),
            trim($description),
            $is_finished,
            $id
        ]) !== false;
    }

    // ======================================================
    // TOGGLE STATUS
    // ======================================================
    public function toggleStatus($id)
    {
        $todo = $this->getTodoById($id);
        if (!$todo) return false;

        $current = ($todo['is_finished'] === 't' || $todo['is_finished'] == 1 || $todo['is_finished'] === true);
        $newStatus = $current ? 'FALSE' : 'TRUE';

        $query = "
            UPDATE todo
            SET is_finished = $1,
                updated_at = NOW()
            WHERE id = $2
        ";

        return pg_query_params($this->conn, $query, [$newStatus, $id]) !== false;
    }

    // ======================================================
    // DELETE TODO
    // ======================================================
    public function deleteTodo($id)
    {
        return pg_query_params(
            $this->conn,
            "DELETE FROM todo WHERE id = $1",
            [$id]
        ) !== false;
    }

    // ======================================================
    // REORDER TODOS
    // ======================================================
    public function updateOrder($orders)
    {
        pg_query($this->conn, "BEGIN");

        $num = 1;
        foreach ($orders as $id) {
            pg_query_params(
                $this->conn,
                "UPDATE todo SET sort_order = $1 WHERE id = $2",
                [$num++, $id]
            );
        }

        pg_query($this->conn, "COMMIT");
        return true;
    }

    // ======================================================
    // GET SINGLE TODO
    // ======================================================
    public function getTodoById($id)
    {
        $result = pg_query_params(
            $this->conn,
            "SELECT * FROM todo WHERE id = $1 LIMIT 1",
            [$id]
        );

        return ($result && pg_num_rows($result) > 0)
            ? pg_fetch_assoc($result)
            : null;
    }
}
?>