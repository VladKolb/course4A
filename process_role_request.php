<?php
session_start();
include 'db_connect.php';

// Проверяем, является ли текущий пользователь администратором
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $action = $_POST['action'];

    if ($action === 'approve') {
        // Одобряем запрос и меняем роль на 'employee'
        $sql = "UPDATE users SET role = 'employee', role_request = 'none' WHERE id = ?";
    } elseif ($action === 'deny') {
        // Отклоняем запрос, просто сбрасываем статус запроса
        $sql = "UPDATE users SET role_request = 'none' WHERE id = ?";
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);

    if ($stmt->execute()) {
        $_SESSION['notification'] = $action === 'approve' ? "Роль успешно изменена." : "Запрос отклонен.";
    } else {
        $_SESSION['notification'] = "Ошибка при обработке запроса.";
    }

    $stmt->close();
}

header('Location: user_list.php'); // Перенаправляем обратно на страницу списка пользователей
exit();
?>
