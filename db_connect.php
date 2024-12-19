<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $host = 'localhost';  
    $user = 'root';       
    $password = '';       
    $dbname = 'pizzeria'; 

    $conn = new mysqli($host, $user, $password, $dbname);

    $conn->set_charset('utf8');

} catch (mysqli_sql_exception $e) {
    
    echo "Ошибка подключения к базе данных: Пожалуйста, проверьте настройки подключения.";

    exit(); 
}
?>
