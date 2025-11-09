<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include 'db.php';

header('Content-Type: application/json'); // ✅ force JSON response

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $sql = "SELECT * FROM admins WHERE username = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(["success" => false, "message" => "SQL prepare failed: " . $conn->error]);
        exit();
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $row = $result->fetch_assoc();

        if (password_verify($password, $row['password'])) {
            $_SESSION['username'] = $row['username'];

            echo json_encode([
                "success" => true,
                "redirect" => "admin.php"
            ]);
            exit();
        } else {
            echo json_encode(["success" => false, "message" => "❌ Incorrect password"]);
            exit();
        }
    } else {
        echo json_encode(["success" => false, "message" => "❌ Username not found"]);
        exit();
    }
}

echo json_encode(["success" => false, "message" => "Invalid request"]);
