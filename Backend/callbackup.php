<?php
// callback.php
date_default_timezone_set('Africa/Nairobi');
include 'db.php';

// Get the raw response
$data = file_get_contents('php://input');
file_put_contents('callback_response.json', $data); // For logging

$mpesaResponse = json_decode($data, true);

if (isset($mpesaResponse['Body']['stkCallback'])) {
    $callback = $mpesaResponse['Body']['stkCallback'];
    $resultCode = $callback['ResultCode'];
    $resultDesc = $callback['ResultDesc'];
    $checkoutRequestID = $callback['CheckoutRequestID'];
    $merchantRequestID = $callback['MerchantRequestID'];

    if ($resultCode == 0) {
        // Successful transaction
        $metadata = $callback['CallbackMetadata']['Item'];
        $amount = $metadata[0]['Value'] ?? 0;
        $mpesaReceipt = $metadata[1]['Value'] ?? '';
        $transactionDate = $metadata[3]['Value'] ?? '';
        $phone = $metadata[4]['Value'] ?? '';

        $sql = "INSERT INTO payments 
            (MerchantRequestID, CheckoutRequestID, ResultCode, ResultDesc, Amount, MpesaReceipt, TransactionDate, Phone)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssisdsss", $merchantRequestID, $checkoutRequestID, $resultCode, $resultDesc, $amount, $mpesaReceipt, $transactionDate, $phone);
        $stmt->execute();
    } else {
        // Failed transaction
        $sql = "INSERT INTO payments (MerchantRequestID, CheckoutRequestID, ResultCode, ResultDesc)
                VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssis", $merchantRequestID, $checkoutRequestID, $resultCode, $resultDesc);
        $stmt->execute();
    }
}

http_response_code(200);
?>
