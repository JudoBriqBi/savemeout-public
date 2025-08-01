<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}
include('db.php');

$user_id = $_SESSION['user_id']; // Get logged-in user ID

$response = ["success" => false];

function sanitizeInput($value)
{
    return isset($value) && $value !== "" ? $value : null;
}

try {
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        // Begin transaction
        $db->beginTransaction();
        $user_image = NULL;
        $medical_doc = NULL;

        $tag_id = sanitizeInput($_POST["tag_id"] ?? null);
        $first_name = sanitizeInput($_POST["first_name"] ?? null);
        $last_name = sanitizeInput($_POST["last_name"] ?? null);
        $gender = sanitizeInput($_POST["gender"] ?? null);
        $birth_date = sanitizeInput($_POST["birth_date"] ?? null);
        $email = sanitizeInput($_POST["email"] ?? null);
        $mobile = sanitizeInput($_POST["mobile"] ?? null);
        $country = sanitizeInput($_POST["country"] ?? null);
        $state = sanitizeInput($_POST["state"] ?? null);
        $city = sanitizeInput($_POST["city"] ?? null);
        $pincode = sanitizeInput($_POST["pincode"] ?? null);
        $address = sanitizeInput($_POST["address"] ?? null);
        $blood_group = sanitizeInput($_POST["blood_group"] ?? null);
        $height = sanitizeInput($_POST["height"] ?? null);
        $weight = sanitizeInput($_POST["weight"] ?? null);
        $any_disease = sanitizeInput($_POST["any_disease"] ?? null);
        $disease = sanitizeInput($_POST["disease"] ?? null);
        $any_allergies = sanitizeInput($_POST["any_allergies"] ?? null);
        $allergies = sanitizeInput($_POST["allergies"] ?? null);
        $prescription = sanitizeInput($_POST["prescription"] ?? null);
        $important_notes = sanitizeInput($_POST["important_notes"] ?? null);
        $emergency_first_name = sanitizeInput($_POST["emergency_first_name"] ?? null);
        $emergency_last_name = sanitizeInput($_POST["emergency_last_name"] ?? null);
        $emergency_mobile = sanitizeInput($_POST["emergency_mobile"] ?? null);
        $emergency_email = sanitizeInput($_POST["emergency_email"] ?? null);
        $relation = sanitizeInput($_POST["relation"] ?? null);
        $doc_name = sanitizeInput($_POST["doc_name"] ?? null);
        $doc_phone = sanitizeInput($_POST["doc_phone"] ?? null);


        // Fetch the old image path if it exists
        $fetchQuery = "SELECT ui.image_path 
                        FROM user_images ui
                        JOIN users ut ON ui.user_data_id = ut.user_data_id
                        JOIN tag_link tl ON ut.tag_link_id = tl.id
                        JOIN savemeout sm ON tl.tag_id = sm.savemeout_id
                        WHERE sm.id = :tag_id AND tl.user_id = :user_id;";
        $fetchStmt = $db->prepare($fetchQuery);
        $fetchStmt->bindParam(":tag_id", $_POST["tag_id"]);
        $fetchStmt->bindParam(":user_id", $user_id);
        $fetchStmt->execute();
        $oldImage = $fetchStmt->fetchColumn(); // Get old image path

        // Fetch the old image path if it exists
        $fetchQuery = "SELECT ui.doc_path 
                FROM medical_info ui
                JOIN users ut ON ui.user_data_id = ut.user_data_id
                JOIN tag_link tl ON ut.tag_link_id = tl.id
                JOIN savemeout sm ON tl.tag_id = sm.savemeout_id
                WHERE sm.id = :tag_id AND tl.user_id = :user_id;";
        $fetchStmt = $db->prepare($fetchQuery);
        $fetchStmt->bindParam(":tag_id", $_POST["tag_id"]);
        $fetchStmt->bindParam(":user_id", $user_id);
        $fetchStmt->execute();
        $oldDoc = $fetchStmt->fetchColumn(); // Get old image path

        // Insert user first (without image path)
        $query = "CALL InsertOrReplaceUserData(:tag_id, :user_id, :first_name, :last_name, :gender, :birth_date, :email, :mobile, :country, :state, :city, :pincode, :address,
                                      :blood_group, :height, :weight, :any_disease, :disease, :any_allergies, :allergies, :prescription, :important_notes,
                                      :emergency_first_name, :emergency_last_name, :emergency_mobile, :emergency_email, :relation, :doc_name, :doc_phone, :medical_doc, :user_image)";

        $stmt = $db->prepare($query);

        $stmt->bindParam(":tag_id", $tag_id);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":first_name", $first_name);
        $stmt->bindParam(":last_name", $last_name);
        $stmt->bindParam(":gender", $gender);
        $stmt->bindParam(":birth_date", $birth_date);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":mobile", $mobile);
        $stmt->bindParam(":country", $country);
        $stmt->bindParam(":state", $state);
        $stmt->bindParam(":city", $city);
        $stmt->bindParam(":pincode", $pincode);
        $stmt->bindParam(":address", $address);
        $stmt->bindParam(":blood_group", $blood_group);
        $stmt->bindParam(":height", $height);
        $stmt->bindParam(":weight", $weight);
        $stmt->bindParam(":any_disease", $any_disease);
        $stmt->bindParam(":disease", $disease);
        $stmt->bindParam(":any_allergies", $any_allergies);
        $stmt->bindParam(":allergies", $allergies);
        $stmt->bindParam(":prescription", $prescription);
        $stmt->bindParam(":important_notes", $important_notes);
        $stmt->bindParam(":emergency_first_name", $emergency_first_name);
        $stmt->bindParam(":emergency_last_name", $emergency_last_name);
        $stmt->bindParam(":emergency_mobile", $emergency_mobile);
        $stmt->bindParam(":emergency_email", $emergency_email);
        $stmt->bindParam(":relation", $relation);
        $stmt->bindParam(":doc_name", $doc_name);
        $stmt->bindParam(":doc_phone", $doc_phone);
        $stmt->bindParam(":medical_doc", $medical_doc);
        $stmt->bindParam(":user_image", $user_image);


        $stmt->execute();
        // var_dump($stmt->debugDumpParams());

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        if (!$result || !isset($result["new_data_id"]) || !isset($result["tag_id"])) {
            throw new Exception("User insertion failed");
        }

        $newUserId = $result["new_data_id"];
        $oldUserId = $result["old_data_id"];

        $docPath = null;
        // medical_info
        $uploadDir = "docs/";
        if (isset($_FILES["medical_doc"]) && $_FILES["medical_doc"]["error"] == 0) {
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true); // Ensure directory exists
            }
            // Delete old doc if it exists
            if ($oldDoc && file_exists(__DIR__ . "/" . $oldDoc)) {
                unlink(__DIR__ . "/" . $oldDoc);
            }
            // Generate new file name
            $fileExtension = pathinfo($_FILES["medical_doc"]["name"], PATHINFO_EXTENSION);
            $fileName = "user_" . $newUserId . "." . $fileExtension; // Naming format: user_<user_id>.jpg
            $docPath = $uploadDir . $fileName;
            $fullPath = __DIR__ . "/" . $docPath;

            if (!move_uploaded_file($_FILES["medical_doc"]["tmp_name"], $docPath)) {
                throw new Exception("Failed to upload document");
            }
        } else {
            // No new file uploaded, rename the old image if it exists
            if ($oldDoc && file_exists(__DIR__ . "/" . $oldDoc)) {
                $oldExtension = pathinfo($oldDoc, PATHINFO_EXTENSION);
                $newFileName = "user_" . $newUserId . "." . $oldExtension;
                $newDocPath = $uploadDir . $newFileName;
                $newFullPath = __DIR__ . "/" . $newDocPath;

                if (!rename(__DIR__ . "/" . $oldDoc, $newFullPath)) {
                    throw new Exception("Failed to rename old image");
                }

                $docPath = $newDocPath; // Set the new image path for DB update
            }
        }
        // Update user image path in the database
        $updateQuery = "UPDATE medical_info SET doc_path = :doc_path WHERE user_data_id = :user_data_id";
        $updateStmt = $db->prepare($updateQuery);
        $updateStmt->bindParam(":doc_path", $docPath);
        $updateStmt->bindParam(":user_data_id", $newUserId);
        $updateStmt->execute();


        $imagePath = null;
        // Handle file upload
        $uploadDir = "images/";
        if (isset($_FILES["user_image"]) && $_FILES["user_image"]["error"] == 0) {
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true); // Ensure directory exists
            }
            // Delete old image if it exists
            if ($oldImage && file_exists(__DIR__ . "/" . $oldImage)) {
                unlink(__DIR__ . "/" . $oldImage);
            }

            // Generate new file name
            $fileExtension = pathinfo($_FILES["user_image"]["name"], PATHINFO_EXTENSION);
            $fileName = "user_" . $newUserId . "." . $fileExtension; // Naming format: user_<user_id>.jpg
            $imagePath = $uploadDir . $fileName;
            $fullPath = __DIR__ . "/" . $imagePath;

            if (!move_uploaded_file($_FILES["user_image"]["tmp_name"], $imagePath)) {
                throw new Exception("Failed to upload image");
            }

        } else {
            // No new file uploaded, rename the old image if it exists
            if ($oldImage && file_exists(__DIR__ . "/" . $oldImage)) {
                $oldExtension = pathinfo($oldImage, PATHINFO_EXTENSION);
                $newFileName = "user_" . $newUserId . "." . $oldExtension;
                $newImagePath = $uploadDir . $newFileName;
                $newFullPath = __DIR__ . "/" . $newImagePath;

                if (!rename(__DIR__ . "/" . $oldImage, $newFullPath)) {
                    throw new Exception("Failed to rename old image");
                }

                $imagePath = $newImagePath; // Set the new image path for DB update
            }
        }

        // Update user image path in the database
        $updateQuery = "UPDATE user_images SET image_path = :image_path WHERE user_data_id = :user_data_id";
        $updateStmt = $db->prepare($updateQuery);
        $updateStmt->bindParam(":image_path", $imagePath);
        $updateStmt->bindParam(":user_data_id", $newUserId);
        $updateStmt->execute();
        // Commit transaction
        $db->commit();

        $response = ["success" => true, "message" => "Data inserted successfully", "data" => $oldImage];
    } else {
        throw new Exception("Invalid request method");
    }
} catch (Exception $e) {
    // Rollback transaction on error
    $db->rollBack();
    $response = ["success" => false, "error" => $e->getMessage()];
}

// Return JSON response
echo json_encode($response);
?>