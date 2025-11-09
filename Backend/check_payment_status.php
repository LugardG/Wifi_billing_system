<?php
header('Content-Type: application/json');
require_once 'db.php';

$phone = $_GET['phone'] ?? null;
if (!$phone) {
    echo json_encode(['success' => false, 'message' => 'Missing phone']);
    exit;
}

// Look up wifi_access for this phone
$stmt = $conn->prepare("SELECT username, access_code, expiry FROM wifi_access WHERE phone = ? LIMIT 1");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'DB error: ' . $conn->error]);
    exit;
}
$stmt->bind_param('s', $phone);
$stmt->execute();
$res = $stmt->get_result();
if ($res && $res->num_rows === 1) {
    $row = $res->fetch_assoc();
    // If access_code exists and expiry in future, return it
    if (!empty($row['access_code']) && strtotime($row['expiry']) > time()) {
        echo json_encode(['success' => true, 'username' => $row['username'], 'access_code' => $row['access_code'], 'expiry' => $row['expiry']]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No active access yet']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No access record']);
}
$stmt->close();
$conn->close();
?>