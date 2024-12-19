<?php
session_start(); // Запускаем сессию

include 'db_connect.php'; // Включаем файл подключения к базе данных
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // Включаем режим обработки ошибок через исключения для MySQLi

// Функция для проверки данных перед добавлением
function validatePizzaData($name, $description, $price) {
    if (empty($name) || empty($description) || $price <= 0 || $price > 9999) {
        return 'Все поля обязательны для заполнения, цена должна быть больше 0 и меньше 10000.';
    }
    return null;
}

// Функция для добавления пиццы в базу данных
function addPizzaToDatabase($conn, $name, $description, $price) {
    try {
        // Подготавливаем запрос с параметрами
        $stmt = $conn->prepare("INSERT INTO pizza (name, description, price) VALUES (?, ?, ?)");
        $stmt->bind_param("ssd", $name, $description, $price);  // s - string, d - double (для цены)

        // Выполняем запрос
        $stmt->execute();
        $stmt->close();
        return true;
    } catch (mysqli_sql_exception $e) {
        error_log("Ошибка при добавлении пиццы: " . $e->getMessage());
        return false;
    }
}

// Функция для обработки ошибок и перенаправления
function handleRedirectWithMessage($message, $location) {
    $_SESSION['notification'] = $message;
    header("Location: $location");
    exit;
}

// Основной обработчик запроса
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Получаем данные из формы
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = (float)$_POST['price'];

    // Проверка данных
    $validationError = validatePizzaData($name, $description, $price);
    if ($validationError) {
        handleRedirectWithMessage($validationError, 'add_pizza_form.php');
    }

    // Добавляем пиццу в базу данных
    $isAdded = addPizzaToDatabase($conn, $name, $description, $price);
    
    if ($isAdded) {
        handleRedirectWithMessage('Пицца успешно добавлена!', 'index.php');
    } else {
        handleRedirectWithMessage('Произошла ошибка при добавлении пиццы. Пожалуйста, попробуйте снова.', 'add_pizza_form.php');
    }
}
?>
