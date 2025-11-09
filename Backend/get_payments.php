<?php
header("Content-Type: application/json");
include "db.php";

$sql = "SELECT customer, amount, date, method FROM payments ORDER BY date DESC";
$result = $conn->query($sql);

$payments = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $payments[] = $row;
    }
}

echo json_encode($payments);
$conn->close();
?>
