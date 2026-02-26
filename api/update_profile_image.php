<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

require_once '../db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_POST['user_id'] ?? null;
    
    if (!$userId || !isset($_FILES['image'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'User ID and image are required']);
        exit;
    }

    try {
        // 1. Fetch user to get current email (for filename) and check existence
        $stmt = $pdo->prepare("SELECT email, profile_image FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        if (!$user) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'User not found']);
            exit;
        }

        // 2. Handle Profile Image
        $file = $_FILES['image'];
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        $hashed_filename = md5($user['email'] . time() . rand()) . '.' . $file_extension;
        $upload_dir = 'uploads/profile_images/';
        
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $upload_path = $upload_dir . $hashed_filename;
        if (move_uploaded_file($file['tmp_name'], $upload_path)) {
            // Delete old image if it exists and is not the new one
            if ($user['profile_image'] && file_exists($user['profile_image'])) {
                unlink($user['profile_image']);
            }

            // 3. Update User Table
            $stmt = $pdo->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
            $stmt->execute([$upload_path, $userId]);

            echo json_encode([
                'success' => true, 
                'message' => 'Profile image updated successfully',
                'profile_image' => $upload_path
            ]);
        } else {
            throw new Exception('Failed to move uploaded file');
        }

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Update failed: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
?>
