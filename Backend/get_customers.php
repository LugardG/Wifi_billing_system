<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'db.php';

$sql = "SELECT name, plan, status FROM customers ORDER BY customer_id DESC";
$result = $conn->query($sql);

$customers = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $customers[] = $row;
    }
}

header('Content-Type: application/json');
echo json_encode($customers);
$conn->close();
?>
