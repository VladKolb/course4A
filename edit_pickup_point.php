<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['notification'] = "У вас нет прав для редактирования пунктов выдачи.";
    header("Location: index.php");
    exit();
}

$pickup_point_id = $_GET['id'] ?? null;
$pickup_point = null;

if ($pickup_point_id) {
    $sql = "SELECT * FROM pickup_points WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $pickup_point_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $pickup_point = $result->fetch_assoc();
    } else {
        $_SESSION['notification'] = "Пункт выдачи не найден.";
        header("Location: index.php");
        exit();
    }

    $stmt->close();
} else {
    $_SESSION['notification'] = "ID пункта выдачи не передан.";
    header("Location: index.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Редактировать пункт выдачи</title>
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

        input {
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
</head>
<body>
    <div class="container">
        <a href="index.php" class="back-btn">Вернуться назад</a>
        <h1>Редактировать пункт выдачи</h1>
        <form method="post" action="update_pickup_point.php">
            <input type="hidden" name="pickup_point_id" value="<?php echo htmlspecialchars($pickup_point['id']); ?>">
            
            <label for="name">Название пункта:</label>
            <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($pickup_point['name']); ?>" required>
            
            <label for="address">Адрес пункта:</label>
            <input type="text" name="address" id="address" value="<?php echo htmlspecialchars($pickup_point['address']); ?>" required>

            <button type="submit">Сохранить изменения</button>
        </form>
    </div>
</body>
</html>
