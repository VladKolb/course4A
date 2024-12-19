<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['notification'] = "У вас нет прав для редактирования пунктов выдачи.";
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pickup_point_id = $_POST['pickup_point_id'] ?? null;
    $name = $_POST['name'] ?? null;
    $address = $_POST['address'] ?? null;

    if ($pickup_point_id && $name && $address) {
        // Обновляем пункт выдачи в базе данных
        $sql = "UPDATE pickup_points SET name = ?, address = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssi', $name, $address, $pickup_point_id);

        if ($stmt->execute()) {
            $_SESSION['notification'] = "Пункт выдачи успешно обновлен.";
        } else {
            $_SESSION['notification'] = "Ошибка при обновлении пункта выдачи.";
        }

        $stmt->close();
    } else {
        $_SESSION['notification'] = "Все поля обязательны для заполнения.";
    }

    // Перенаправление на главную страницу после сохранения
    header("Location: index.php");
    exit();
}
?>
