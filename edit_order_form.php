<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['notification'] = "У вас нет прав для редактирования заказов.";
    header("Location: index.php");
    exit();
}

$order_id = $_GET['id'] ?? null;
$order = null;

if ($order_id) {
    $sql = "SELECT * FROM orders WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $order_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $order = $result->fetch_assoc();
    } else {
        $_SESSION['notification'] = "Заказ не найден.";
        header("Location: index.php");
        exit();
    }

    $stmt->close();
} else {
    $_SESSION['notification'] = "ID заказа не передан.";
    header("Location: index.php");
    exit();
}

// Получаем существующие пункты выдачи
$pickup_points = [];
$pickup_sql = "SELECT id, name FROM pickup_points";
$pickup_result = $conn->query($pickup_sql);

while ($row = $pickup_result->fetch_assoc()) {
    $pickup_points[] = $row;
}

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Редактировать заказ</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f4f4f4;
        }

        .container {
            position: relative;
            width: 100%;
            max-width: 600px;
            background-color: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .back-btn {
            position: absolute;
            top: 20px;
            left: 20px;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .back-btn:hover {
            background-color: #0056b3;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        label {
            font-size: 14px;
        }

        input, select {
            padding: 10px;
            font-size: 16px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        button {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #45a049;
        }
    </style>
    <script>
        function validatePhone() {
            const phoneInput = document.getElementById('phone');
            const phoneValue = phoneInput.value;
            const phonePattern = /^\d{0,10}$/; // Только цифры, максимум 10 символов
            if (!phonePattern.test(phoneValue)) {
                alert("Телефон должен содержать только цифры и не более 10 символов.");
                return false;
            }
            return true;
        }
    </script>
</head>
<body>
    <div class="container">
        <a href="index.php" class="back-btn">Вернуться назад</a>
        <h1>Редактировать заказ</h1>
        <form method="post" action="update_order.php" onsubmit="return validatePhone()">
            <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order['id']); ?>">
            
            <label for="address">Адрес:</label>
            <input type="text" name="address" id="address" value="<?php echo htmlspecialchars($order['address']); ?>" required>
            
            <label for="phone">Телефон:</label>
            <input type="text" name="phone" id="phone" value="<?php echo htmlspecialchars($order['phone']); ?>" required maxlength="10" pattern="\d{0,10}">
            
            <label for="order_date">Дата заказа:</label>
            <input type="datetime-local" name="order_date" id="order_date" value="<?php echo date('Y-m-d\TH:i', strtotime($order['order_date'])); ?>" required min="<?php echo date('Y-m-d\TH:i'); ?>">

            <label for="pickup_point_id">Пункт выдачи:</label>
            <select name="pickup_point_id" id="pickup_point_id" required>
                <option value="">-- Выберите пункт выдачи --</option>
                <?php foreach ($pickup_points as $point): ?>
                    <option value="<?php echo htmlspecialchars($point['id']); ?>" <?php echo ($point['id'] == $order['pickup_point_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($point['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit">Сохранить изменения</button>
        </form>
    </div>
</body>
</html>
