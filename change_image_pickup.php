<?php
session_start();


$is_logged_in = isset($_SESSION['user_id']);
$is_employee = $is_logged_in && $_SESSION['role'] === 'employee';

if (!$is_logged_in || !$is_employee) {
    header('Location: login.php');
    exit();
}

$notification = '';
$pickup_id = isset($_GET['pickup_id']) ? intval($_GET['pickup_id']) : 0;

// Подключаемся к базе данных
include 'db_connect.php';

// Получаем данные о пункте выдачи
if ($pickup_id > 0) {
    $sql = "SELECT * FROM pickup_points WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $pickup_id);
    $stmt->execute();
    $pickup = $stmt->get_result()->fetch_assoc();

    if (!$pickup) {
        die('Пункт выдачи не найден.');
    }
}

// Обработка формы обновления изображения
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

            if ($image_data === false) {
                $notification = "Ошибка: не удалось получить данные изображения.";
            } else {
                // Обновляем изображение в базе данных
                $sql = "UPDATE pickup_points SET image = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('si', $image_data, $pickup_id); // 'b' для BLOB, 'i' для целого числа

                try {
                    if ($stmt->execute()) {
                        if ($stmt->affected_rows > 0) {
                            $notification = "Изображение успешно обновлено.";
                            header("Location: home_employee.php"); // Перенаправление на главную страницу
                            exit(); // Завершение выполнения скрипта
                        } else {
                            $notification = "Изображение не было обновлено. Возможно, оно не изменилось.";
                        }
                    } else {
                        $notification = "Ошибка при обновлении изображения: " . $stmt->error;
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
            }
        }
    } else {
        // Уведомление об ошибке загрузки изображения
        $upload_error = $_FILES['image']['error'];
        switch ($upload_error) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $notification = "Ошибка: размер файла превышает допустимый лимит.";
                break;
            case UPLOAD_ERR_PARTIAL:
                $notification = "Ошибка: файл был загружен частично.";
                break;
            case UPLOAD_ERR_NO_FILE:
                $notification = "Ошибка: файл не был загружен.";
                break;
            default:
                $notification = "Ошибка при загрузке изображения: код ошибки $upload_error.";
                break;
        }
    }

    $conn->close(); // Закрытие соединения
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Обновить изображение пункта выдачи</title>
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
            color: white;
        }
        .form-container {
            max-width: 400px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        input[type="file"] {
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
    position: absolute;
    top: 20px; /* Расстояние от верхней части страницы */
    left: 20px; /* Расстояние от левого края страницы */
    padding: 10px 20px;
    background-color: #007bff;
    color: white;
    text-decoration: none;
    border-radius: 5px;
    margin-bottom: 20px;
    display: inline-block;
    z-index: 1000; /* Чтобы кнопка отображалась поверх других элементов */
}

.back-btn:hover {
    background-color: #0056b3;
}
        .current-image {
            text-align: center;
            margin-bottom: 20px;
        }
        .current-image img {
            max-width: 100%;
            height: auto;
            border-radius: 5px;
        }
    </style>
</head>
<body>

<div class="form-container">
    <a href="home_employee.php" class="back-btn">Вернуться на главную</a>

    <h1>Обновить изображение для пункта выдачи: <?= htmlspecialchars($pickup['name']); ?></h1>

    <?php if ($notification): ?>
        <div class="notification <?= strpos($notification, 'Ошибка') !== false ? 'error' : '' ?>">
            <?= htmlspecialchars($notification); ?>
        </div>
    <?php endif; ?>

    <!-- Отображение текущего изображения -->
    <?php if (!empty($pickup['image'])): ?>
        <div class="current-image">
            <h3>Текущее изображение:</h3>
            <img src="data:image/jpeg;base64,<?= base64_encode($pickup['image']); ?>" alt="Текущее изображение">
        </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <input type="file" name="image" accept="image/*" required>
        <button type="submit">Обновить</button>
    </form>
</div>

</body>
</html>
