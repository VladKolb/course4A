<?php
session_start();

// Проверка, что пользователь — админ
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

// Подключение к базе данных
include 'db_connect.php';

if (isset($_POST['user_id']) && isset($_POST['new_role'])) {
    $user_id = (int)$_POST['user_id']; // Приводим к целому числу для безопасности
    $new_role = $_POST['new_role']; // Получаем новую роль

    // Обновляем роль пользователя в базе данных
    $update_sql = "UPDATE users SET role = ? WHERE id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param('si', $new_role, $user_id);

    if ($stmt->execute()) {
        $_SESSION['notification'] = "Роль пользователя успешно изменена.";
    } else {
        $_SESSION['notification'] = "Ошибка при изменении роли пользователя.";
    }

    $stmt->close();
} else {
    $_SESSION['notification'] = "Некорректный запрос.";
}

header('Location: user_list.php'); // Вернуться на страницу со списком пользователей
exit();
?>
