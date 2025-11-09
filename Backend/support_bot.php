<?php
// Database credentials
$host = 'localhost';
$user = 'root';
$pass = 'Luc1601ky@2025';
$dbname = 'famget_wifi';

// Connect to MySQL
$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get incoming message from form (simulate SMS/WhatsApp)
$from = $_POST['phone'] ?? 'TEST_NUMBER';
$body = trim($_POST['message'] ?? 'Hello Famget, I need help');

// --- Function to generate ticket number with monthly reset ---
function generate_ticket_number($conn) {
    $month_year = date('mY'); // MMYYYY

    $stmt = $conn->prepare("SELECT last_counter FROM ticket_counters WHERE month_year=?");
    $stmt->bind_param("s", $month_year);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($last_counter);
    $stmt->fetch();

    if ($stmt->num_rows > 0) {
        $counter = $last_counter + 1;
        $update = $conn->prepare("UPDATE ticket_counters SET last_counter=? WHERE month_year=?");
        $update->bind_param("is", $counter, $month_year);
        $update->execute();
    } else {
        $counter = 1;
        $insert = $conn->prepare("INSERT INTO ticket_counters (month_year, last_counter) VALUES (?, ?)");
        $insert->bind_param("si", $month_year, $counter);
        $insert->execute();
    }

    return '#' . $month_year . str_pad($counter, 4, '0', STR_PAD_LEFT);
}

// --- Function to classify issue ---
function classify_issue($message) {
    $message = strtolower($message);

    if (preg_match('/bill|payment|charge/', $message)) {
        return 'billing';
    } elseif (preg_match('/disconnect|slow|wifi|connection/', $message)) {
        return 'connection';
    } elseif (preg_match('/setup|installation|router|hardware/', $message)) {
        return 'technical';
    } else {
        return 'general';
    }
}

// --- Check for existing open ticket ---
$stmt = $conn->prepare("SELECT ticket_number, customer_msg FROM tickets WHERE customer_number=? AND status='pending'");
$stmt->bind_param("s", $from);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($ticket_number, $customer_msg);
$stmt->fetch();

$response = '';

if ($stmt->num_rows > 0) {
    if (empty($customer_msg)) {
        // Customer provides issue
        $category = classify_issue($body);
        $update = $conn->prepare("UPDATE tickets SET customer_msg=?, category=? WHERE ticket_number=?");
        $update->bind_param("sss", $body, $category, $ticket_number);
        $update->execute();

        $response = "Thank you for contacting Famget. Your ticket number is $ticket_number. Our customer support team will respond shortly.";
    } else {
        $response = "Your ticket number is $ticket_number. Our support team is already working on your request.";
    }
} else {
    // First message: generate ticket immediately
    $ticket_number = generate_ticket_number($conn);
    $insert = $conn->prepare("INSERT INTO tickets (ticket_number, customer_number, customer_msg, status) VALUES (?, ?, ?, 'pending')");
    $empty_msg = '';
    $insert->bind_param("sss", $ticket_number, $from, $empty_msg);
    $insert->execute();

    // Immediate response with ticket number
    $response = "Hello! Your ticket number is $ticket_number. Which kind of help do you need?";
}

// Output response for local testing
echo "<h3>Bot Response:</h3>";
echo "<p>$response</p>";

$conn->close();
?>
