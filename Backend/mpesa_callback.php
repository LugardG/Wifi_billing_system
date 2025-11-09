<?php
header('Content-Type: application/json');
require_once 'db.php';

// Log raw callback for debugging
$raw = file_get_contents('php://input');
file_put_contents(__DIR__ . '/callback_raw.json', $raw . "\n", FILE_APPEND);

$input = json_decode($raw, true);

if (!$input) {
    echo json_encode(['success' => false, 'error' => 'No data received']);
    exit;
}

$callback = $input['Body']['stkCallback'] ?? null;

if (!$callback) {
    echo json_encode(['success' => false, 'error' => 'Invalid callback data']);
    exit;
}

$checkoutRequestID = $callback['CheckoutRequestID'] ?? '';
$resultCode = $callback['ResultCode'] ?? '';
$resultDesc = $callback['ResultDesc'] ?? '';
$amount = 0;
$phone = '';
$plan = '';
$receipt = '';

if ((int)$resultCode === 0 && isset($callback['CallbackMetadata']['Item'])) {

    foreach ($callback['CallbackMetadata']['Item'] as $item) {
        switch ($item['Name']) {
            case 'Amount':
                $amount = $item['Value'];
                break;
            case 'MpesaReceiptNumber':
                $receipt = $item['Value'];
                break;
            case 'PhoneNumber':
                $phone = $item['Value'];
                break;
            case 'AccountReference':
                $plan = $item['Value'];
                break;
        }
    }
}

// Calculate expiry based on plan
$durationHours = 0;
switch ($plan) {
    case '1 Hour Plan': $durationHours = 1; break;
    case '3 Hour Plan': $durationHours = 3; break;
    case '8 Hour Plan': $durationHours = 8; break;
    case 'Daily 1 Device':
    case 'Daily 2 Devices': $durationHours = 24; break;
    case 'Weekly 1 Device':
    case 'Weekly 2 Devices': $durationHours = 24*7; break;
    case 'Monthly 1 Device':
    case 'Monthly 2 Devices': $durationHours = 24*30; break;
}

$expiry = $durationHours ? date('Y-m-d H:i:s', strtotime("+$durationHours hours")) : null;

// Store transaction
$stmt = $conn->prepare("INSERT INTO subscriptions (checkoutRequestID, phone, plan, amount, resultCode, resultDesc, receipt, status, expiry)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
$status = ($resultCode === 0) ? 'active' : 'failed';
$stmt->bind_param('sssisssss', $checkoutRequestID, $phone, $plan, $amount, $resultCode, $resultDesc, $receipt, $status, $expiry);
$success = $stmt->execute();
$stmt->close();

// Optionally insert into wifi_access and generate username/access code
if ($success && $status === 'active') {
    // 1. Check if this phone already has a username
    $username = null;
    $stmtCheck = $conn->prepare("SELECT username FROM wifi_access WHERE phone = ? LIMIT 1");
    if ($stmtCheck) {
        $stmtCheck->bind_param('s', $phone);
        $stmtCheck->execute();
        $res = $stmtCheck->get_result();
        if ($res && $res->num_rows > 0) {
            $row = $res->fetch_assoc();
            $username = $row['username'];
        }
        $stmtCheck->close();
    }
    // 2. If not, generate next username (D1, D2, ...)
    if (!$username) {
        $result = $conn->query("SELECT MAX(CAST(SUBSTRING(username,2) AS UNSIGNED)) AS maxnum FROM wifi_access WHERE username LIKE 'D%'");
        $nextNum = 1;
        if ($result && ($row = $result->fetch_assoc()) && $row['maxnum']) {
            $nextNum = $row['maxnum'] + 1;
        }
        $username = 'D' . $nextNum;
    }
    // 3. Generate a new 4-digit access code for each payment
    $access_code = str_pad(strval(random_int(0, 9999)), 4, '0', STR_PAD_LEFT);

    // 4. Insert or replace into wifi_access with username and access_code
    $stmt2 = $conn->prepare("REPLACE INTO wifi_access (phone, plan, expiry, access_code, username) VALUES (?, ?, ?, ?, ?)");
    if ($stmt2) {
        $stmt2->bind_param('sssss', $phone, $plan, $expiry, $access_code, $username);
        $stmt2->execute();
        if ($stmt2->errno) {
            file_put_contents(__DIR__ . '/mpesa_db_errors.log', date('c') . " - wifi_access insert error: " . $stmt2->error . "\n", FILE_APPEND);
        }
        $stmt2->close();
        // Log for operator
        $logLine = date('Y-m-d H:i:s') . " | phone:" . $phone . " | plan:" . $plan . " | username:" . $username . " | code:" . $access_code . " | expiry:" . $expiry . "\n";
        file_put_contents(__DIR__ . '/access_codes.log', $logLine, FILE_APPEND);
    } else {
        file_put_contents(__DIR__ . '/mpesa_db_errors.log', date('c') . " - prepare failed for wifi_access: " . $conn->error . "\n", FILE_APPEND);
    }
}

$conn->close();
echo json_encode(['success' => $success]);
?>
