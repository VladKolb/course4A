<?php
session_start();
include 'db_connect.php'; // Подключение к БД

// Проверка, авторизован ли пользователь
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: index.php'); // Перенаправляем на главную страницу, если не обычный пользователь
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = $_POST['order_id'];
    $user_id = $_SESSION['user_id'];

    // Обновляем поле is_hidden для данного заказа
    $hide_sql = "UPDATE orders SET is_hidden = 1 WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($hide_sql);
    $stmt->bind_param('ii', $order_id, $user_id);
    $stmt->execute();

    $_SESSION['notification'] = 'Заказ удалён из истории.';
    header('Location: profile.php');
    exit();
}
?>
