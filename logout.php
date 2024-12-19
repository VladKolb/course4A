<?php
session_start(); // Запускаем сессию

// Удаляем все данные сессии
session_unset();
session_destroy();

// Перенаправляем на страницу входа
header("Location: login.php");
exit;
?>
