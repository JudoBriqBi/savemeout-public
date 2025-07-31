<?php
session_start();
include('db.php'); // Include database connection
session_unset(); // Unset all session variables

header('Content-Type: application/json');

// Check if email and password are provided
if (!isset($_POST['email']) || !isset($_POST['password'])) {
    echo json_encode(["success" => false, "error" => "Missing email or password."]);
    exit;
}

$email = trim($_POST['email']);
$pass = trim($_POST['password']); // Already MD5 hashed from frontend

try {
    $stmt = $db->prepare("SELECT user_id FROM auth WHERE user_email = ? AND user_pass = ? LIMIT 1");
    $stmt->execute([$email, $pass]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) { 
        $_SESSION['user_id'] = $user['user_id'];
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => "Invalid email or password."]);
    }
} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => "Database error: " . $e->getMessage()]);
}
