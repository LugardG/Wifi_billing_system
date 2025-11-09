<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $status = $_POST['status']; // "active" or "inactive"

    $stmt = $conn->prepare("UPDATE subscriptions SET status=? WHERE id=?");
    $stmt->bind_param("si", $status, $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Plan status updated']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Status update failed']);
    }
}
?>
