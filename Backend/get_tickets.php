<?php
header("Content-Type: application/json");
include "db.php"; // assumes you have db.php that sets $conn (mysqli)

// Basic safety: ensure $conn exists
if (!isset($conn)) {
    echo json_encode(["list" => []]);
    exit;
}

$sql = "SELECT id, ticket_number, customer_number, customer_msg, category, status, created_at FROM tickets ORDER BY id DESC";
$res = $conn->query($sql);
$list = [];
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $list[] = $row;
    }
}

echo json_encode(["list" => $list]);
$conn->close();
?>
