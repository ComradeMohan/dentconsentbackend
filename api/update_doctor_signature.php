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
        echo json_encode(['success' => false, 'error' => 'User ID and signature image are required']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT user_id, signature_url FROM doctor_profiles WHERE user_id = ?");
        $stmt->execute([$userId]);
        $doctor = $stmt->fetch();

        if (!$doctor) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Doctor profile not found']);
            exit;
        }

        $file = $_FILES['image'];
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        $hashed_filename = 'doc_sig_' . md5($userId . time() . rand()) . '.' . $file_extension;
        $upload_dir = 'uploads/signatures/';
        
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $upload_path = $upload_dir . $hashed_filename;
        if (move_uploaded_file($file['tmp_name'], $upload_path)) {
            if ($doctor['signature_url'] && file_exists($doctor['signature_url'])) {
                unlink($doctor['signature_url']);
            }

            $stmt = $pdo->prepare("UPDATE doctor_profiles SET signature_url = ? WHERE user_id = ?");
            $stmt->execute([$upload_path, $userId]);

            echo json_encode([
                'success' => true, 
                'message' => 'Signature uploaded successfully',
                'signature_url' => $upload_path
            ]);
        } else {
            throw new Exception('Failed to move uploaded file');
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Signature upload failed: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
?>
