<?php
session_start();
include 'db_connect.php';

// Проверяем, передан ли ID пункта выдачи
if (isset($_POST['pickup_id'])) {
    $pickup_id = (int)$_POST['pickup_id']; // Приводим к целому числу для безопасности

    // Получаем данные пункта выдачи для отображения его имени
    $sql = "SELECT address FROM pickup_points WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $pickup_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $pickup = $result->fetch_assoc();

    // Если пункт выдачи не найден
    if (!$pickup) {
        echo "Пункт выдачи не найден.";
        exit();
    }
} else {
    echo "Некорректный запрос.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Подтверждение удаления</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .confirmation-container {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 100%;
            max-width: 400px;
        }
        .confirmation-container h2 {
            margin-bottom: 20px;
        }
        .confirmation-container form {
            margin-bottom: 20px;
        }
        .confirmation-container button {
            background-color: #f44336;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-right: 10px;
            transition: background-color 0.3s ease;
        }
        .confirmation-container button:hover {
            background-color: #e53935;
        }
        .confirmation-container a {
            color: #007bff;
            text-decoration: none;
            font-size: 16px;
        }
        .confirmation-container a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="confirmation-container">
    <h2>Вы уверены, что хотите удалить пункт выдачи "<?= htmlspecialchars($pickup['address']) ?>"?</h2>

    <form method="POST" action="delete_pickup.php">
        <input type="hidden" name="pickup_id" value="<?= htmlspecialchars($pickup_id); ?>">
        <button type="submit">Удалить</button>
        <a href="home_employee.php">Отмена</a>
    </form>
</div>

</body>
</html>
