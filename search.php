<?php
include 'db_connect.php';

$query = $conn->real_escape_string($_GET['query']);
$result = $conn->query("SELECT * FROM pizza WHERE name LIKE '%$query%'");

echo "<table border='1'>";
echo "<tr><th>Название</th><th>Описание</th><th>Цена</th></tr>";

while ($row = $result->fetch_assoc()) {
    echo "<tr><td>" . $row['name'] . "</td><td>" . $row['description'] . "</td><td>" . $row['price'] . "</td></tr>";
}

echo "</table>";
?>
