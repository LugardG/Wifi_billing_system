<?php
// mpesa_callback_simulate.php
// Simulate a successful MPESA STK callback and create subscription + access code
date_default_timezone_set('Africa/Nairobi');

header('Content-Type: application/json');

// include DB connection (expects $conn from db.php)
require_once 'db.php';

// Read input: accept GET or POST JSON
$input = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw = file_get_contents('php://input');
    $input = json_decode($raw, true) ?: $_POST;
} else {
    // GET query parameters
    $input = $_GET;
}

$phone = isset($input['phone']) ? trim($input['phone']) : null;
$plan  = isset($input['plan']) ? trim($input['plan']) : null;
$amount = isset($input['amount']) ? floatval($input['amount']) : null;

// basic validation
if (!$phone || !$plan) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Please provide phone and plan (e.g. ?phone=2547xxxxxxx&plan=1 Hour Plan).']);
    exit;
}

// Plan -> hours mapping (same logic as your callback)
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
    default: $durationHours = 1; // default fallback
}

$expiry = $durationHours ? date('Y-m-d H:i:s', strtotime("+$durationHours hours")) : null;

// Create a fake checkoutRequestID / MerchantRequestID
$checkoutRequestID = 'SIM_' . time() . '_' . substr(preg_replace('/\D/', '', $phone), -6);
$merchantRequestID = 'SIMMR_' . uniqid();

// If amount not provided, set from plan default amounts (use your plan amounts)
$planAmounts = [
    '1 Hour Plan' => 5,
    '3 Hour Plan' => 9,
    '8 Hour Plan' => 15,
    'Daily 1 Device' => 35,
    'Daily 2 Devices' => 50,
    'Weekly 1 Device' => 160,
    'Weekly 2 Devices' => 200,
    'Monthly 1 Device' => 500,
    'Monthly 2 Devices' => 700
];
if (!$amount) $amount = isset($planAmounts[$plan]) ? $planAmounts[$plan] : 5;

// Generate an access code (8 alphanumeric)
function generateAccessCode($len = 8) {
    $chars = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789'; // avoid ambiguous chars
    $code = '';
    for ($i=0; $i<$len; $i++) $code .= $chars[random_int(0, strlen($chars)-1)];
    return $code;
}
$accessCode = generateAccessCode(8);

// Insert into subscriptions table
$insertSubSql = "INSERT INTO subscriptions (checkoutRequestID, phone, plan, amount, resultCode, resultDesc, receipt, status, expiry)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($insertSubSql);
$resultCode = 0;
$resultDesc = 'Simulated success';
$receipt = 'SIMRECEIPT' . strtoupper(substr(sha1($checkoutRequestID), 0, 8));
$status = 'active';
$stmt->bind_param('sssisssss', $checkoutRequestID, $phone, $plan, $amount, $resultCode, $resultDesc, $receipt, $status, $expiry);
$ok1 = $stmt->execute();
$subId = $stmt->insert_id;
$stmt->close();

// Insert or replace into wifi_access with access code
$insertAccessSql = "REPLACE INTO wifi_access (phone, plan, expiry, access_code, subscription_id) VALUES (?, ?, ?, ?, ?)";
$stmt2 = $conn->prepare($insertAccessSql);
$stmt2->bind_param('ssssi', $phone, $plan, $expiry, $accessCode, $subId);
$ok2 = $stmt2->execute();
$stmt2->close();

$conn->close();

// Log a small local file for debugging
$log = [
    'timestamp' => date('c'),
    'simulator' => true,
    'phone' => $phone,
    'plan' => $plan,
    'amount' => $amount,
    'expiry' => $expiry,
    'access_code' => $accessCode,
    'checkoutRequestID' => $checkoutRequestID,
    'merchantRequestID' => $merchantRequestID,
    'db_sub_inserted' => (bool)$ok1,
    'db_access_inserted' => (bool)$ok2
];
file_put_contents(__DIR__ . '/simulate_callback_log.json', json_encode($log) . PHP_EOL, FILE_APPEND);

// Return success + access code (this simulates the "customer receives access code")
echo json_encode([
    'success' => true,
    'message' => 'Simulated callback processed.',
    'access_code' => $accessCode,
    'expiry' => $expiry,
    'subscription_id' => $subId,
    'checkoutRequestID' => $checkoutRequestID
]);
