<?php
include 'db.php';

// Total Customers
$result = $conn->query("SELECT COUNT(*) AS total_customers FROM customers");
$row = $result->fetch_assoc();
$total_customers = $row['total_customers'] ?? 0;

// Active Plans
$result = $conn->query("SELECT COUNT(*) AS active_plans FROM plans WHERE status='active'");
$row = $result->fetch_assoc();
$active_plans = $row['active_plans'] ?? 0;

// Expired Plans (none in plans table, so check sessions for expired)
$result = $conn->query("SELECT COUNT(*) AS expired_plans FROM sessions WHERE status='expired'");
$row = $result->fetch_assoc();
$expired_plans = $row['expired_plans'] ?? 0;

// Online Users
$result = $conn->query("SELECT COUNT(*) AS online_users FROM sessions WHERE status='active'");
$row = $result->fetch_assoc();
$online_users = $row['online_users'] ?? 0;

// Total Payments
$result = $conn->query("SELECT SUM(amount) AS total_payments FROM payments");
$row = $result->fetch_assoc();
$total_payments = $row['total_payments'] ?? 0;

// Return JSON response
header('Content-Type: application/json');
echo json_encode([
    'totalCustomers' => (int)$total_customers,
    'activePlans'    => (int)$active_plans,
    'expiredPlans'   => (int)$expired_plans,
    'activeSessions' => (int)$online_users,
    'paymentsToday'  => (float)$total_payments
]);
?>
