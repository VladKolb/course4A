<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['notification'] = "У вас нет прав для редактирования заказов.";
    header("Location: index.php");
    exit();
}

if (isset($_POST['order_id'], $_POST['address'], $_POST['phone'], $_POST['order_date'], $_POST['pickup_point_id'])) {
    $order_id = $_POST['order_id'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];
    $order_date = $_POST['order_date'];
    $pickup_point_id = $_POST['pickup_point_id'];

    // Проверка даты заказа
    if (strtotime($order_date) > time()) {
        $_SESSION['notification'] = "Дата заказа не может быть в будущем.";
        header("Location: edit_order_form.php?id=" . urlencode($order_id));
        exit();
    }

    $sql = "UPDATE orders SET address = ?, phone = ?, order_date = ?, pickup_point_id = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssssi', $address, $phone, $order_date, $pickup_point_id, $order_id);

    if ($stmt->execute()) {
        $_SESSION['notification'] = "Заказ успешно обновлен.";
    } else {
        $_SESSION['notification'] = "Ошибка при обновлении заказа: " . $stmt->error;
    }

    $stmt->close();
} else {
    $_SESSION['notification'] = "Не все данные были переданы.";
}

$conn->close();
header("Location: index.php");
exit();
?>
