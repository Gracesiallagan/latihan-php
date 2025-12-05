<?php
// Menentukan halaman yang sedang diakses
$page = $_GET['page'] ?? 'index';

// Memanggil controller utama
include('../controllers/TodoController.php');

$todoController = new TodoController();

// Routing berdasarkan nilai $page
switch ($page) {
    case 'index':
        $todoController->index();
        break;

    case 'create':
        $todoController->create();
        break;

    case 'update':
        $todoController->update();
        break;

    case 'toggle':
        $todoController->toggle();
        break;

    case 'delete':
        $todoController->delete();
        break;

    case 'detail':
        $todoController->detail();
        break;

    case 'reorder':
        $todoController->reorder();
        break;

    default:
        $todoController->index();
        break;
}
?>
