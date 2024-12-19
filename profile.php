<?php
session_start();
include 'db_connect.php'; // Подключение к БД

// Проверка, авторизован ли пользователь и является ли он обычным пользователем
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: index.php'); // Перенаправляем на главную страницу, если не обычный пользователь
    exit();
}

// Получение данных пользователя
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Обработка обновления данных пользователя
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $address = $_POST['address'];
    $phone = $_POST['phone'];

    // Проверка телефона
    if (!preg_match('/^\d{5,9}$/', $phone)) {
        $_SESSION['notification'] = 'Телефон должен содержать от 5 до 9 цифр.';
        header('Location: profile.php');
        exit();
    }

    $update_sql = "UPDATE users SET address = ?, phone = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param('ssi', $address, $phone, $user_id);
    $update_stmt->execute();

    $_SESSION['notification'] = 'Информация обновлена!';
    header('Location: profile.php');
    exit();
}

// Получение всех заказов пользователя с пунктами выдачи
$orders_sql = "SELECT o.*, GROUP_CONCAT(p.name SEPARATOR ', ') AS pizzas, pickup.name AS pickup_point_name 
               FROM orders o 
               JOIN order_items oi ON o.id = oi.order_id 
               JOIN pizza p ON oi.pizza_id = p.id 
               JOIN pickup_points pickup ON o.pickup_point_id = pickup.id 
               WHERE o.user_id = ? AND o.is_hidden = 0
               GROUP BY o.id 
               ORDER BY o.order_date DESC";
$orders_stmt = $conn->prepare($orders_sql);
$orders_stmt->bind_param('i', $user_id);
$orders_stmt->execute();
$orders_result = $orders_stmt->get_result();

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Личный кабинет</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&family=Montserrat:wght@600&display=swap">
    <style>
        * {
            font-family: 'Roboto', sans-serif; /* Применяем шрифт ко всем элементам */
            box-sizing: border-box; /* Упрощаем работу с размерами */
        }
        body {
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: #333;
            font-family: 'Montserrat', sans-serif; /* Используем Montserrat для заголовка */
        }
        p, label, input[type="text"], button, a {
            color: #555;
        }
        p {
            font-size: 18px;
        }
        label {
            font-size: 16px;
        }
        input[type="text"] {
            width: 100%;
            padding: 10px;
            margin-top: 8px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            background-color: #007bff; /* Синий цвет кнопки */
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #0056b3; /* Темнее фон при наведении */
        }
        a {
            display: inline-block;
            margin-top: 20px;
            text-decoration: none;
            color: #007bff;
        }
        a:hover {
            color: #0056b3;
        }
        .notification {
            background-color: #d4edda;
            color: #155724;
            padding: 10px;
            border: 1px solid #c3e6cb;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .back-button {
            margin: 20px;
            padding: 10px 20px;
            background-color: #007bff; /* Синий цвет кнопки */
            color: white; /* Белый цвет текста */
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        .back-button:hover {
            background-color: #0056b3; /* Темнее фон при наведении */
            color: white; /* Текст остается белым */
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #4CAF50;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>

<a class="back-button" href="index.php">Вернуться на главную страницу</a>

<div class="container">
    <h1>Личный кабинет</h1>

    <?php if (isset($_SESSION['notification'])): ?>
        <div class="notification"><?= $_SESSION['notification']; unset($_SESSION['notification']); ?></div>
    <?php endif; ?>
    
    <p>Email: <?= htmlspecialchars($user['email']); ?></p>

    <h3>Обновить информацию:</h3>
    <form method="post">
        <label>Адрес:</label>
        <input type="text" name="address" value="<?= htmlspecialchars($user['address']); ?>"><br>

        <label>Телефон:</label>
        <input type="text" name="phone" value="<?= htmlspecialchars($user['phone']); ?>" pattern="\d{5,9}" maxlength="9" title="Введите от 5 до 9 цифр" required><br>

        <button type="submit">Сохранить</button>
    </form>

    <h3>Ваши заказы:</h3>
    <?php if ($orders_result->num_rows > 0): ?>
        <table>
            <tr>
                <th>ID Заказа</th>
                <th>Пиццы</th>
                <th>Дата заказа</th>
                <th>Пункт выдачи</th>
                <th>Статус</th> <!-- Статус заказа -->
                <th>Действие</th> <!-- Действие для удаления -->
            </tr>
            <?php while ($order = $orders_result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($order['id']); ?></td>
                    <td><?= htmlspecialchars($order['pizzas']); ?></td>
                    <td><?= htmlspecialchars($order['order_date']); ?></td>
                    <td><?= htmlspecialchars($order['pickup_point_name']); ?></td>
                    <td><?= htmlspecialchars($order['status']); ?></td> <!-- Выводим статус заказа -->
                    <td>
                        <?php if ($order['status'] === 'Отменен' || $order['status'] === 'Выдан' || $order['status'] === 'В ожидании'): ?>
                           
                            <form method="post" action="hide_order.php">
                                <input type="hidden" name="order_id" value="<?= htmlspecialchars($order['id']); ?>">
                                <button type="submit">Удалить</button>
                            </form>
                            
                        <?php else: ?>
                            <span>—</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>У вас нет заказов.</p>
    <?php endif; ?>

    <a href="logout.php">Выйти из аккаунта</a>
</div>

</body>
</html>
