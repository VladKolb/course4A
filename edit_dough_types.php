<?php
session_start();

// Проверка, что пользователь — админ
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

// Подключаемся к базе данных
include 'db_connect.php';

// Обработка формы добавления нового типа теста
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dough_name'])) {
    $dough_name = $_POST['dough_name'];

    $insert_sql = "INSERT INTO dough_types (dough_name) VALUES (?)";
    $stmt = $conn->prepare($insert_sql);
    $stmt->bind_param('s', $dough_name);
    $stmt->execute();
    $stmt->close();

    $_SESSION['notification'] = "Тип теста успешно добавлен.";
}

// Получаем все типы теста
$dough_sql = "SELECT * FROM dough_types";
$dough_result = $conn->query($dough_sql);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактировать типы теста</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f8f8f8;
            margin: 0;
            padding: 20px;
        }

        h1, h2 {
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

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
            background-color: #fff;
        }

        th {
            background-color: #4CAF50;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        tr:hover {
            background-color: #ddd;
        }
    </style>
</head>
<body>

<a href="index.php" class="back-button">Вернуться на главную страницу</a>

<h1>Редактировать типы теста</h1>

<?php if (isset($_SESSION['notification'])): ?>
    <div class="notification">
        <?php echo htmlspecialchars($_SESSION['notification']); ?>
        <?php unset($_SESSION['notification']); ?>
    </div>
<?php endif; ?>

<form method="post">
    <input type="text" name="dough_name" placeholder="Введите название типа теста" required>
    <button type="submit">Добавить тип теста</button>
</form>

<h2>Существующие типы теста</h2>
<table>
    <tr>
        <th>ID</th>
        <th>Название</th>
        <th>Действия</th>
    </tr>
    <?php while ($row = $dough_result->fetch_assoc()): ?>
    <tr>
        <td><?php echo htmlspecialchars($row['id']); ?></td>
        <td><?php echo htmlspecialchars($row['dough_name']); ?></td>
        <td>
            <a href="edit_dough_type_form.php?id=<?php echo $row['id']; ?>">Редактировать</a>
            <a href="delete_dough_type.php?id=<?php echo $row['id']; ?>">Удалить</a>
        </td>
    </tr>
    <?php endwhile; ?>
</table>

</body>
</html>

<?php
$conn->close();
?>
