<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}
include('db.php');

// Check if user_id exists in the session
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        "success" => false,
        "error" => "User not authenticated",
        "errorCode" => "UNLI087"
    ]);
    exit;
}

$user_id = intval($_SESSION['user_id']);
// $user_id = 1;
$tagid = isset($_GET['tagid']) ? trim($_GET['tagid']) : '';
$tagpin = isset($_GET['tagpin']) ? trim($_GET['tagpin']) : '';
if (empty($tagid) || empty($tagpin)) {
    echo json_encode([
        "success" => false,
        "error" => "Invalid parameters",
        "errorCode" => "COMIN001"
    ]);
    exit;
}

try {
    // Call the stored procedure
    $stmt = $db->prepare("CALL LinkTagToUser(?, ?, ?)");
    $stmt->execute([$user_id, $tagid, $tagpin]);

    // Check if any row was affected
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            "success" => true,
            "message" => "Tag linked successfully",
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "error" => "No changes made or invalid credentials",
            "errorCode" => "COMIN001"
        ]);
    }
} catch (PDOException $e) {
    $errorMessage = $e->getMessage();
    $errorCode = "COMIN001"; // General SQL error

       // Custom error messages based on SQLSTATE
    if (strpos($errorMessage, 'COMI') !== false) {
        $errorCode = "COMI001"; // Error: Tag ID & PIN mismatch
    } elseif (strpos($errorMessage, 'ALTAP001') !== false) {
        $errorCode = "ALTAP001"; // Error: Already linked
    } else {
        $errorCode = "SQL_ERROR"; // General SQL error
    }

    echo json_encode([
        "success" => false,
        "errorCode" => $errorCode
    ]);
}
?>