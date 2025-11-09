<?php
header('Content-Type: application/json');
require_once 'db.php';

$username = $_POST['username'] ?? null;
$code = $_POST['code'] ?? null;

if (!$username || !$code) {
    echo json_encode(['success' => false, 'message' => 'Missing username or code']);
    exit;
}

$stmt = $conn->prepare("SELECT plan, expiry, access_code FROM wifi_access WHERE username = ? LIMIT 1");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'DB error: ' . $conn->error]);
    exit;
}
$stmt->bind_param('s', $username);
$stmt->execute();
$res = $stmt->get_result();
if ($res && $res->num_rows === 1) {
    $row = $res->fetch_assoc();
    if ($row['access_code'] === $code) {
        if ($row['expiry'] && strtotime($row['expiry']) > time()) {
            echo json_encode(['success' => true, 'plan' => $row['plan'], 'expiry' => $row['expiry']]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Subscription expired']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid access code']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No access found for this username']);
}
$stmt->close();
$conn->close();
?>