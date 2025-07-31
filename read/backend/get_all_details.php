<?php

include('db.php');

try {
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
        // 
        $stmt = $db->prepare("SELECT qr FROM savemeout WHERE id = ? AND pin = ? LIMIT 1;");
        $stmt->execute([$_GET['tag_id'], $_GET['pin']]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$data) {
            echo json_encode(value: ["success" => false, "error" => "NO QR OR DATA FOR USER", "errorCode" => $errorCode]);
            exit;
        }
        echo json_encode(value: ["success" => false, "error" => "NO DATA FOR USER", "errorCode" => $errorCode, "qr" => $data['qr']]);
    } else {
        echo json_encode(["success" => false, "error" => $errorMessage, "errorCode" => $errorCode]);
    }
}
?>