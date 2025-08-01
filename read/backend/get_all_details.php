<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include('db.php');

try {
    if (empty($_GET['tag_id']) || empty($_GET['pin'])) {
        echo json_encode(["success" => false, "error" => "Missing tag_id or pin"]);
        exit;
    }

    $stmt = $db->prepare("CALL GetUserData(?,?);");
    $stmt->execute([$_GET['tag_id'], $_GET['pin']]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$data) {
        echo json_encode(["success" => false, "error" => "Data not found"]);
        exit;
    }
    echo json_encode(["success" => true, "data" => $data]);

} catch (PDOException $e) {
    $errorMessage = $e->getMessage();
    $errorCode = "";
    if (strpos($errorMessage, 'EMPTY') !== false) {
        $errorCode = "NULLDATA"; // Error: Tag ID & PIN mismatch
        $stmt = $db->prepare("SELECT qr FROM savemeout WHERE id = ? AND pin = ? LIMIT 1;");
        $stmt->execute([$_GET['tag_id'], $_GET['pin']]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$data) {
            echo json_encode(["success" => false, "error" => "NO QR OR DATA FOR USER", "errorCode" => $errorCode]);
            exit;
        }
        echo json_encode(["success" => false, "error" => "NO DATA FOR USER", "errorCode" => $errorCode, "qr" => $data['qr']]);
    } else {
        echo json_encode(["success" => false, "error" => $errorMessage, "errorCode" => $errorCode]);
    }
}
?>