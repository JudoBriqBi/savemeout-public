<?php
session_start();
include('db.php'); // Include database connection
session_unset(); // Unset all session variables

header('Content-Type: application/json');

// Check if email and password are provided
if (!isset($_POST['tagId']) || !isset($_POST['tagPin'])) {
    echo json_encode(["success" => false, "error" => "Missing values."]);
    exit;
}

$tagId = trim($_POST['tagId']);
$tagPin = trim($_POST['tagPin']);

try {
    $stmt = $db->prepare("SELECT savemeout_id FROM savemeout WHERE id = ? AND pin = ?");
    $stmt->execute([$tagId, $tagPin]);

    if ($stmt->fetch()) {
        echo json_encode(["success" => true]);
    } else {

        echo json_encode(["success" => false]);
    }



} catch (PDOException $e) {
    echo json_encode(["success" => false, "errorCode" => "EXEERROR", "error" => "Database error: " . $e->getMessage()]);
}
