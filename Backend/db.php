<?php
$host = "localhost";
$user = "root";   // your MySQL username
$pass = "Luc1601ky@2025";       // your MySQL password
$db   = "famget_wifi";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
