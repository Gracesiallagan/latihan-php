<?php
require_once(__DIR__ . '/../config.php');

class TodoModel
{
    private $conn;

    public function __construct()
    {
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

    public function getTodos($filter = 'all', $search = '')
    {
        $where = [];
        $params = [];
        $paramIndex = 1;

        if ($filter === 'done') {
            $where[] = "is_finished = TRUE";
        } elseif ($filter === 'pending') {
            $where[] = "is_finished = FALSE";
        }

        if (!empty($search)) {
            $where[] = "LOWER(title) LIKE LOWER($" . $paramIndex . ")";
            $params[] = '%' . $search . '%';
            $paramIndex++;
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        $query = "SELECT * FROM todo $whereClause ORDER BY sort_order ASC, id ASC";

        $result = pg_query_params($this->conn, $query, $params);
        return $result ? pg_fetch_all($result) : [];
    }

    public function createTodo($title, $description)
    {
        $check = pg_query_params($this->conn, "SELECT COUNT(*) FROM todo WHERE LOWER(title)=LOWER($1)", [$title]);
        if (pg_fetch_result($check, 0, 0) > 0) {
            return false;
        }

        $maxOrderRes = pg_query($this->conn, "SELECT COALESCE(MAX(sort_order), 0) + 1 FROM todo");
        $nextOrder = $maxOrderRes ? pg_fetch_result($maxOrderRes, 0, 0) : 1;

        $query = "INSERT INTO todo (title, description, sort_order) VALUES ($1, $2, $3)";
        $result = pg_query_params($this->conn, $query, [$title, $description, $nextOrder]);
        return $result !== false;
    }

    public function updateTodo($id, $title, $description, $is_finished)
    {
        $is_finished = ($is_finished === '1' || $is_finished === 1 || $is_finished === true) ? 1 : 0;

        $query = "UPDATE todo 
                  SET title = $1, 
                      description = $2, 
                      is_finished = $3, 
                      updated_at = NOW() 
                  WHERE id = $4";
        return pg_query_params($this->conn, $query, [$title, $description, $is_finished, $id]) !== false;
    }

    public function toggleStatus($id)
    {
        $todo = $this->getTodoById($id);
        if (!$todo) return false;

        $current = ($todo['is_finished'] === 't' || $todo['is_finished'] == 1);
        $newStatus = $current ? 0 : 1;

        $query = "UPDATE todo SET is_finished = $1, updated_at = NOW() WHERE id = $2";
        return pg_query_params($this->conn, $query, [$newStatus, $id]) !== false;
    }

    public function deleteTodo($id)
    {
        return pg_query_params($this->conn, "DELETE FROM todo WHERE id = $1", [$id]) !== false;
    }

    public function updateOrder($orders)
    {
        pg_query($this->conn, "BEGIN");
        $orderNumber = 1;
        foreach ($orders as $id) {
            pg_query_params($this->conn,
                "UPDATE todo SET sort_order = $1 WHERE id = $2",
                [$orderNumber++, $id]);
        }
        pg_query($this->conn, "COMMIT");
        return true;
    }

    public function getTodoById($id)
    {
        $result = pg_query_params($this->conn, "SELECT * FROM todo WHERE id = $1 LIMIT 1", [$id]);
        return ($result && pg_num_rows($result) > 0) ? pg_fetch_assoc($result) : null;
    }
}
?>
