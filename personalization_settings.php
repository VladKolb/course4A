<?php
session_start();

// Проверяем, что пользователь вошел в систему и его роль — администратор
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    // Если пользователь не администратор, перенаправляем на главную
    header('Location: index.php');
    exit();
}

include 'db_connect.php';

// Получение критериев и весов из базы данных
$sql = "SELECT id, criterion, weight FROM weights";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Настройки персонализации</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f9f9f9;
        }
        h1 {
            color: #333;
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ccc;
        }
        th {
            background-color: #f2f2f2;
        }
        input[type="number"] {
            width: 80px;
            padding: 5px;
            border: 1px solid #ccc;
        }
        .message {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            padding: 10px;
            margin: 20px 0;
            border-radius: 5px;
            text-align: center;
        }
        .button {
            display: inline-block;
            padding: 10px 15px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
        .submit-button {
            padding: 10px 15px;
            background-color: #28a745; /* Цвет для кнопки обновления */
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 20px;
        }
        .submit-button:hover {
            background-color: #218838; /* Цвет для кнопки обновления при наведении */
        }
        .center {
            text-align: center; /* Центрирование содержимого */
            margin-top: 20px; /* Добавить отступ сверху */
        }
    </style>
</head>
<body>

<h1>Настройки персонализации</h1>

<?php
// Проверяем, есть ли сообщение об обновлении
if (isset($_SESSION['update_message'])) {
    echo "<div class='message'>{$_SESSION['update_message']}</div>";
    unset($_SESSION['update_message']); // Удаляем сообщение из сессии
}
?>

<form method="post" action="update_weights.php">
    <table>
        <tr>
            <th>Критерий</th>
            <th>Вес</th>
        </tr>
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['criterion']}</td>
                        <td>
                            <input type='number' step='0.1' name='weight[{$row['id']}]' value='{$row['weight']}'>
                        </td>
                    </tr>";
            }
        } else {
            echo "<tr><td colspan='2'>Нет критериев для отображения</td></tr>";
        }
        ?>
    </table>
    <div class="center">
        <button type="submit" class="submit-button">Обновить коэффициенты</button> <!-- Обернули кнопку в div для центрирования -->
    </div>
</form>

<a class="button" href="index.php">Вернуться на главную</a>

</body>
</html>

<?php
// Закрываем соединение
$conn->close();
?>
