<?php
session_start();

// Подключаемся к базе данных
include 'db_connect.php';

// Проверяем, что пользователь является администратором
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "Доступ запрещён.";
    exit();
}

// Получаем ID пиццы из запроса
$pizza_id = $_GET['id'] ?? null;

if (!$pizza_id) {
    echo "Некорректный ID пиццы.";
    exit;
}

// Получаем данные пиццы из базы данных
$sql = "SELECT * FROM pizza WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $pizza_id);
$stmt->execute();
$result = $stmt->get_result();
$pizza = $result->fetch_assoc();

if (!$pizza) {
    echo "Пицца не найдена.";
    exit;
}

// Обрабатываем отправку формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $description = $conn->real_escape_string($_POST['description']);
    $price = (float)$_POST['price'];

    // Проверка: цена не должна превышать 9999
    if ($price <= 0 || $price > 9999) {
        echo "Цена должна быть больше 0 и не превышать 9999.";
    } else {
        // Обновляем данные пиццы в базе
        $update_sql = "UPDATE pizza SET name = ?, description = ?, price = ? WHERE id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param('ssdi', $name, $description, $price, $pizza_id);

        if ($stmt->execute()) {
            // Перенаправляем на главную страницу с уведомлением
            $_SESSION['notification'] = 'Пицца успешно обновлена!';
            header('Location: index.php');
            exit();
        } else {
            echo "Ошибка при обновлении пиццы.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактировать пиццу</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Основные стили для формы */
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f9f9f9;
            padding: 20px;
        }

        h2 {
            text-align: center;
            color: #333;
        }

        form {
            max-width: 600px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
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

        .cancel-btn {
            background-color: #dc3545;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            text-decoration: none;
            margin-left: 10px;
        }

        .cancel-btn:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>

<h2>Редактировать пиццу</h2>

<form method="POST">
    <input type="hidden" name="id" value="<?= htmlspecialchars($pizza['id']) ?>">
    <div class="form-group">
        <label for="name">Название пиццы:</label>
        <input type="text" id="name" name="name" value="<?= htmlspecialchars($pizza['name']) ?>" required>
    </div>
    <div class="form-group">
        <label for="description">Описание:</label>
        <textarea id="description" name="description" required><?= htmlspecialchars($pizza['description']) ?></textarea>
    </div>
    <div class="form-group">
        <label for="price">Цена (руб.):</label>
        <input type="number" id="price" name="price" value="<?= htmlspecialchars($pizza['price']) ?>" step="0.01" required>
    </div>
    <div class="form-actions">
        <button type="submit">Сохранить изменения</button>
        <a href="index.php" class="cancel-btn">Отмена</a>
    </div>
</form>

</body>
</html>
