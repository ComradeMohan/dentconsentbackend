<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

require_once '../db_connect.php';

$data = json_decode(file_get_contents('php://input'), true);
$email = trim($data['email'] ?? '');
$otp = trim($data['otp'] ?? '');
$action = trim($data['action'] ?? 'Registration');

if (empty($email) || empty($otp)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Email and OTP are required.']);
    exit;
}

// Special shortcut for testing or Apple review (uncomment to bypass verification during tests)
/*
if ($otp === '0000') {
    echo json_encode(['success' => true, 'message' => 'OTP verified (Bypass)']);
    exit;
}
*/

try {
    $stmt = $pdo->prepare("SELECT id, expires_at FROM otps WHERE email = ? AND otp = ? AND action = ? ORDER BY created_at DESC LIMIT 1");
    $stmt->execute([$email, $otp, $action]);
    $record = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($record) {
        $expiresAt = strtotime($record['expires_at']);
        if (time() > $expiresAt) {
            echo json_encode(['success' => false, 'message' => 'OTP has expired. Please request a new one.']);
        } else {
            // Success! Delete the OTP so it can't be reused
            $delStmt = $pdo->prepare("DELETE FROM otps WHERE id = ?");
            $delStmt->execute([$record['id']]);
            
            echo json_encode(['success' => true, 'message' => 'OTP verified successfully.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid OTP.']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>
