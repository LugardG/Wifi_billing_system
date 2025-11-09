<?php
header("Content-Type: application/json");
include "db.php";

$sql = "SELECT customer, devices, status, expiry FROM sessions ORDER BY expiry DESC";
$result = $conn->query($sql);

$sessions = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $sessions[] = $row;
    }
}

echo json_encode($sessions);
$conn->close();
?>
