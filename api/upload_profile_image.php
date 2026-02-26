<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

require_once '../db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = isset($_POST['user_id']) ? $_POST['user_id'] : null;

    if (!$user_id || !isset($_FILES['image'])) {
        echo json_encode(["success" => false, "error" => "User ID or image missing"]);
        exit;
    }

    $file = $_FILES['image'];
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    // Allowed extensions
    $allowed_extensions = ['jpg', 'jpeg', 'png'];
    if (!in_array($file_extension, $allowed_extensions)) {
        echo json_encode(["success" => false, "error" => "Only JPG, JPEG, and PNG files are allowed"]);
        exit;
    }

    // Generate hashed filename
    $hashed_filename = md5($user_id . time() . rand()) . '.' . $file_extension;
    $upload_dir = 'uploads/profile_images/';
    $upload_path = $upload_dir . $hashed_filename;

    // Check if directory exists, create if not
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        try {
            // Check if column exists, if not, try to add it (or assume it exists)
            // SQL: ALTER TABLE users ADD COLUMN IF NOT EXISTS profile_image VARCHAR(255) NULL AFTER role;
            
            $stmt = $pdo->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
            if ($stmt->execute([$upload_path, $user_id])) {
                echo json_encode([
                    "success" => true, 
                    "message" => "Profile image uploaded successfully",
                    "path" => $upload_path
                ]);
            } else {
                echo json_encode(["success" => false, "error" => "Failed to update database"]);
            }
        } catch (PDOException $e) {
            echo json_encode(["success" => false, "error" => "Database error: " . $e->getMessage()]);
        }
    } else {
        echo json_encode(["success" => false, "error" => "Failed to move uploaded file"]);
    }
} else {
    echo json_encode(["success" => false, "error" => "Invalid request method"]);
}
?>
