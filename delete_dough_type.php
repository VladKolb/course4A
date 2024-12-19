<?php
session_start();

// Проверка, что пользователь — админ
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

// Подключаемся к базе данных
include 'db_connect.php';

// Проверяем, передан ли ID типа теста
if (isset($_GET['id'])) {
    $dough_id = (int)$_GET['id'];

    // Удаляем все записи в корзине с данным типом теста
    $delete_cart_sql = "DELETE FROM user_cart WHERE dough_type_id = ?";
    $delete_cart_stmt = $conn->prepare($delete_cart_sql);
    $delete_cart_stmt->bind_param('i', $dough_id);
    $delete_cart_stmt->execute();
    $delete_cart_stmt->close();

    // Удаляем все записи в заказах с данным типом теста
    $delete_order_items_sql = "DELETE FROM order_items WHERE dough_type_id = ?";
    $delete_order_stmt = $conn->prepare($delete_order_items_sql);
    $delete_order_stmt->bind_param('i', $dough_id);
    $delete_order_stmt->execute();
    $delete_order_stmt->close();

    // Удаляем тип теста
    $delete_sql = "DELETE FROM dough_types WHERE id = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param('i', $dough_id);

    if ($stmt->execute()) {
        $_SESSION['notification'] = "Тип теста успешно удален.";
    } else {
        $_SESSION['notification'] = "Ошибка при удалении типа теста.";
    }

    $stmt->close();
}

// Перенаправление обратно на страницу редактирования типов теста
header('Location: edit_dough_types.php');
exit;
?>
