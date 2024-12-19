<?php
session_start();
include 'db_connect.php';

// Установка вывода ошибок
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Проверка авторизации
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$pizza_id = $_POST['pizza_id'] ?? null;

if ($pizza_id === null) {
    echo "ID пиццы не указан.";
    exit();
}

// Получение данных о пицце
$pizza_sql = "SELECT * FROM pizza WHERE id = ?";
$stmt = $conn->prepare($pizza_sql);
$stmt->bind_param('i', $pizza_id);
$stmt->execute();
$result = $stmt->get_result();
$pizza = $result->fetch_assoc();

if (!$pizza) {
    echo "Пицца не найдена.";
    exit();
}

// Инициализация переменных для сообщений
$error_message = null;
$success_message = null;

// Функция для проверки прав доступа к директории
function check_uploads_directory($dir) {
    if (!is_dir($dir)) {
        if (!mkdir($dir, 0755, true)) {
            return "Ошибка: не удалось создать директорию для загрузки изображений.";
        }
    } elseif (!is_writable($dir)) {
        return "Ошибка: доступ к директории для загрузки изображений запрещен.";
    }
    return null; // Если все в порядке
}

// Функция для безопасного удаления файла с обработкой ошибок
function safe_unlink($file, $attempts = 5, $delay = 1) {
    for ($i = 0; $i < $attempts; $i++) {
        if (!file_exists($file)) {
            return true; // Файл уже удален или не существует
        }

        // Попытка удалить файл
        if (@unlink($file)) {
            return true; // Успешное удаление
        }

        // Если ошибка связана с правами доступа
        $error = error_get_last();
        if (strpos($error['message'], 'Permission denied') !== false) {
            return false; // Ошибка прав доступа
        }

        // Если удаление не удалось, делаем паузу перед повторной попыткой
        sleep($delay);
    }

    // Возвращаем false, если файл так и не удалось удалить
    return false;
}

// Обработка загрузки изображения
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    $image = $_FILES['image'];

    // Проверка на ошибки загрузки
    if ($image['error'] === UPLOAD_ERR_OK) {
        // Проверка размера файла
        if ($image['size'] > 3 * 1024 * 1024) {
            $error_message = "Размер файла превышает 3 МБ.";
        } else {
            // Проверка типа файла
            $allowed_types = ['image/jpeg', 'image/png'];
            if (!in_array($image['type'], $allowed_types)) {
                $error_message = "Недопустимый тип файла. Загрузите изображение в формате JPEG или PNG.";
            } else {
                // Полный путь к директории загрузки
                $target_dir = __DIR__ . "/uploads/";
                $error_message = check_uploads_directory($target_dir); // Проверка прав доступа

                // Если ошибок нет, продолжаем обработку
                if (is_null($error_message)) {
                    // Получаем путь к старому изображению
                    $old_image_path = __DIR__ . "/" . $pizza['image_url'];

                    // Удаляем старое изображение, если оно существует
                    if (file_exists($old_image_path)) {
                        if (!safe_unlink($old_image_path)) {
                            $error_message = "Сейчас нельзя заменить фото для пиццы. Попробуйте позже.";
                        }
                    }

                    if (is_null($error_message)) {
                        // Генерация уникального имени файла
                        $unique_name = uniqid('pizza_', true);
                        $extension = pathinfo($image['name'], PATHINFO_EXTENSION);
                        $target_file = $target_dir . $unique_name . '.' . $extension;

                        // Перемещение загруженного файла
                        if (move_uploaded_file($image["tmp_name"], $target_file)) {
                            // Сохраняем только относительный путь в базе данных
                            $relative_path = "uploads/" . $unique_name . '.' . $extension;

                            $update_sql = "UPDATE pizza SET image_url = ? WHERE id = ?";
                            $update_stmt = $conn->prepare($update_sql);
                            $update_stmt->bind_param('si', $relative_path, $pizza_id);
                            $update_stmt->execute();

                            if ($update_stmt->affected_rows > 0) {
                                $pizza['image_url'] = $relative_path;
                                $success_message = "Изображение успешно обновлено!";
                            } else {
                                $error_message = "Ошибка обновления изображения.";
                            }
                        } else {
                            // Если перемещение не удалось, записываем ошибку в лог и выводим сообщение пользователю
                            $error_message = "Ошибка при перемещении файла. Попробуйте еще раз позже.";
                            error_log("Ошибка загрузки файла: " . error_get_last()['message']);
                        }
                    }
                }
            }
        }
    } else {
        $error_message = "Ошибка при загрузке файла: " . $image['error'];
    }
}

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Изменить изображение пиццы</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 20px;
        }

        h1 {
            color: #333;
            margin-bottom: 20px;
        }

        img {
            max-width: 300px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        form {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
        }

        input[type="file"] {
            margin-bottom: 15px;
        }

        button {
            padding: 10px 15px;
            background-color: green;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #0056b3;
        }

        .back-button {
            display: inline-block;
            margin-top: 15px;
            padding: 10px 15px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .back-button:hover {
            background-color: #0056b3;
        }

        .success-message {
            color: green;
            margin-bottom: 20px;
            font-weight: bold;
        }

        .error-message {
            color: red;
            margin-bottom: 20px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <h1>Изменить изображение для: <?= htmlspecialchars($pizza['name']) ?></h1>
    <img src="<?= htmlspecialchars($pizza['image_url']) ?>" alt="<?= htmlspecialchars($pizza['name']) ?>">

    <?php if (isset($success_message)): ?>
        <div class="success-message"><?= $success_message ?></div>
    <?php elseif (isset($error_message)): ?>
        <div class="error-message"><?= $error_message ?></div>
    <?php endif; ?>

    <form action="" method="post" enctype="multipart/form-data">
        <input type="hidden" name="pizza_id" value="<?= htmlspecialchars($pizza_id) ?>">
        <label for="image">Выберите изображение:</label>
        <input type="file" name="image" id="image" accept="image/*" required>
        <button type="submit">Загрузить изображение</button>
    </form>

    <a href="index.php" class="back-button">Вернуться на главную</a>
</body>
</html>
