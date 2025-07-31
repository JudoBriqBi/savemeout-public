<?php
session_start();
include('db.php'); // Include database connection
session_unset(); // Unset all session variables

header('Content-Type: application/json');

// Check if email and password are provided
if (!isset($_POST['email']) || !isset($_POST['pass']) || !isset($_POST['firstName']) || !isset($_POST['lastName'])) {
    echo json_encode(["success" => false, "error" => "Missing values."]);
    exit;
}

$email = trim($_POST['email']);
$pass = trim($_POST['pass']); // Already MD5 hashed from frontend
$firstName = trim($_POST['firstName']);
$lastName = trim($_POST['lastName']);

try {
    $stmt = $db->prepare("SELECT user_id FROM auth WHERE user_email = ?");
    $stmt->execute([$email]);

    if ($stmt->fetch()) {
        echo json_encode(["success" => false, "errorCode" => "UAE001"]);
    } else {
        // Prepare and execute the SQL query to insert user data
        $stmt = $db->prepare("INSERT INTO auth (first_name, last_name, user_email, user_pass) VALUES (?, ?, ?, ?)");
        $result = $stmt->execute([$firstName, $lastName, $email, $pass]); // Assuming $pass is hashed already

        if ($result) {

            // Fetch the last inserted user_id
            $userId = $db->lastInsertId();
            // Create session and store user_id
            $_SESSION['user_id'] = $userId;

            echo json_encode(["success" => true, "message" => "Registration successful."]);
        } else {
            echo json_encode(["success" => false, "errorCode" => "COMMON", "error" => "Failed to register user. Please try again later."]);
        }
    }


} catch (PDOException $e) {
    echo json_encode(["success" => false, "errorCode" => "EXEERROR", "error" => "Database error: " . $e->getMessage()]);
}
