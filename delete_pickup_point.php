<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['notification'] = "У вас нет прав для удаления пунктов выдачи.";
    header("Location: index.php");
    exit();
}

$pickup_point_id = $_GET['id'] ?? null;

if ($pickup_point_id) {
    // Удаляем пункт выдачи из базы данных
    $sql = "DELETE FROM pickup_points WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $pickup_point_id);

    if ($stmt->execute()) {
        $_SESSION['notification'] = "Пункт выдачи успешно удалён.";
    } else {
        $_SESSION['notification'] = "Ошибка при удалении пункта выдачи.";
    }

    $stmt->close();
} else {
    $_SESSION['notification'] = "ID пункта выдачи не передан.";
}

// Перенаправление на главную страницу после удаления
header("Location: index.php");
exit();
?>
