<?php
header('Content-Type: application/json');
require_once 'db.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$username = isset($input['username']) ? trim($input['username']) : '';
$newPassword = isset($input['newPassword']) ? $input['newPassword'] : '';

if (!$username || !$newPassword) {
    echo json_encode(['success' => false, 'error' => 'Missing username or password.']);
    exit;
}
if (strlen($newPassword) < 6) {
    echo json_encode(['success' => false, 'error' => 'Password too short.']);
    exit;
}

// Use the existing connection from db.php
// $conn = getConnection(); // <-- removed

// Check if user exists
$stmt = $conn->prepare('SELECT id FROM admins WHERE username = ?');
$stmt->bind_param('s', $username);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows === 0) {
    echo json_encode(['success' => false, 'error' => 'Username not found.']);
    $stmt->close();
    $conn->close();
    exit;
}
$stmt->close();

// Update password (hashed)
$hashed = password_hash($newPassword, PASSWORD_DEFAULT);
$update = $conn->prepare('UPDATE admins SET password = ? WHERE username = ?');
$update->bind_param('ss', $hashed, $username);
$success = $update->execute();
$update->close();
$conn->close();

if ($success) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to update password.']);
}
?>
