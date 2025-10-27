<?php
require_once(__DIR__ . '/../models/TodoModel.php');

class TodoController
{
    private $model;

    public function __construct()
    {
        $this->model = new TodoModel();
    }

    // 🏠 Menampilkan semua todo
    public function index()
    {
        $filter = $_GET['filter'] ?? 'all';
        $search = $_GET['search'] ?? '';
        $todos = $this->model->getTodos($filter, $search);
        include(__DIR__ . '/../views/TodoView.php');
    }

    // ➕ Tambah todo
    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $success = $this->model->createTodo($title, $description);
            header('Location: index.php?msg=' . ($success ? 'added' : 'duplicate'));
            exit;
        }
        header('Location: index.php');
        exit;
    }

    // ✏️ Update todo (form biasa atau AJAX)
    public function update()
    {
        // === Jika dari fetch() (AJAX) ===
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            header('Content-Type: application/json');

            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? null;
            $title = trim($data['title'] ?? '');
            $description = trim($data['description'] ?? '');
            $is_finished = isset($data['is_finished']) ? (int)$data['is_finished'] : 0;

            if (!empty($id) && $title !== '') {
                $updated = $this->model->updateTodo($id, $title, $description, $is_finished);
                $todo = $this->model->getTodoById($id);

                // Pastikan balikan JSON lengkap untuk JS
                echo json_encode([
                    'success' => true,
                    'todo' => [
                        'id' => $todo['id'],
                        'title' => $todo['title'],
                        'description' => $todo['description'],
                        'is_finished' => $todo['is_finished']
                    ]
                ]);
                exit;
            }

            echo json_encode(['success' => false, 'message' => 'Invalid input']);
            exit;
        }

        // === Jika dari form biasa (non-AJAX) ===
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? null;
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $is_finished = isset($_POST['is_finished']) ? (int)$_POST['is_finished'] : 0;

            if (!empty($id) && $title !== '') {
                $this->model->updateTodo($id, $title, $description, $is_finished);
            }
        }

        header('Location: index.php');
        exit;
    }

    // 🔁 Toggle status
    public function toggle()
    {
        if (isset($_GET['id'])) {
            $id = (int)$_GET['id'];
            $this->model->toggleStatus($id);
        }
        header("Location: index.php");
        exit;
    }

    // ❌ Hapus
    public function delete()
    {
        if (isset($_GET['id'])) {
            $id = (int)$_GET['id'];
            $this->model->deleteTodo($id);
        }
        header('Location: index.php');
        exit;
    }

    // 🔀 Reorder (drag & drop)
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

    // 🔍 Detail
    public function detail()
    {
        if (!isset($_GET['id'])) {
            header('Location: index.php');
            exit;
        }

        $id = (int)$_GET['id'];
        $todo = $this->model->getTodoById($id);
        include(__DIR__ . '/../views/TodoDetailView.php');
        exit;
    }
}
?>
