<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Детали пиццы</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .modal {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            width: 600px;
            display: flex;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            position: relative;
        }
        .modal img {
            max-width: 250px;
            border-radius: 10px;
        }
        .modal-content {
            margin-left: 20px;
            flex-grow: 1;
        }
        .modal-content h2 {
            margin-top: 0;
        }
        .modal-content p {
            font-size: 16px;
            color: #666;
        }
        .modal-content .price {
            font-size: 18px;
            color: #28a745;
            margin-top: 10px;
        }
        .modal-content .add-to-cart {
            margin-top: 20px;
        }
        .modal-content a {
            text-decoration: none;
            color: white;
            background-color: #ff5733;
            padding: 10px 20px;
            border-radius: 5px;
        }
        .modal-content a:hover {
            background-color: #c70039;
        }
        .close-modal {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 24px;
            color: black;
            text-decoration: none;
            background-color: transparent;
        }
    </style>
</head>
<body>

<?php
include 'db_connect.php'; // Подключение к базе данных

// Получаем ID пиццы из GET-запроса
$pizza_id = $_GET['id'] ?? 0;

// Запрос к базе данных для получения данных пиццы
$sql = "SELECT name, description, price, image_url, image_blob FROM pizza WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $pizza_id);
$stmt->execute();
$result = $stmt->get_result();

// Проверка, существует ли пицца с указанным ID
if ($result->num_rows > 0) {
    $pizza = $result->fetch_assoc();
} else {
    echo "<p>Пицца не найдена.</p>";
    exit();
}
$stmt->close();
$conn->close();
?>

<div class="modal">
    <!-- Изображение пиццы -->
    <div class="modal-image">
        <?php if (!empty($pizza['image_url'])): ?>
            <img src="<?= htmlspecialchars($pizza['image_url']) ?>" alt="<?= htmlspecialchars($pizza['name']) ?>">
        <?php elseif (!empty($pizza['image_blob'])): ?>
            <img src="data:image/jpeg;base64, <?= base64_encode($pizza['image_blob']) ?>" alt="<?= htmlspecialchars($pizza['name']) ?>">
        <?php else: ?>
            <p>Изображение отсутствует</p>
        <?php endif; ?>
    </div>

    <!-- Описание пиццы -->
    <div class="modal-content">
        <h2><?= htmlspecialchars($pizza['name']) ?></h2>
        <p><?= htmlspecialchars($pizza['description']) ?></p>
        <p class="price">Цена: <?= htmlspecialchars($pizza['price']) ?> руб.</p>
        
        <div class="add-to-cart">
            <form method="post" action="add_to_cart.php">
                <input type="hidden" name="pizza_id" value="<?= $pizza_id ?>">
                <button type="submit">Добавить в корзину</button>
            </form>
        </div>
    </div>
</div>

<!-- Кнопка закрытия модального окна -->
<a href="index.php" class="close-modal">&times;</a>

</body>
</html>
