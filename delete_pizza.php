<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

include 'db_connect.php';

if (isset($_POST['id'])) {
    $pizza_id = (int)$_POST['id']; 

    $check_sql = "SELECT * FROM pizza WHERE id = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param('i', $pizza_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $_SESSION['notification'] = "Пицца с таким ID не найдена.";
        header('Location: index.php');
        exit;
    }

    $delete_pizza_sql = "DELETE FROM pizza WHERE id = ?";
    $stmt = $conn->prepare($delete_pizza_sql);
    $stmt->bind_param('i', $pizza_id);

    if ($stmt->execute()) {
        $_SESSION['notification'] = "Пицца успешно удалена.";
    } else {
        $_SESSION['notification'] = "Ошибка при удалении пиццы.";
    }

    $stmt->close();
    $conn->close();
    header('Location: index.php');
    exit;
} else {
    $_SESSION['notification'] = "Некорректный запрос.";
    header('Location: index.php');
    exit;
}
?>