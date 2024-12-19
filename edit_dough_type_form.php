<?php
session_start();

// Проверка, что пользователь — админ
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

// Подключаемся к базе данных
include 'db_connect.php';

// Проверяем, передан ли ID типа теста
if (!isset($_GET['id'])) {
    header('Location: edit_dough_types.php');
    exit;
}

$dough_id = (int)$_GET['id'];

// Получаем данные типа теста
$sql = "SELECT * FROM dough_types WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $dough_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: edit_dough_types.php');
    exit;
}

$dough = $result->fetch_assoc();

// Обработка формы редактирования
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dough_name'])) {
    $dough_name = $_POST['dough_name'];

    $update_sql = "UPDATE dough_types SET dough_name = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param('si', $dough_name, $dough_id);
    $update_stmt->execute();
    $update_stmt->close();

    $_SESSION['notification'] = "Тип теста успешно обновлен.";
    header('Location: edit_dough_types.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Редактировать тип теста</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f8f8f8;
            margin: 0;
            padding: 20px;
        }

        h1 {
            text-align: center;
            color: #333;
        }

        .notification {
            background-color: #4CAF50;
            color: white;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            text-align: center;
        }

        .back-button {
            background-color: #007bff;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 5px;
            margin-bottom: 20px;
            display: inline-block;
            position: absolute; /* Позиционируем кнопку */
            top: 20px; /* Отступ сверху */
            left: 20px; /* Отступ слева */
        }

        .back-button:hover {
            background-color: #0056b3;
        }

        form {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }

        input[type="text"] {
            padding: 10px;
            border-radius: 4px;
            border: 1px solid #ddd;
            margin-right: 10px;
        }

        button {
            padding: 10px 15px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
        }

        button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

<a href="edit_dough_types.php" class="back-button">Вернуться назад</a>

<h1>Редактировать тип теста</h1>

<?php if (isset($_SESSION['notification'])): ?>
    <div class="notification">
        <?php echo htmlspecialchars($_SESSION['notification']); ?>
        <?php unset($_SESSION['notification']); ?>
    </div>
<?php endif; ?>

<form method="post">
    <input type="text" name="dough_name" value="<?php echo htmlspecialchars($dough['dough_name']); ?>" required>
    <button type="submit">Обновить тип теста</button>
</form>

</body>
</html>

<?php
$conn->close();
?>
