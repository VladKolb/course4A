<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$pizza_id = $_POST['pizza_id'] ?? null;
$image = $_FILES['image'] ?? null;

// Проверяем, что ID пиццы и файл изображения заданы
if ($pizza_id === null || $image === null) {
    echo "Недостаточно данных для загрузки изображения.";
    exit();
}

// Проверяем, была ли загружена ошибка
if ($image['error'] !== UPLOAD_ERR_OK) {
    echo "Ошибка при загрузке изображения.";
    exit();
}

// Проверка типа файла: разрешаем только JPEG и PNG
$allowed_types = ['image/jpeg', 'image/png'];
if (!in_array($image['type'], $allowed_types)) {
    echo "Недопустимый тип файла. Загрузите изображение в формате JPEG или PNG.";
    exit();
}

// Создаем уникальное имя для загружаемого файла
$upload_dir = 'uploads/'; // Папка для хранения изображений
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true); // Создаем папку, если она не существует
}

$image_name = uniqid('pizza_', true) . '.' . pathinfo($image['name'], PATHINFO_EXTENSION);
$image_path = $upload_dir . $image_name;

// Перемещаем загруженный файл
if (move_uploaded_file($image['tmp_name'], $image_path)) {
    // Обновляем URL изображения в базе данных
    $update_sql = "UPDATE pizza SET image_url = ? WHERE id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param('si', $image_path, $pizza_id);
    
    if ($stmt->execute()) {
        echo "Изображение успешно обновлено.";
    } else {
        echo "Ошибка при обновлении изображения в базе данных.";
    }
} else {
    echo "Ошибка при перемещении загруженного файла.";
}

$stmt->close();
$conn->close();
?>
