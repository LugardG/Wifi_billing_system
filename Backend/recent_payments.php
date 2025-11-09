<?php
include 'db.php';

// Fetch the 10 most recent payments
$result = $conn->query("SELECT customer_name, plan_name, amount, payment_date 
                        FROM payments 
                        ORDER BY payment_date DESC 
                        LIMIT 10");

$payments = [];
while($row = $result->fetch_assoc()) {
    $payments[] = $row;
}

// Return JSON
echo json_encode($payments);
?>
