<?php
// stk_push.php
date_default_timezone_set('Africa/Nairobi');

// ==== 1. ENTER YOUR OWN CREDENTIALS FROM DARAJA ====
$consumerKey = 'fjGNAgSoxEyB0ApGj4CxDvq8BxpvxrWhpmHFGtpTpDM3cpxA';
$consumerSecret = 'NfUhQGrux8URp2nFe6PrLEI5rTMKF9MJAN2NNautC1nMVhMUx4lAtlfGPjJiGUfc';
$BusinessShortCode = '174379'; // Test Paybill for Sandbox
$Passkey = 'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919';

// ==== 2. CUSTOMER DETAILS ====
$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !isset($input['phone']) || !isset($input['plan'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing phone or plan']);
    exit;
}

// Get plan amount
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

$plan = $input['plan'];
if (!isset($planAmounts[$plan])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid plan']);
    exit;
}

$PartyA = $input['phone']; // Customer phone number in format 254XXXXXXXXX
$AccountReference = $plan;
$TransactionDesc = 'WiFi ' . $plan;
$Amount = (string)$planAmounts[$plan];

// ==== 3. AUTHENTICATION ====
header('Content-Type: application/json');
$headers = ['Content-Type:application/json; charset=utf8'];
$access_token_url = 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';

// Request access token
$curl = curl_init($access_token_url);
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($curl, CURLOPT_HEADER, FALSE);
curl_setopt($curl, CURLOPT_USERPWD, $consumerKey . ':' . $consumerSecret);
$result = curl_exec($curl);
$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
if ($result === false) {
  $err = curl_error($curl);
  curl_close($curl);
  echo json_encode(['ResponseCode' => '1', 'ResponseDescription' => 'Failed to get access token', 'error' => $err]);
  exit;
}

$resultDecoded = json_decode($result, true);
curl_close($curl);
if (!$resultDecoded || !isset($resultDecoded['access_token'])) {
  echo json_encode(['ResponseCode' => '1', 'ResponseDescription' => 'Invalid token response', 'raw' => $result]);
  exit;
}
$access_token = $resultDecoded['access_token'];

// ==== 4. INITIATE STK PUSH ====
$stkheader = ['Content-Type:application/json','Authorization:Bearer '.$access_token];

$Timestamp = date('YmdHis');
$password = base64_encode($BusinessShortCode . $Passkey . $Timestamp);

$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest');
curl_setopt($curl, CURLOPT_HTTPHEADER, $stkheader);

$curl_post_data = [
  'BusinessShortCode' => $BusinessShortCode,
  'Password' => $password,
  'Timestamp' => $Timestamp,
  'TransactionType' => 'CustomerPayBillOnline',
  'Amount' => $Amount,
  'PartyA' => $PartyA,
  'PartyB' => $BusinessShortCode,
  'PhoneNumber' => $PartyA,
  'CallBackURL' => "https://shiny-humpiest-nikole.ngrok-free.dev/Wifi_management/mpesa_callback.php",
  'AccountReference' => $AccountReference,
  'TransactionDesc' => $TransactionDesc
];

$data_string = json_encode($curl_post_data);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
// Execute STK request
$curl_response = curl_exec($curl);

// Log response and any curl errors for debugging
if ($curl_response === false) {
  $err = 'CURL ERROR: ' . curl_error($curl) . "\n";
  file_put_contents(__DIR__ . '/stk_error.log', date('c') . ' ' . $err, FILE_APPEND);
  curl_close($curl);
  echo json_encode(['ResponseCode' => '1', 'ResponseDescription' => 'CURL error', 'error' => $err]);
  exit;
}

// Save raw response from Safaricom to file for inspection
file_put_contents(__DIR__ . '/stk_response.json', $curl_response);
curl_close($curl);

// Ensure we return valid JSON to frontend
$decoded = json_decode($curl_response, true);
if ($decoded === null) {
    // response wasn't valid JSON; return an error with raw for debugging
    echo json_encode(['ResponseCode' => '1', 'ResponseDescription' => 'Invalid response from STK API', 'raw' => $curl_response]);
} else {
    echo json_encode($decoded);
}
?>
