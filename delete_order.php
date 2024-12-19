<?php
session_start();
include 'db_connect.php'; // Подключение к базе данных

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    // Проверка прав доступа
    $_SESSION['notification'] = "У вас нет прав для удаления заказов.";
    header("Location: index.php"); // Перенаправление на главную страницу
    exit();
}

if (isset($_GET['id'])) {
    $order_id = $_GET['id'];

    // Подготовка и выполнение запроса на получение user_id и pizza_id из заказа
    $get_order_sql = "SELECT user_id, pizza_id FROM order_items LEFT JOIN orders ON orders.id = order_items.order_id WHERE order_id = ?";
    $get_order_stmt = $conn->prepare($get_order_sql);
    $get_order_stmt->bind_param('i', $order_id);
    $get_order_stmt->execute();
    $order_items = $get_order_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Подготовка и выполнение запроса на удаление заказа
    $delete_order_sql = "DELETE FROM orders WHERE id = ?";
    $delete_order_stmt = $conn->prepare($delete_order_sql);
    $delete_order_stmt->bind_param('i', $order_id);

    if ($delete_order_stmt->execute()) {
        // Успешное удаление заказа
        $_SESSION['notification'] = "Заказ успешно удален.";

        // Уменьшаем количество предпочтений для каждого пользователя и пиццы из заказа
        foreach ($order_items as $item) {
            $user_id = $item['user_id']; // Идентификатор пользователя
            $pizza_id = $item['pizza_id']; // Идентификатор пиццы

            // Уменьшаем количество предпочтений, связанных с пользователем и пиццей
            $decrease_preferences_sql = "UPDATE user_pizza_preferences SET order_count = GREATEST(order_count - 1, 0) WHERE user_id = ? AND pizza_id = ?";
            $preferences_stmt = $conn->prepare($decrease_preferences_sql);
            $preferences_stmt->bind_param('ii', $user_id, $pizza_id);

            if ($preferences_stmt->execute()) {
                // Успешное уменьшение количества предпочтений
                $_SESSION['notification'] .= " Количество предпочтений для пользователя с ID {$user_id} и пиццы с ID {$pizza_id} уменьшено.";
            } else {
                // Ошибка при уменьшении количества предпочтений
                $_SESSION['notification'] .= " Ошибка при уменьшении предпочтений для пользователя с ID {$user_id} и пиццы с ID {$pizza_id}: " . $preferences_stmt->error;
            }

            $preferences_stmt->close();
        }
    } else {
        $_SESSION['notification'] = "Ошибка при удалении заказа: " . $delete_order_stmt->error;
    }

    $delete_order_stmt->close();
} else {
    $_SESSION['notification'] = "ID заказа не передан.";
}

$get_order_stmt->close();
$conn->close();
header("Location: index.php"); // Перенаправление на главную страницу
exit();
?>
