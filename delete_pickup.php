<?php
session_start();

$is_logged_in = isset($_SESSION['user_id']);
$is_employee = $is_logged_in && $_SESSION['role'] === 'employee'; // Проверка, что пользователь — сотрудник

if (!$is_logged_in || !$is_employee) {
    header('Location: login.php');
    exit();
}

// Подключаемся к базе данных
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pickup_id'])) {
    $pickup_id = (int)$_POST['pickup_id'];

    // Удаление пункта выдачи
    $delete_sql = "DELETE FROM pickup_points WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param('i', $pickup_id);

    if ($delete_stmt->execute()) {
        $_SESSION['notification'] = "Пункт выдачи успешно удалён.";
    } else {
        $_SESSION['notification'] = "Ошибка при удалении пункта выдачи: " . $delete_stmt->error;
    }

    $delete_stmt->close();
    header('Location: home_employee.php'); // Перенаправление обратно на главную страницу
    exit();
} else {
    echo "Некорректный запрос.";
    exit();
}
