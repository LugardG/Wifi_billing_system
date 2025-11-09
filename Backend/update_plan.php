<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $price = $_POST['price'];
    $duration = $_POST['duration'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE subscriptions SET name=?, price=?, duration=?, status=? WHERE id=?");
    $stmt->bind_param("sdisi", $name, $price, $duration, $status, $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Plan updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Update failed']);
    }
}
?>
