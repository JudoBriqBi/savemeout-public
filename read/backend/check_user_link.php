<?php

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}
include('db.php');

$user_id = $_SESSION['user_id']; // Get logged-in user ID

$tag_id = isset($_GET['tag_id']) ? trim($_GET['tag_id']) : '';

if (empty($tag_id)) {
    echo json_encode(["success" => "false", "message" => "Missing tag_id"]);
    exit;
}

try {
    // Fetch all QR code data
    // Query to check if the user has a linked tag via savemeout_id
    $query = "SELECT tl.user_id 
                FROM savemeout s
                JOIN tag_link tl ON s.savemeout_id = tl.tag_id
                WHERE s.id = ? AND tl.user_id = ?";

    $stmt = $db->prepare($query);
    $stmt->execute([$tag_id, $user_id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(["success" => "true", "message" => "Tag linked to user"]);
    } else {
        echo json_encode(["success" => "false", "message" => "Tag not linked to user"]);
    }
} catch (Exception $e) {
    echo json_encode(["success" => "false", "message" => $e->getMessage()]);
}

// Close the connection (optional as PDO uses persistent connections by default)
$db = null;
