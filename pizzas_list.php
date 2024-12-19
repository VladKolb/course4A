<?php

include 'db_connect.php';

$result = $conn->query("SELECT * FROM pizza");

if ($result->num_rows > 0) {
    echo "<h1>Список пицц</h1>";
    echo "<table border='1'>
            <tr>
                <th>Название</th>
                <th>Описание</th>
                <th>Цена</th>
            </tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>{$row['name']}</td>
                <td>{$row['description']}</td>
                <td>{$row['price']} руб.</td>
              </tr>";
    }
    
    echo "</table>";
} else {
    echo "Нет пицц в базе данных.";
}

$conn->close();
?>
