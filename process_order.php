<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

include 'db_connect.php';

$user_id = $_SESSION['user_id'];
$address = $_POST['address'];
$phone = $_POST['phone'];
$pickup_point_id = $_POST['pickup_point'];
$quantity = $_POST['quantity'][$pizza_id] ?? 1; // Получаем количество из поля формы


// Получаем данные карты
$card_number = $_POST['card_number'];
$card_name = $_POST['card_name'];
$card_expiry = $_POST['card_expiry'];
$card_cvc = $_POST['card_cvc'];

if (!isset($_POST['selected_pizzas']) || empty($_POST['selected_pizzas'])) {
    $_SESSION['error'] = 'Вы не выбрали ни одной пиццы!';
    header('Location: checkout.php');
    exit;
}


// Проверка формата MM/YY и срок действия карты
if (!preg_match('/^(0[1-9]|1[0-2])\/\d{2}$/', $card_expiry)) {
    $_SESSION['error'] = 'Введите срок действия карты в формате MM/YY';
    header('Location: checkout.php');
    exit;
}

list($month, $year) = explode('/', $card_expiry);
$month = (int)$month;
$year = (int)$year + 2000; // Приводим год в формате YY к полному

$currentYear = (int)date('Y');
$currentMonth = (int)date('m');

// Проверяем, не истек ли срок действия
if ($year < $currentYear || ($year === $currentYear && $month < $currentMonth)) {
    $_SESSION['error'] = 'Срок действия карты истёк. Пожалуйста, введите действующий срок.';
    header('Location: checkout.php');
    exit;
}

$selected_pizzas = $_POST['selected_pizzas'];

// Вставляем заказ в таблицу orders
$insert_order = "INSERT INTO orders (user_id, address, phone, order_date, pickup_point_id, status) VALUES (?, ?, ?, NOW(), ?, 'В ожидании')";
$stmt_order = $conn->prepare($insert_order);
$stmt_order->bind_param('issi', $user_id, $address, $phone, $pickup_point_id);
$stmt_order->execute();

$order_id = $conn->insert_id;

foreach ($selected_pizzas as $pizza_id) {
    // Получаем информацию о пицце
    $quantity = $_POST['quantity'][$pizza_id] ?? 1; // Получаем количество из поля формы
    $sql = "SELECT p.price, d.id AS dough_type_id FROM pizza p 
            JOIN user_cart uc ON uc.pizza_id = p.id 
            JOIN dough_types d ON uc.dough_type_id = d.id 
            WHERE uc.user_id = ? AND uc.pizza_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $user_id, $pizza_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $price = $row['price'];
        $dough_type_id = $row['dough_type_id'];

        // Вставляем пункт заказа в таблицу order_items
        $insert_order_item = "INSERT INTO order_items (order_id, pizza_id, dough_type_id, quantity, price) VALUES (?, ?, ?, ?, ?)";
        $stmt_item = $conn->prepare($insert_order_item);
        $stmt_item->bind_param('iiiii', $order_id, $pizza_id, $dough_type_id, $quantity, $price);
        $stmt_item->execute();

        // Обновляем предпочтения пользователя по пицце
        $insert_preference = "INSERT INTO user_pizza_preferences (user_id, pizza_id, order_count)
                              VALUES (?, ?, 1)
                              ON DUPLICATE KEY UPDATE order_count = order_count + 1";
        $stmt_preference = $conn->prepare($insert_preference);
        $stmt_preference->bind_param('ii', $user_id, $pizza_id);
        $stmt_preference->execute();
    }
}

// Удаляем только заказанные пиццы из корзины
foreach ($selected_pizzas as $pizza_id) {
    $delete_cart_item = "DELETE FROM user_cart WHERE user_id = ? AND pizza_id = ?";
    $stmt_delete_item = $conn->prepare($delete_cart_item);
    $stmt_delete_item->bind_param('ii', $user_id, $pizza_id);
    $stmt_delete_item->execute();
}

// Устанавливаем уведомление
$_SESSION['notification'] = 'Ваш заказ успешно оформлен!';


function encrypt_number($card_number) {
    $encrypted = '';
    for ($i = 0; $i < strlen($card_number); $i++) {
        $digit = (int)$card_number[$i];
        if ($i % 2 == 0) {
            $shifted = ($digit + 5) % 10; 
        } else {
            $shifted = ($digit - 2 + 10) % 10; 
        }
        $encrypted .= $shifted; 
    }
    return $encrypted; 
}




$encrypted_number = encrypt_number($card_number);


// Проверка, выбрал ли пользователь сохранение данных карты
// if (isset($_POST['save_card_data']) && $_POST['save_card_data'] == '1') {

// Сохраняем адрес в куке на 30 дней
setcookie('saved_address_' . $user_id, $address, time() + (86400 * 30), "/"); // Сохранение адреса на 30 дней
setcookie('card_number_' . $user_id, $encrypted_number, time() + (86400 * 30), "/"); // Сохранение номера карты
setcookie('card_name_' . $user_id, $card_name, time() + (86400 * 30), "/"); // Имя на карте
setcookie('card_expiry_' . $user_id, $card_expiry, time() + (86400 * 30), "/"); // Срок действия
// }
// Перенаправляем на страницу подтверждения
header('Location: index.php');
exit;

