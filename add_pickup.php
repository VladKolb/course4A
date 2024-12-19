<?php
session_start();

$is_logged_in = isset($_SESSION['user_id']);
$is_employee = $is_logged_in && $_SESSION['role'] === 'employee';

if (!$is_logged_in || !$is_employee) {
    header('Location: login.php');
    exit();
}

$notification = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include 'db_connect.php';

    $name = $_POST['name'];
    $address = $_POST['address'];

    // Проверка, что файл загружен и не превышает допустимый размер
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image = $_FILES['image'];
        $image_size = $image['size'];
        $image_tmp_name = $image['tmp_name'];
        $image_type = mime_content_type($image_tmp_name); // Определяем MIME-тип

        // Допустимые форматы изображений
        $allowed_types = ['image/jpeg', 'image/png'];

        // Лимит в 10 MB для изображения
        $max_file_size = 10 * 1024 * 1024;

        if (!in_array($image_type, $allowed_types)) {
            $notification = "Ошибка: недопустимый формат изображения. Разрешены только JPEG, PNG.";
        } elseif ($image_size > $max_file_size) {
            $notification = "Ошибка: размер изображения превышает 10MB.";
        } else {
            // Получаем данные изображения как двоичный массив (BLOB)
            $image_data = file_get_contents($image['tmp_name']);

            // Получаем ID текущего сотрудника
            $employee_id = $_SESSION['user_id'];

            // Добавляем пункт выдачи с изображением
            $sql = "INSERT INTO pickup_points (name, address, employee_id, image) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ssis', $name, $address, $employee_id, $image_data); // 'ssis' для строки и BLOB

            try {
                if ($stmt->execute()) {
                    $notification = "Пункт выдачи успешно добавлен.";
                } else {
                    $notification = "Ошибка при добавлении пункта выдачи: " . $stmt->error;
                }
            } catch (mysqli_sql_exception $e) {
                // Обработка ошибок превышения допустимого размера пакета
                if (strpos($e->getMessage(), 'max_allowed_packet') !== false) {
                    $notification = "Ошибка: размер данных превышает допустимый лимит на сервере.";
                } else {
                    $notification = "Ошибка базы данных: " . $e->getMessage();
                }
            }

            $stmt->close();
            $conn->close();
        }
    } else {
        $notification = "Ошибка при загрузке изображения.";
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Добавить пункт выдачи</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f9f9f9;
            padding: 20px;
        }
        .notification {
            text-align: center;
            margin-bottom: 20px;
            color: white;
        }
        .error {
            color: #d9534f; /* Красный цвет для ошибок */
        }
        .form-container {
            max-width: 400px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        input[type="text"], input[type="file"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
        button {
            padding: 10px 15px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        button:hover {
            background-color: #0056b3;
        }
        .back-btn {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-bottom: 20px;
            display: inline-block;
        }
        .back-btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

<div class="form-container">
    <a href="home_employee.php" class="back-btn">Вернуться на главную</a>

    <h1>Добавить пункт выдачи</h1>

    <?php if ($notification): ?>
        <div class="notification <?= strpos($notification, 'Ошибка') !== false ? 'error' : '' ?>">
            <?= htmlspecialchars($notification); ?>
        </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <input type="text" name="name" placeholder="Название" required>
        <input type="text" name="address" placeholder="Адрес" required>
        <input type="file" name="image" accept="image/*" required> <!-- Поле для загрузки изображения -->
        <button type="submit">Добавить</button>
    </form>
</div>

</body>
</html>
