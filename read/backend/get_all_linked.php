<?php

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}
include('db.php');

$user_id = $_SESSION['user_id']; // Get logged-in user ID

// Response array with success flag
$response = ["success" => false];

try {
    // Fetch all QR code data
    // Query to check if the user has a linked tag via savemeout_id
    $query = "SELECT 
                tl.tag_id, 
                sm.id AS tag_id, 
                sm.pin, 
                sm.unique_id, 
                sm.created_on, 
                sm.qr,
                ui.image_path
                FROM tag_link tl
                INNER JOIN savemeout sm ON tl.tag_id = sm.savemeout_id
                LEFT JOIN users u ON tl.id = u.tag_link_id  -- Join user table on taglink_id
                LEFT JOIN user_images ui ON u.user_data_id = ui.user_data_id  -- Join user_image on user_id
                WHERE tl.user_id = ?";

    // Prepare and execute the query
    $stmt = $db->prepare(query: $query);
    $stmt->execute(params: [$user_id]);

    $tags_data = [];

    // Fetch rows and add them to the array
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $tags_data[] = $row;
    }

    // Prepare the response to match DataTables expected format
    $response = [
        "success" => "success",  // Draw counter (helps with pagination and sorting)
        "recordsTotal" => count($tags_data),  // Total number of records
        "data" => $tags_data  // The actual data
    ];

    // Return the data as JSON
    echo json_encode($response);

} catch (PDOException $e) {
    // Handle the error
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}

// Close the connection (optional as PDO uses persistent connections by default)
$db = null;
