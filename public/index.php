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

    case 'toggle': // ✅ diperbaiki: variabel sudah konsisten
        $todoController->toggle();
        break;

    case 'delete':
        $todoController->delete();
        break;

    case 'detail': // ✅ untuk fitur detail (bisa popup nanti)
        $todoController->detail();
        break;

    default:
        // jika page tidak dikenali, kembalikan ke index
        $todoController->index();
        break;
}
?>
