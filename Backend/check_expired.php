<?php
require_once 'db.php';

$conn = getConnection();

// Get all expired subscriptions
$stmt = $conn->prepare("SELECT phone FROM wifi_access WHERE expiry < NOW()");
$stmt->execute();
$result = $stmt->get_result();

$expiredPhones = [];
while ($row = $result->fetch_assoc()) {
    $expiredPhones[] = $row['phone'];
}

// Delete expired access
if (count($expiredPhones) > 0) {
    $placeholders = implode(',', array_fill(0, count($expiredPhones), '?'));
    $types = str_repeat('s', count($expiredPhones));

    $deleteStmt = $conn->prepare("DELETE FROM wifi_access WHERE phone IN ($placeholders)");
    $deleteStmt->bind_param($types, ...$expiredPhones);
    $deleteStmt->execute();
    $deleteStmt->close();
}

// Log removed subscriptions to a file
$logFile = __DIR__ . '/expired_log.txt';
$logEntry = "[" . date('Y-m-d H:i:s') . "] ";

if (count($expiredPhones) > 0) {
    $logEntry .= "Removed expired subscriptions: " . implode(', ', $expiredPhones) . PHP_EOL;
    echo "Expired subscriptions removed: " . implode(', ', $expiredPhones);
} else {
    $logEntry .= "No expired subscriptions." . PHP_EOL;
    echo "No expired subscriptions.";
}

// Append to log file
file_put_contents($logFile, $logEntry, FILE_APPEND);

$conn->close();
?>
