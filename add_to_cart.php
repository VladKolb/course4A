<?php
session_start();

// Проверка, что пользователь авторизован
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Получаем данные из формы
$user_id = $_SESSION['user_id'];
$pizza_id = $_POST['pizza_id'];
$dough_type = $_POST['dough_type'];

// Подключаемся к базе данных
include 'db_connect.php';

// Проверяем, сколько всего пицц в корзине
$sql_total = "SELECT SUM(quantity) as total_quantity FROM user_cart WHERE user_id = ?";
$stmt_total = $conn->prepare($sql_total);
$stmt_total->bind_param('i', $user_id);
$stmt_total->execute();
$result_total = $stmt_total->get_result();
$total_quantity = $result_total->fetch_assoc()['total_quantity'] ?? 0;

// Проверяем, если общее количество пицц в корзине уже 30 или больше
if ($total_quantity >= 30) {
    // Устанавливаем уведомление, что больше нельзя добавлять пиццы
    $_SESSION['notification'] = 'Нельзя добавить больше 30 пицц в корзину!';
} else {
    // Проверяем, есть ли уже такая пицца с данным типом теста в корзине пользователя
    $sql = "SELECT * FROM user_cart WHERE user_id = ? AND pizza_id = ? AND dough_type_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iii', $user_id, $pizza_id, $dough_type);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Если такая пицца уже есть в корзине, увеличиваем количество
        $update_sql = "UPDATE user_cart SET quantity = quantity + 1 WHERE user_id = ? AND pizza_id = ? AND dough_type_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param('iii', $user_id, $pizza_id, $dough_type);
        $update_stmt->execute();
    } else {
        // Если такой пиццы нет, добавляем новую запись в корзину
        $insert_sql = "INSERT INTO user_cart (user_id, pizza_id, dough_type_id, quantity) VALUES (?, ?, ?, 1)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param('iii', $user_id, $pizza_id, $dough_type);
        $insert_stmt->execute();
    }

    // Устанавливаем уведомление, что пицца добавлена
    $_SESSION['notification'] = 'Пицца успешно добавлена в корзину!';
}

// Перенаправляем обратно на главную страницу
header('Location: index.php');
exit;
?>
