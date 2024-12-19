<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    echo "Вы должны быть авторизованы.";
    exit();
}

$user_id = $_SESSION['user_id'];

// Обновляем статус запроса на смену роли
$sql = "UPDATE users SET role_request = 'pending' WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);

if ($stmt->execute()) {
    $_SESSION['notification'] = "Ваш запрос на смену роли отправлен.";
} else {
    $_SESSION['notification'] = "Ошибка при отправке запроса.";
}

$stmt->close();
header('Location: profile.php');
exit();
?>
