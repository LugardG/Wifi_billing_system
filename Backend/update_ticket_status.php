<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ticket_number = $_POST['ticket_number'];
    $status = $_POST['status'];

    $conn = new mysqli('localhost', 'root', '', 'famget');
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $stmt = $conn->prepare("UPDATE tickets SET status=? WHERE ticket_number=?");
    $stmt->bind_param("ss", $status, $ticket_number);
    $stmt->execute();
    $stmt->close();
    $conn->close();

    // Redirect back to admin page
    header("Location: admin.php#ticketsSection");
    exit();
}
?>
