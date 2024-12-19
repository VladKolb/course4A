<?php
session_start();

// Проверяем, является ли пользователь сотрудником
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employee') {
    header('Location: login.php');
    exit();
}

// Подключаемся к базе данных
include 'db_connect.php';

// Обработка изменения статуса заказа
if (isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['status'];

    $update_sql = "UPDATE orders SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param('si', $new_status, $order_id);

    if ($stmt->execute()) {
        $_SESSION['notification'] = "Статус заказа №{$order_id} обновлен на '{$new_status}'.";
    } else {
        $_SESSION['notification'] = "Ошибка обновления статуса заказа.";
    }

    $stmt->close();
    header('Location: order_list.php');
    exit();
}

// Получаем ID текущего сотрудника
$employee_id = $_SESSION['user_id'];

// Получаем только заказы, относящиеся к пунктам выдачи текущего сотрудника
$sql = "SELECT o.id, o.user_id, o.address, o.phone, o.order_date, o.status, u.phone AS user_phone
        FROM orders o
        JOIN users u ON o.user_id = u.id
        JOIN pickup_points p ON o.pickup_point_id = p.id
        WHERE p.employee_id = ?
        ORDER BY o.order_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $employee_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Список заказов</title>
    <link rel="stylesheet" href="styles.css">

    <style>
        .back-btn {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-bottom: 20px;
            display: inline-block;
        }

        .back-btn:hover {
            background-color: #0056b3;
        }

        .update-btn {
        padding: 10px 15px;
        background-color: #28a745;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s ease;
        font-weight: bold;
    }

    .update-btn:hover {
        background-color: #218838;
    }

    select {
        padding: 10px;
        border: 2px solid #007bff;
        border-radius: 5px;
        background-color: #f8f9fa;
        color: #495057;
        font-size: 16px;
        cursor: pointer;
        transition: border-color 0.3s ease, background-color 0.3s ease;
    }

    select:focus {
        border-color: #0056b3; /* Цвет границы при фокусе */
        background-color: #e9ecef; /* Цвет фона при фокусе */
        outline: none; /* Убираем стандартный outline */
    }
    </style>

</head>

<body>
    <h1>Список заказов</h1>

    <?php if (isset($_SESSION['notification'])): ?>
        <div class="notification">
            <?= htmlspecialchars($_SESSION['notification']) ?>
        </div>
        <?php unset($_SESSION['notification']); ?>
    <?php endif; ?>

    <a href="index.php" class="back-btn">Вернуться на главную</a>

    <?php if ($result->num_rows > 0): ?>
        <table>
            <tr>
                <th>Номер заказа</th>
                <th>Телефон клиента</th>
                <th>Адрес</th>
                <th>Телефон</th>
                <th>Дата заказа</th>
                <th>Статус</th>
                <th>Действия</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['id']) ?></td>
                    <td><?= htmlspecialchars($row['user_phone']) ?></td>
                    <td><?= htmlspecialchars($row['address']) ?></td>
                    <td><?= htmlspecialchars($row['phone']) ?></td>
                    <td><?= htmlspecialchars($row['order_date']) ?></td>
                    <td><?= htmlspecialchars($row['status']) ?></td>
                    <td>
                        <form method="post">
                            <input type="hidden" name="order_id" value="<?= $row['id'] ?>">
                            <select name="status">
                                <option value="В ожидании" <?= $row['status'] === 'В ожидании' ? 'selected' : '' ?>>В ожидании
                                </option>
                                <option value="Принят" <?= $row['status'] === 'Принят' ? 'selected' : '' ?>>Принят</option>
                                <option value="Отменен" <?= $row['status'] === 'Отменен' ? 'selected' : '' ?>>Отменен</option>
                                <option value="Готовится" <?= $row['status'] === 'Готовится' ? 'selected' : '' ?>>Готовится
                                </option>
                                <option value="Выдан" <?= $row['status'] === 'Выдан' ? 'selected' : '' ?>>Выдан</option>
                            </select>

                            <button type="submit" name="update_status" class="update-btn">Обновить статус</button>

                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>Заказы не найдены.</p>
    <?php endif; ?>

    <?php $stmt->close(); ?>
    <?php $conn->close(); ?>
</body>

</html>