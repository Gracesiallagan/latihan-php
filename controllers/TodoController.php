<?php
require_once(__DIR__ . '/../models/TodoModel.php');

class TodoController
{
    private $model;

    public function __construct()
    {
        $this->model = new TodoModel();
    }

    // ============================================================
    // INDEX – tampilkan daftar todo + filter + search
    // ============================================================
    public function index()
    {
        $filter = $_GET['filter'] ?? 'all';
        $search = $_GET['search'] ?? '';

        $todos = $this->model->getTodos($filter, $search);
        include(__DIR__ . '/../views/TodoView.php');
    }

    // ============================================================
    // CREATE – tambah baru
    // ============================================================
    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');

            // Validasi judul tidak boleh kosong
            if (empty($title)) {
                header('Location: index.php?msg=empty');
                exit;
            }

            // Cek duplikat judul
            if ($this->model->existsTitle($title)) {
                header('Location: index.php?msg=duplicate');
                exit;
            }

            $success = $this->model->createTodo($title, $description);

            header('Location: index.php?msg=' . ($success ? 'added' : 'error'));
            exit;
        }

        header('Location: index.php');
        exit;
    }

    // ============================================================
    // UPDATE – mendukung form biasa atau AJAX
    // ============================================================
    public function update()
    {
        // === AJAX ===
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {

            header('Content-Type: application/json');
            $data = json_decode(file_get_contents("php://input"), true);

            $id = $data['id'] ?? null;
            $title = trim($data['title'] ?? '');
            $description = trim($data['description'] ?? '');
            $is_finished = $data['is_finished'] ?? 0;

            if (empty($id) || $title === '') {
                echo json_encode(['success' => false, 'message' => 'Invalid input']);
                exit;
            }

            // Validasi duplikat judul (kecuali untuk todo yang sedang diedit)
            if ($this->model->existsTitleExcept($title, $id)) {
                echo json_encode(['success' => false, 'message' => 'Judul sudah digunakan!']);
                exit;
            }

            $this->model->updateTodo($id, $title, $description, $is_finished);
            $todo = $this->model->getTodoById($id);

            echo json_encode([
                'success' => true,
                'todo' => $todo
            ]);
            exit;
        }

        // === FORM BIASA ===
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $id = $_POST['id'] ?? null;
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $is_finished = $_POST['is_finished'] ?? 0;

            // Validasi duplikat judul
            if ($this->model->existsTitleExcept($title, $id)) {
                header('Location: index.php?msg=duplicate');
                exit;
            }

            $this->model->updateTodo($id, $title, $description, $is_finished);
        }

        header('Location: index.php');
        exit;
    }

    // ============================================================
    // TOGGLE
    // ============================================================
    public function toggle()
    {
        if (isset($_GET['id'])) {
            $this->model->toggleStatus((int)$_GET['id']);
        }
        header("Location: index.php");
        exit;
    }

    // ============================================================
    // DELETE
    // ============================================================
    public function delete()
    {
        if (isset($_GET['id'])) {
            $this->model->deleteTodo((int)$_GET['id']);
        }
        header('Location: index.php');
        exit;
    }

    // ============================================================
    // REORDER DRAG & DROP
    // ============================================================
    public function reorder()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            header('Content-Type: application/json');
            $data = json_decode(file_get_contents('php://input'), true);

            if (!empty($data['order']) && is_array($data['order'])) {
                $this->model->updateOrder($data['order']);
                echo json_encode(['status' => 'ok']);
                exit;
            }

            http_response_code(400);
            echo json_encode(['status' => 'bad_request']);
            exit;
        }

        http_response_code(405);
        echo json_encode(['status' => 'method_not_allowed']);
        exit;
    }

    // ============================================================
    // DETAIL PAGE
    // ============================================================
    public function detail()
    {
        if (!isset($_GET['id'])) {
            header('Location: index.php');
            exit;
        }

        $todo = $this->model->getTodoById((int)$_GET['id']);
        include(__DIR__ . '/../views/TodoDetailView.php');
        exit;
    }
}
?>