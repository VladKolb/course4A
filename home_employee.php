<?php
session_start();

$is_logged_in = isset($_SESSION['user_id']);
$is_employee = $is_logged_in && $_SESSION['role'] === 'employee'; // Проверка, что пользователь — сотрудник

$notification = '';
if (isset($_SESSION['notification'])) {
    $notification = $_SESSION['notification'];
    unset($_SESSION['notification']); // Удаляем сообщение после его использования
}

// Перенаправление, если пользователь не авторизован или не является сотрудником
if (!$is_logged_in || !$is_employee) {
    header('Location: login.php');
    exit();
}

// Подключаемся к базе данных
include 'db_connect.php';

// Получаем ID текущего сотрудника
$employee_id = $_SESSION['user_id'];

// Получаем только пункты выдачи, принадлежащие текущему сотруднику
$pickup_sql = "SELECT * FROM pickup_points WHERE employee_id = ?";
$stmt = $conn->prepare($pickup_sql);
$stmt->bind_param('i', $employee_id);
$stmt->execute();
$pickup_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&family=Montserrat:wght@600&display=swap">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Пиццерия - Пункты выдачи</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f9f9f9;
        }

        h1 {
            margin-top: 80px;
        }

        .edit-btn,
        .delete-btn {
            padding: 8px 15px;
            margin-right: 10px;
            margin-bottom: 5px;
            display: inline-block;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            color: white;
            font-family: 'Roboto', sans-serif;
            transition: background-color 0.3s ease;
        }

        .edit-btn {
            background-color: #4CAF50;
        }

        .edit-btn:hover {
            background-color: #45a049;
        }

        .delete-btn {
            background-color: #f44336;
        }

        .delete-btn:hover {
            background-color: #e53935;
        }

        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
        }

        .auth-buttons {
            position: absolute;
            top: 0;
            right: 0;
            display: flex;
            gap: 10px;
        }

        .auth-btn {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .auth-btn:hover {
            background-color: #0056b3;
        }

        .notification {
            text-align: center;
            margin-top: 20px;
            font-size: 1.2em;
            color: white;
            /* Красный цвет для уведомлений */
        }

        /* Стиль модального окна */
        .modal-overlay {
            display: none;
            /* Скрыто по умолчанию */
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: white;
            padding: 20px;
            border-radius: 5px;
            text-align: center;
            position: relative;
            max-width: 500px;
            width: 80%;
        }

        /* Класс для показа модального окна */
        input[type="checkbox"]:checked+.modal-overlay {
            display: flex;
            /* Показывать при открытии */
        }

        .modal-close {
            cursor: pointer;
            color: #007BFF;
            text-decoration: underline;
            display: inline-block;
            margin-top: 10px;
        }

        .change-image-btn {
            display: inline-block;
            padding: 10px 15px;
            margin-top: 10px;
            border: none;
            border-radius: 5px;
            background-color: #FFA500;
            /* Цвет для кнопки изменения изображения */
            color: white;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }

        .change-image-btn:hover {
            background-color: #FF8C00;
            /* Цвет при наведении */
        }

        /* Стиль модального окна */
        .modal-overlay {
            display: none;
            /* Скрыто по умолчанию */
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: white;
            padding: 20px;
            border-radius: 5px;
            text-align: center;
            position: relative;
            max-width: 500px;
            width: 80%;
        }

        .modal-content img {
            width: 100%;
            /* Займет всю ширину модального окна */
            max-width: 400px;
            /* Максимальная ширина картинки */
            max-height: 300px;
            /* Максимальная высота картинки */
            object-fit: cover;
            /* Сохранит пропорции, но заполнит область */
            border-radius: 5px;
            /* Скруглим углы, если нужно */
        }

        /* Класс для показа модального окна */
        input[type="checkbox"]:checked+.modal-overlay {
            display: flex;
            /* Показывать при открытии */
        }

        .modal-close {
            cursor: pointer;
            color: #007BFF;
            text-decoration: underline;
            display: inline-block;
            margin-top: 10px;
        }
    </style>
</head>

<body>
    <div class="header-container">
        <div class="auth-buttons">
            <?php if ($is_logged_in && $is_employee): ?>
                <a href="order_list.php" class="auth-btn">Список заказов</a>
                <a href="logout.php" class="auth-btn">Выйти</a>
            <?php else: ?>
                <a href="login.php" class="auth-btn">Войти</a>
            <?php endif; ?>
        </div>
    </div>

    <h1>Добро пожаловать в нашу Пиццерию!</h1>

    <?php if ($notification): ?>
        <div class="notification">
            <?php echo htmlspecialchars($notification); ?>
        </div>
    <?php endif; ?>

    <h2 style="text-align: center;">Пункты выдачи</h2>

    <div class="button-container">
        <a href="add_pickup.php" class="edit-btn">Добавить пункт выдачи</a>
    </div>

    <?php if ($pickup_result->num_rows > 0): ?>
        <table>
            <tr>
                <th>ID</th>
                <th>Название пункта выдачи</th>
                <th>Адрес</th>
                <th>Действия</th>
            </tr>
            <?php while ($pickup = $pickup_result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($pickup['id']); ?></td>
                    <td>
                        <label for="modal-<?= $pickup['id'] ?>" style="cursor: pointer; color: #007BFF; font-weight: bold;">
                            <?= htmlspecialchars($pickup['name']); ?>
                        </label>

                        <!-- Модальное окно -->
                        <input type="checkbox" id="modal-<?= $pickup['id'] ?>" style="display: none;">
                        <div class="modal-overlay" id="modal-<?= $pickup['id'] ?>">
                            <div class="modal-content">
                                <h2 class="modal-header"><?= htmlspecialchars($pickup['name']); ?></h2>
                                <?php if ($pickup['image']): ?>
                                    <?php $base64_image = base64_encode($pickup['image']); ?>
                                    <img src="data:image/jpeg;base64,<?= $base64_image ?>" alt="Изображение пункта выдачи">
                                <?php else: ?>
                                    <p>Изображение не загружено</p>
                                <?php endif; ?>
                                <p><?= htmlspecialchars($pickup['address']); ?></p>
                                <a href="change_image_pickup.php?pickup_id=<?= htmlspecialchars($pickup['id']); ?>"
                                    class="change-image-btn">Изменить изображение</a>
                                <label for="modal-<?= $pickup['id'] ?>" class="modal-close">Закрыть</label>
                            </div>
                        </div>

                    </td>
                    <td><?= htmlspecialchars($pickup['address']); ?></td>
                    <td>
                        <form method="post" action="confirm_delete_pickup.php">
                            <input type="hidden" name="pickup_id" value="<?= htmlspecialchars($pickup['id']); ?>">
                            <button type="submit" class="delete-btn">Удалить</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p style="text-align: center;">Пункты выдачи не найдены.</p>
    <?php endif; ?>

</body>

</html>