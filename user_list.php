<?php
session_start();

// Проверка, является ли пользователь администратором
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

// Подключение к базе данных
include 'db_connect.php';

if (isset($_SESSION['notification'])): ?>
    <div class="notification">
        <?= htmlspecialchars($_SESSION['notification']); ?>
    </div>
    <?php unset($_SESSION['notification']); ?>
<?php endif; 

// Получаем список всех пользователей
$sql = "SELECT * FROM users";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&family=Montserrat:wght@600&display=swap">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Список пользователей</title>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        .header {
            display: flex;
            justify-content: flex-start;
            padding: 20px;
        }
        .back-btn {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }
        .back-btn:hover {
            background-color: #0056b3;
        }
        .container {
            max-width: 100%; /* Задаем максимальную ширину 100% */
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: #333;
            font-family: 'Montserrat', sans-serif;
        }
        table {
            width: 100%; /* Устанавливаем ширину таблицы на 100% */
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            overflow: hidden; /* Прячем переполнение ячеек */
            text-overflow: ellipsis; /* Добавляем многоточие для длинного текста */
            white-space: nowrap; /* Не переносим текст на новую строку */
        }
        th {
            background-color: #007bff;
            color: white;
        }
        .action-buttons button {
            padding: 5px 10px;
            margin-right: 5px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .approve-btn {
            background-color: #28a745;
            color: white;
        }
        .approve-btn:hover {
            background-color: #218838;
        }
        .deny-btn {
            background-color: #dc3545;
            color: white;
        }
        .deny-btn:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>

<div class="header">
    <a href="index.php" class="back-btn">Вернуться на главную страницу</a>
</div>

<div class="container">
    <h1>Список пользователей</h1>

    <table>
        <tr>
            <th>ID</th>
            <th>Email</th>
            <th>Адрес</th>
            <th>Телефон</th>
            <th>Роль</th>
            <th>Запрос на смену роли</th>
            <th>Изменить роль</th>
            <th>Действия</th>
        </tr>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['id']); ?></td>
                    <td><?= htmlspecialchars($row['email']); ?></td>
                    <td><?= htmlspecialchars($row['address']); ?></td>
                    <td><?= htmlspecialchars($row['phone']); ?></td>
                    <td><?= htmlspecialchars($row['role']); ?></td>
                    <td><?= htmlspecialchars($row['role_request']); ?></td>
                    <td>
                        <form method="post" action="update_user_role.php" style="display:inline;">
                            <input type="hidden" name="user_id" value="<?= $row['id']; ?>">
                            <select name="new_role">
                                <option value="user" <?= $row['role'] === 'user' ? 'selected' : ''; ?>>Пользователь</option>
                                <option value="admin" <?= $row['role'] === 'admin' ? 'selected' : ''; ?>>Администратор</option>
                                <option value="employee" <?= $row['role'] === 'employee' ? 'selected' : ''; ?>>Сотрудник</option>
                            </select>
                            <button type="submit" class="approve-btn">Изменить</button>
                        </form>
                    </td>
                    <td class="action-buttons">
                        <?php if ($row['role_request'] === 'pending'): ?>
                            <form method="post" action="process_role_request.php" style="display:inline;">
                                <input type="hidden" name="user_id" value="<?= $row['id']; ?>">
                                <button type="submit" name="action" value="approve" class="approve-btn">Одобрить</button>
                            </form>
                            <form method="post" action="process_role_request.php" style="display:inline;">
                                <input type="hidden" name="user_id" value="<?= $row['id']; ?>">
                                <button type="submit" name="action" value="deny" class="deny-btn">Отклонить</button>
                            </form>
                        <?php else: ?>
                            <span>Нет активного запроса</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="8">Пользователи не найдены.</td>
            </tr>
        <?php endif; ?>
    </table>
</div>

</body>
</html>
