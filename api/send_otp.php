<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

require_once '../db_connect.php';
require_once 'email_service.php';

$data = json_decode(file_get_contents('php://input'), true);
$email = trim($data['email'] ?? '');
$action = trim($data['action'] ?? 'Registration');

if (empty($email)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Email is required.']);
    exit;
}

try {
    // If it's a password reset, ensure the email actually exists
    if ($action === 'Password Reset') {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if (!$stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'No account found with this email.']);
            exit;
        }
    }

    // 1. Generate 4-digit numeric OTP
    $otp = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
    $expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'));

    // 2. Invalidate older OTPs for this email temporarily
    $stmt = $pdo->prepare("DELETE FROM otps WHERE email = ? AND action = ?");
    $stmt->execute([$email, $action]);

    // 3. Store new OTP
    $stmt = $pdo->prepare("INSERT INTO otps (email, otp, action, expires_at) VALUES (?, ?, ?, ?)");
    $stmt->execute([$email, $otp, $action, $expiresAt]);

    // Fast Response to Client
    ob_start();
    echo json_encode(['success' => true, 'message' => "OTP sent successfully to $email"]);
    $size = ob_get_length();
    header("Content-Length: $size");
    header('Connection: close');
    ob_end_flush();
    @ob_flush();
    flush();
    if (session_id()) session_write_close();
    if (function_exists('fastcgi_finish_request')) fastcgi_finish_request();

    // 4. Send Email Asynchronously
    $emailService = new EmailService();
    $emailService->sendOTP($email, $otp, $action);

} catch (Throwable $e) {
    error_log("OTP Error for $email: " . $e->getMessage());
    // Only send error if we haven't flushed headers yet
    if (!headers_sent()) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
    }
}
?>
