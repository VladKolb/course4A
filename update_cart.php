<?php
session_start();

// Проверяем, авторизован ли пользователь
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Подключаемся к базе данных
include 'db_connect.php';

// Получаем данные из формы
$user_id = $_SESSION['user_id'];
$pizza_id = $_POST['pizza_id'];
$dough_type = $_POST['dough_type'];
$new_quantity = $_POST['quantity'];

// Проверяем общее количество пицц в корзине
$sql_total = "SELECT SUM(quantity) as total_quantity FROM user_cart WHERE user_id = ?";
$stmt_total = $conn->prepare($sql_total);
$stmt_total->bind_param('i', $user_id);
$stmt_total->execute();
$result_total = $stmt_total->get_result();
$current_total_quantity = $result_total->fetch_assoc()['total_quantity'] ?? 0;

// Рассчитываем, какое будет общее количество после изменения
$sql_current_pizza = "SELECT quantity FROM user_cart WHERE user_id = ? AND pizza_id = ? AND dough_type_id = ?";
$stmt_current_pizza = $conn->prepare($sql_current_pizza);
$stmt_current_pizza->bind_param('iii', $user_id, $pizza_id, $dough_type);
$stmt_current_pizza->execute();
$current_pizza_quantity = $stmt_current_pizza->get_result()->fetch_assoc()['quantity'] ?? 0;

// Общее количество пицц после обновления
$new_total_quantity = $current_total_quantity - $current_pizza_quantity + $new_quantity;

// Проверяем, не превышает ли новое количество 30
if ($new_total_quantity > 30) {
    $_SESSION['notification'] = 'Общее количество пицц в корзине не может превышать 30 штук!';
} else {
    // Если количество валидно, обновляем корзину
    if ($new_quantity > 0) {
        // Обновляем количество в корзине
        $sql = "UPDATE user_cart SET quantity = ? WHERE user_id = ? AND pizza_id = ? AND dough_type_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('iiii', $new_quantity, $user_id, $pizza_id, $dough_type);
        $stmt->execute();
    } else {
        // Если количество равно 0, удаляем элемент из корзины
        $sql = "DELETE FROM user_cart WHERE user_id = ? AND pizza_id = ? AND dough_type_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('iii', $user_id, $pizza_id, $dough_type);
        $stmt->execute();
    }
    $_SESSION['notification'] = 'Корзина успешно обновлена!';
}

// Закрываем соединение с базой данных
$conn->close();

// Перенаправляем обратно в корзину
header('Location: cart.php');
exit;
?>
