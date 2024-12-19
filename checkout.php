<?php
session_start();

// Проверяем, авторизован ли пользователь
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Подключаемся к базе данных
include 'db_connect.php';

// Получаем данные корзины пользователя
$sql = "SELECT uc.*, p.name AS pizza_name, p.price, d.dough_name 
        FROM user_cart uc
        JOIN pizza p ON uc.pizza_id = p.id
        JOIN dough_types d ON uc.dough_type_id = d.id
        WHERE uc.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

$total_price = 0;



function decrypt_number($encrypted_number) {
    $decrypted = '';
    for ($i = 0; $i < strlen($encrypted_number); $i++) {
        $digit = (int)$encrypted_number[$i];
        if ($i % 2 == 0) {
            $shifted = ($digit - 5 + 10) % 10; 
        } else {
            $shifted = ($digit + 2) % 10; 
        }
        $decrypted .= $shifted; 
    }
    return $decrypted; 
}




// Получаем пункты выдачи
$points_sql = "SELECT * FROM pickup_points";
$points_result = $conn->query($points_sql);
?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Оформление заказа</title>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
        }

        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background-color: #4CAF50;
            color: white;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
        }

        input[type="text"],
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        button {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background-color: #45a049;
        }

        .notification {
            color: red;
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>

<body>

    <div class="container">
        <h1>Подтверждение заказа</h1>

        <form action="process_order.php" method="post">
            <table>
                <tr>
                    <th>Выбрать</th>
                    <th>Пицца</th>
                    <th>Тип теста</th>
                    <th>Цена</th>
                    <th>Всего</th>
                </tr>
                <?php
                while ($row = $result->fetch_assoc()) {
                    $total = $row['price'] * $row['quantity'];
                    $total_price += $total;
                    echo "<tr>
                        <td><input type='checkbox' name='selected_pizzas[]' value='{$row['pizza_id']}' checked></td>  
                        <td>{$row['pizza_name']}</td>
                        <td>{$row['dough_name']}</td>
                        <td>{$row['price']} руб.</td>
                        <td>{$total} руб.</td>
                      </tr>";
                }
                ?>
                <tr>
                    <td colspan="4">Итого:</td>
                    <td><?php echo $total_price; ?> руб.</td>
                </tr>
            </table>

            <div class="form-group">
                <label for="pickup_point">Пункт выдачи:</label>
                <select id="pickup_point" name="pickup_point" required>
                    <option value="">Выберите пункт выдачи</option>
                    <?php while ($point = $points_result->fetch_assoc()): ?>
                        <option value="<?php echo $point['id']; ?>">
                            <?php echo htmlspecialchars($point['name']) . ' - ' . htmlspecialchars($point['address']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="address">Адрес доставки:</label>
                <input type="text" id="address" name="address"
                    value="<?php echo isset($_COOKIE['saved_address_' . $_SESSION['user_id']]) ? htmlspecialchars($_COOKIE['saved_address_' . $_SESSION['user_id']]) : ''; ?>"
                    required>
            </div>

            <div class="form-group">
                <label for="phone">Телефон:</label>
                <input type="text" id="phone" name="phone" required pattern="^\+?[0-9]{7,15}$"
                    title="Введите корректный номер телефона">
            </div>

            <div class="form-group">
                <label for="card_number">Номер карты:</label>
                <input type="text" id="card_number" name="card_number"
                    value="<?php echo isset($_COOKIE['card_number_' . $_SESSION['user_id']]) ? htmlspecialchars(decrypt_number($_COOKIE['card_number_' . $_SESSION['user_id']])) : ''; ?>"
                    pattern="\d{16}" required title="Введите 16-значный номер карты">
            </div>
            <div class="form-group">
                <label for="card_name">Имя на карте:</label>
                <input type="text" id="card_name" name="card_name"
                    value="<?php echo isset($_COOKIE['card_name_' . $_SESSION['user_id']]) ? htmlspecialchars($_COOKIE['card_name_' . $_SESSION['user_id']]) : ''; ?>"
                    required>
            </div>
            <div class="form-group">
                <label for="card_expiry">Срок действия (MM/YY):</label>
                <input type="text" id="card_expiry" name="card_expiry"
                    value="<?php echo isset($_COOKIE['card_expiry_' . $_SESSION['user_id']]) ? htmlspecialchars($_COOKIE['card_expiry_' . $_SESSION['user_id']]) : ''; ?>"
                    pattern="^(0[1-9]|1[0-2])\/\d{2}$" required title="Введите срок действия в формате MM/YY">
            </div>
            <!-- Чекбокс для сохранения данных карты
            <div class="form-group">
                <input type="checkbox" id="save_card_data" name="save_card_data" value="1">
                <label for="save_card_data">Сохранить данные карты для следующего заказа</label>
            </div> -->

            <button type="submit">Подтвердить заказ</button>
        </form>

        <a href="cart.php" class="back-button">Вернуться назад</a>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="notification">
                <?php echo htmlspecialchars($_SESSION['error']); ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
    </div>

</body>

</html>