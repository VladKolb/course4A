<?php
session_start();

// Проверяем, что пользователь вошел в систему и его роль — администратор
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    // Если пользователь не администратор, перенаправляем на главную
    header('Location: index.php');
    exit();
}


include 'db_connect.php';

// Обновление весов критериев
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['weight']) && is_array($_POST['weight'])) {
        foreach ($_POST['weight'] as $id => $weight) {
            $weight = floatval($weight); // Приводим значение к числу
            // Обновляем вес в базе данных
            $stmt = $conn->prepare("UPDATE weights SET weight = ? WHERE id = ?");
            $stmt->bind_param('di', $weight, $id);
            $stmt->execute();
            $stmt->close();
        }
    }
    // Перенаправляем обратно на страницу настроек
    $_SESSION['update_message'] = "Коэффициент успешно обновлен";
    header('Location: personalization_settings.php');
    exit();
}

// Закрываем соединение
$conn->close();
?>
