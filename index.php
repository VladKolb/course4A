<?php
session_start();

$is_logged_in = isset($_SESSION['user_id']);
$is_user = $is_logged_in && isset($_SESSION['role']) && $_SESSION['role'] === 'user'; 
$is_admin = $is_logged_in && isset($_SESSION['role']) && $_SESSION['role'] === 'admin'; 
$is_employee = $is_logged_in && $_SESSION['role'] === 'employee'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$notification = '';
if (isset($_SESSION['notification'])) {
    $notification = $_SESSION['notification'];
    unset($_SESSION['notification']);
}

include 'db_connect.php';

if ($is_employee) {
    header('Location: home_employee.php');
    exit();
}

$cart_count = 0;
if ($is_logged_in) {
    $sql = "SELECT SUM(quantity) AS total_quantity FROM user_cart WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $stmt->bind_result($cart_count);
    $stmt->fetch();
    $stmt->close();
}

$sort_column = $_GET['sort'] ?? 'order_count'; 
$sort_order = $_GET['order'] ?? 'desc'; 

$valid_columns = ['name', 'price', 'order_count'];
if (!in_array($sort_column, $valid_columns)) {
    $sort_column = 'order_count'; 
}

$user_order_count = 0;
if ($is_logged_in) {
    $sql = "SELECT COUNT(*) FROM orders WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $stmt->bind_result($user_order_count);
    $stmt->fetch();
    $stmt->close();
}

$show_personalized_message = false;
if ($user_order_count > 0 && $sort_column === 'order_count' && empty($_GET['search'])) {
    $show_personalized_message = true;
}

$sql = "SHOW TABLES";
$result = $conn->query($sql);

$tables = [];
while ($row = $result->fetch_row()) {
    if (!in_array($row[0], ['order_items', 'pizza', 'sizes', 'user_cart', 'users', 'user_pizza_preferences', 'weights'])) {
        $tables[] = $row[0];
    }
}

$selected_table = $_POST['selected_table'] ?? null;
$table_data = null;

if ($selected_table) {
    $sql = "SELECT * FROM $selected_table";
    $table_data = $conn->query($sql);
}


$temperature = "Недоступно";

// // Получение данных о погоде
// $url = "http://api.openweathermap.org/data/2.5/weather";
// $options = array(
//     "q" => "Minsk",
//     "APPID" => "fcd8c69769c16263fda143737d57f5a5", 
//     "units" => "metric", 
//     "lang" => "en", 
// );

// $ch = curl_init();
// curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
// curl_setopt($ch, CURLOPT_URL, $url . '?' . http_build_query($options));

// $response = curl_exec($ch);
// $data = json_decode($response, true);
// curl_close($ch);

// if (isset($data['main']['temp'])) {
//     $temperature = $data['main']['temp'] . "°C"; 
// }


?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&family=Montserrat:wght@600&display=swap">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Пиццерия - Главная</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .edit-btn,
        .delete-btn {
            padding: 8px 15px;
            margin-right: 10px;
            margin-bottom: 5px;
            display: inline-block;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            color: white;
            font-family: 'Roboto', sans-serif;
            transition: background-color 0.3s ease;
        }

        .edit-orders-button {
            padding: 10px 20px;
            background-color: #f44336;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-right: 10px;
        }

        h1 {
            margin-top: 80px;
        }

        h2 {
            margin-top: 15px;
            color: #000000;
        }


        .edit-btn {
            background-color: #4CAF50;
        }

        .edit-btn:hover {
            background-color: #45a049;
        }

        .delete-btn {
            background-color: #f44336;
        }

        .delete-btn:hover {
            background-color: #e53935;
        }

        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
        }

        .search-wrapper {
            flex-grow: 1;
            margin-top: 0px;
        }

        select {
            padding: 5px;
            border-radius: 4px;
            border: 1px solid #ddd;
            font-family: 'Roboto', sans-serif;
        }

        .auth-buttons {
            position: absolute;
            top: 0;
            right: 0;
            display: flex;
            gap: 10px;
        }

        .auth-btn {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .auth-btn:hover {
            background-color: #0056b3;
        }

        .search-wrapper {
            margin-right: 150px;
        }

        .add-pizza-container {
            margin-top: 20px;
        }

        .add-pizza-btn {
            padding: 10px 20px;
            background-color: #f44336;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-right: 10px;
        }

        .add-pizza-btn:hover {
            background-color: #0056b3;
        }

        .add-to-cart-btn {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            font-family: 'Roboto', sans-serif;
        }

        .add-to-cart-btn:hover {
            background-color: #0056b3;
        }

        .role-request-btn {
            background-color: #007bff;
            border: none;
            padding: 12px 20px;
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            cursor: pointer;
            color: white;
        }

        .role-request-btn:hover {
            background-color: #0056b3;
        }

        .personalized-menu {
            display: inline-block;
            text-align: center;
            margin: 0;
            padding: 5px 15px;
            background: linear-gradient(135deg, rgba(0, 123, 255, 0.5), rgba(0, 123, 255, 0.3));
            border-radius: 10px;
            font-size: 14px;
            color: #ffffff;
            font-family: 'Roboto', sans-serif;
            font-weight: 400;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            animation: fadeIn 1.5s ease-in-out;
            line-height: 1;
            vertical-align: middle;
        }

        @keyframes fadeIn {
            0% {
                opacity: 0;
                transform: translateY(-10px);
            }

            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        
        input[type="checkbox"]:checked~.modal-overlay {
            display: block;
        }

        .modal-content {
            display: block;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            text-align: center;
        }

        .modal-close {
            cursor: pointer;
            color: red;
            font-weight: bold;
            margin-top: 10px;
        }

       
        .modal-overlay:hover {
            cursor: pointer;
        }

        .image-container {
            width: 200px;
            height: 200px;
            margin: 0 auto;
            overflow: hidden;
            position: relative;
            border-radius: 5px;
        }

        .pizza-image {
            position: absolute;
            top: 50%;
            left: 50%;
            width: auto;
            height: 100%;
            object-fit: cover;
            transform: translate(-50%, -50%);
        }

   
    </style>
</head>

<body>
    <div class="header-container">
    <!-- <div class="weather-info">
            <p>Температура в Минске: <?= htmlspecialchars($temperature); ?></p>
        </div> -->
        <div class="search-wrapper">
            <h3>Поиск по пиццам:</h3>
            <form method="get" class="search-form">
                <div class="input-group">
                    <input type="text" name="search" class="form-control"
                        value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" placeholder="Введите запрос">
                    <button type="submit" class="btn btn-primary">Найти</button>
                </div>
            </form>
        </div>

        <div class="auth-buttons">
            <?php if ($is_logged_in && $is_user): ?>
                <a href="cart.php" class="auth-btn">Корзина<?= $cart_count > 0 ? " ($cart_count)" : "" ?></a>
                <a href="profile.php" class="auth-btn">Личный кабинет</a>
                <form method="post" action="request_role_change.php" style="display:inline;">
                    <button type="submit" class="auth-btn role-request-btn">Запросить роль сотрудника</button>
                </form>
                <a href="logout.php" class="auth-btn">Выйти</a>
            <?php elseif ($is_logged_in && $is_admin): ?>
                <a href="user_list.php" class="auth-btn">Список пользователей</a>
                <a href="personalization_settings.php" class="auth-btn">Настройки персонализации</a>
                <a href="logout.php" class="auth-btn">Выйти</a>
            <?php else: ?>
                <a href="login.php" class="auth-btn">Войти</a>
            <?php endif; ?>
        </div>
    </div>

    <h1>Добро пожаловать в нашу Пиццерию!</h1>

    <?php if ($show_personalized_message): ?>
        <div class="personalized-menu">
            <h2>Меню на основе ваших предпочтений</h2>
        </div>
    <?php endif; ?>

    <?php if ($notification): ?>
        <div class="notification" style="text-align: center;">
            <?= htmlspecialchars($notification) ?>
        </div>
    <?php endif; ?>

    <?php if ($is_admin): ?>
        <div class="add-pizza-container">
            <a href="add_pizza_form.php" class="add-pizza-btn">Добавить новую пиццу</a>
        </div>
    <?php endif; ?>

    <?php


    // Получаем критерии и веса из таблицы personalization_weights
    $weights = [];
    $sql = "SELECT criterion, weight FROM weights";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        $weights[$row['criterion']] = $row['weight'];
    }

    $order_count_weight = $weights['order_count'] ?? 1.0;
    //$order_frequency_weight = $weights['order_frequency'] ?? 1.0;
    $pizza_count_in_order_weight = $weights['pizza_count_in_order'] ?? 1.0;
    $total_order_value_weight = $weights['total_order_value'] ?? 1.0;
    $monthly_popularity_weight = $weights['monthly_popularity'] ?? 1.0;

    $sort_column = $_GET['sort'] ?? 'weighted_rating'; // Сортируем по взвешенному рейтингу по умолчанию
    $sort_order = $_GET['order'] ?? 'desc';

    $user_id = $_SESSION['user_id'];

    $search = $_GET['search'] ?? '';

    
    $sql = "
SELECT p.*, 
       COALESCE(upp.order_count, 0) AS order_count,
       COALESCE(up.pizza_count_in_order, 0) AS pizza_count_in_order,
       COALESCE(up.total_order_value, 0) AS total_order_value,
       COALESCE(mp.monthly_popularity, 0) AS monthly_popularity,
       (
           ($order_count_weight * COALESCE(upp.order_count, 0)) + 
           ($pizza_count_in_order_weight * COALESCE(up.pizza_count_in_order, 0)) +
           ($total_order_value_weight * COALESCE(up.total_order_value, 0)) +
           ($monthly_popularity_weight * COALESCE(mp.monthly_popularity, 0))
       ) AS weighted_rating
FROM pizza p
LEFT JOIN (SELECT pizza_id, order_count 
                    FROM user_pizza_preferences 
                    WHERE user_id = ? 
                    GROUP BY pizza_id) AS upp ON p.id = upp.pizza_id
LEFT JOIN (
    SELECT oi.pizza_id, 
           SUM(oi.quantity) AS pizza_count_in_order, 
           SUM(oi.price * oi.quantity) AS total_order_value
    FROM order_items oi
    LEFT JOIN orders o ON oi.order_id = o.id
    WHERE o.user_id = ?
    GROUP BY oi.pizza_id
) AS up ON p.id = up.pizza_id
LEFT JOIN (
    SELECT oi.pizza_id, COUNT(*) AS monthly_popularity
    FROM order_items oi
    LEFT JOIN orders o ON oi.order_id = o.id
    WHERE o.order_date >= DATE_SUB(NOW(), INTERVAL 1 MONTH)
    GROUP BY oi.pizza_id
) AS mp ON p.id = mp.pizza_id
WHERE p.name LIKE ? 
ORDER BY $sort_column $sort_order;
";


    $stmt = $conn->prepare($sql);

    $search_param = "%$search%"; 
    $stmt->bind_param('iis', $user_id, $user_id, $search_param); 
    
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<table>
    <tr>
        <th><a href='?search=" . urlencode($search) . "&sort=name&order=" . ($sort_column == 'name' && $sort_order == 'asc' ? 'desc' : 'asc') . "'>Название</a></th>
        <th>Описание</th>
        <th><a href='?search=" . urlencode($search) . "&sort=price&order=" . ($sort_column == 'price' && $sort_order == 'asc' ? 'desc' : 'asc') . "'>Цена (руб.)</a></th>";

        if ($is_admin) {
            echo "<th>Действия</th>"; // для админа
        } else {
            echo "<th>Тип теста</th>"; // для пользователя
        }

        echo "</tr>";

        while ($row = $result->fetch_assoc()) {
            // Уникальный ID для модального окна
            $modal_id = "modal-" . $row['id'];

            echo "<tr>
            <td>
                <label for='$modal_id' style='cursor: pointer; color: #007BFF; font-weight: bold;'>" . htmlspecialchars($row['name']) . "</label>
                
                <!-- Модальное окно -->
                <input type='checkbox' id='$modal_id' style='display: none;'>
                <div class='modal-overlay'>
                    <div class='modal-content'>
                        <h2 class='modal-header'>" . htmlspecialchars($row['name']) . "</h2> <!-- Название пиццы -->
                        <div class='image-container'>
                            <img src='" . htmlspecialchars($row['image_url']) . "' alt='" . htmlspecialchars($row['name']) . "' class='pizza-image'>
                        </div>
                        <p>" . htmlspecialchars($row['description']) . "</p>
                        <p>Цена: " . htmlspecialchars($row['price']) . " руб.</p>";

            if ($is_admin) {
                echo "<form method='post' action='change_image.php'>
                    <input type='hidden' name='pizza_id' value='" . $row['id'] . "'>
                    <button type='submit' class='change-image-btn'>Изменить изображение</button>
                  </form>";
            } else {
                echo "<form method='post' action='add_to_cart.php'>
                    <select name='dough_type'>";

                // Вывод типов теста
                $dough_sql = "SELECT * FROM dough_types";
                $dough_result = $conn->query($dough_sql);
                while ($dough = $dough_result->fetch_assoc()) {
                    echo "<option value='" . $dough['id'] . "'>" . htmlspecialchars($dough['dough_name']) . "</option>";
                }

                echo "</select>
                    <input type='hidden' name='pizza_id' value='" . $row['id'] . "'>
                    <button type='submit' class='add-to-cart-btn'>Добавить в корзину</button>
                  </form>";
            }

            echo "<label for='$modal_id' class='modal-close'>Закрыть</label>
                    </div>
                </div>
            </td>
            <td>" . htmlspecialchars($row['description']) . "</td>
            <td>" . htmlspecialchars($row['price']) . "</td>";

            if ($is_admin) {
                echo "<td>
                <a href='edit_pizza_form.php?id=" . $row['id'] . "' class='edit-btn'>Редактировать</a>
                <a href='confirm_delete.php?id=" . $row['id'] . "' class='delete-btn'>Удалить</a>
            </td>";
            } else {
                echo "<td>
                <form method='post' action='add_to_cart.php'>
                    <select name='dough_type'>";

                $dough_sql = "SELECT * FROM dough_types";
                $dough_result = $conn->query($dough_sql);
                while ($dough = $dough_result->fetch_assoc()) {
                    echo "<option value='" . $dough['id'] . "'>" . htmlspecialchars($dough['dough_name']) . "</option>";
                }

                echo "</select>
                  <input type='hidden' name='pizza_id' value='" . $row['id'] . "'>
                  <button type='submit' class='add-to-cart-btn'>Добавить в корзину</button>
                </form>
            </td>";
            }

            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>Пиццы не найдены.</p>";
    }

    $conn->close();
    ?>


    <?php if ($is_admin): ?>
        <div class="select-table-container" style="text-align: center; margin-top: 20px;">
            <form method="post">
                <label for="tables">Выберите таблицу:</label>
                <select name="selected_table" id="tables">
                    <option value="">-- Выберите --</option>
                    <?php foreach ($tables as $table): ?>
                        <option value="<?= htmlspecialchars($table) ?>"><?= htmlspecialchars($table) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="add-pizza-btn">Выбрать</button>
            </form>
        </div>

        <?php if ($is_admin && $table_data): ?>
            <h2>Содержимое таблицы: <?php echo htmlspecialchars($selected_table); ?></h2>
            <table>
                <tr>
                    <?php while ($field = $table_data->fetch_field()): ?>
                        <th><?php echo htmlspecialchars($field->name); ?></th>
                    <?php endwhile; ?>
                    <th>Действия</th>
                </tr>
                <?php while ($row = $table_data->fetch_assoc()): ?>
                    <tr>
                        <?php foreach ($row as $cell): ?>
                            <td><?php echo htmlspecialchars($cell); ?></td>
                        <?php endforeach; ?>
                        <td>
                            <?php if ($selected_table === 'dough_types'): ?>
                                <a href="edit_dough_types.php?id=<?php echo htmlspecialchars($row['id']); ?>"
                                    class="edit-btn">Редактировать</a>
                                <a href="delete_dough_type.php?id=<?php echo htmlspecialchars($row['id']); ?>"
                                    class="delete-btn">Удалить</a>
                            <?php elseif ($selected_table === 'orders'): ?>
                                <a href="edit_order_form.php?id=<?php echo htmlspecialchars($row['id']); ?>"
                                    class="edit-btn">Редактировать</a>
                                <a href="delete_order.php?id=<?php echo htmlspecialchars($row['id']); ?>" class="delete-btn">Удалить</a>
                            <?php elseif ($selected_table === 'pickup_points'): ?>
                                <a href="edit_pickup_point.php?id=<?php echo htmlspecialchars($row['id']); ?>"
                                    class="edit-btn">Редактировать</a>
                                <a href="delete_pickup_point.php?id=<?php echo htmlspecialchars($row['id']); ?>"
                                    class="delete-btn">Удалить</a>
                            <?php elseif ($selected_table === 'users'): ?>
                                <a href="edit_user.php?id=<?php echo htmlspecialchars($row['id']); ?>"
                                    class="edit-btn">Редактировать</a>
                                <a href="delete_user.php?id=<?php echo htmlspecialchars($row['id']); ?>" class="delete-btn">Удалить</a>
                            <?php else: ?>
                                <a href="edit_table.php?table=<?php echo urlencode($selected_table); ?>&id=<?php echo htmlspecialchars($row['id']); ?>"
                                    class="edit-btn">Редактировать</a>
                                <a href="delete_table.php?table=<?php echo urlencode($selected_table); ?>&id=<?php echo htmlspecialchars($row['id']); ?>"
                                    class="delete-btn">Удалить</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php endif; ?>
    <?php endif; ?>
    <script>
        
    </script>
</body>

</html>