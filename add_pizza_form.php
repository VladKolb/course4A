<?php
session_start();

$notification = '';
if (isset($_SESSION['notification'])) {
    $notification = $_SESSION['notification'];
    unset($_SESSION['notification']); // Удаляем сообщение после его использования
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Добавить новую пиццу</title>
    <link rel="stylesheet" href="styles.css"> <!-- Подключение CSS -->
    <style>
        /* Основные стили для формы */
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f9f9f9;
            padding: 20px;
        }

        h1 {
            text-align: center;
            color: #333;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .notification {
            background-color: #ffcc00;
            color: #333;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
            color: #555;
        }

        input[type="text"], input[type="number"], textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
            box-sizing: border-box;
        }

        textarea {
            height: 100px;
            resize: none;
        }

        .form-actions {
            text-align: center;
            margin-top: 20px;
        }

        button {
            background-color: #28a745;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Добавить новую пиццу</h1>

    <?php if ($notification): ?>
        <div class="notification">
            <?php echo htmlspecialchars($notification); ?>
        </div>
    <?php endif; ?>

    <form method="post" action="add_pizza.php">
        <div class="form-group">
            <label>Название пиццы:</label>
            <input type="text" name="name" required>
        </div>
        <div class="form-group">
            <label>Описание пиццы:</label>
            <textarea name="description" required></textarea>
        </div>
        <div class="form-group">
            <label>Цена (в рублях):</label>
            <input type="number" name="price" step="0.01" max="9999" required>
        </div>
        <div class="form-actions">
            <button type="submit">Добавить пиццу</button>
        </div>
    </form>
</div>

</body>
</html>
