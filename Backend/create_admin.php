<?php
include 'db.php'; // ✅ your database connection

// === Admin account details ===
$username = "Admin001";
$password = "!123Try."; // plain password you want to use

// Hash the password securely
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Insert admin
$sql = "INSERT INTO admins (username, password) VALUES (?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $username, $hashedPassword);

if ($stmt->execute()) {
    echo "✅ Admin account created successfully!<br>";
    echo "Username: " . $username . "<br>";
    echo "Password: " . $password . "<br>";
    echo "Hashed Password Stored in DB: " . $hashedPassword;
} else {
    echo "❌ Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
