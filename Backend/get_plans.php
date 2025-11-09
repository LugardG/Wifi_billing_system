<?php
header("Content-Type: application/json");
include "db.php";

$sql = "SELECT name, price, duration, status, actions FROM plans ORDER BY name ASC";
$result = $conn->query($sql);

$plans = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $plans[] = $row;
    }
}

echo json_encode($plans);
$conn->close();
?>
