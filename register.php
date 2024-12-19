<?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    if (empty($email) || empty($password) || empty($confirm_password)) {
        echo "Пожалуйста, заполните все поля.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Некорректный email.";
    } elseif ($password !== $confirm_password) {
        echo "Пароли не совпадают.";
    } else {

        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            echo "Пользователь с таким email уже существует.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
            $stmt->bind_param("ss", $email, $hashed_password);

            if ($stmt->execute()) {
                echo "Регистрация прошла успешно! Теперь вы можете <a href='login.php'>войти</a>.";
            } else {
                echo "Ошибка при регистрации.";
            }
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация</title>
    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f8f9fa;
            color: #333;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            display: flex;
            flex-direction: column;
            align-items: center;
            max-width: 400px;
            width: 100%;
            padding: 20px;
            box-sizing: border-box;
        }

        h2 {
            margin: 0 0 20px 0;
        }

        form {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            padding: 20px;
            width: 100%;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }

        input[type="email"], input[type="password"] {
            width: calc(100% - 20px);
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-bottom: 15px;
        }

        button {
            background-color: #007bff;
            border: none;
            color: white;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
        }

        button:hover {
            background-color: #0056b3;
        }

        .message {
            text-align: center;
            margin-top: 20px;
        }

        .message a {
            color: #007bff;
            text-decoration: none;
        }

        .message a:hover {
            text-decoration: underline;
        }
    </style>


</head>
<body>
    <div class="container">
        <h2>Регистрация</h2>
        <form action="register.php" method="post">
            <label for="email">Email:</label>
            <input type="email" name="email" required>
            
            <label for="password">Пароль:</label>
            <input type="password" name="password" required>
            
            <label for="confirm_password">Подтвердите пароль:</label>
            <input type="password" name="confirm_password" required>
            
            <button type="submit">Зарегистрироваться</button>
        </form>

        <a href="login.php" class="back-button">Вернуться назад </a>

        <div class="message">
            
        </div>
    </div>
</body>
</html>
