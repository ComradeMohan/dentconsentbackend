<?php
require_once '../db_connect.php';

$data = json_decode(file_get_contents('php://input'), true);

$email       = trim($data['email'] ?? '');
$newPassword = $data['new_password'] ?? '';

if (empty($email) || empty($newPassword)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Email and new password are required.']);
    exit;
}

if (strlen($newPassword) < 6) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters.']);
    exit;
}

try {
    // 1. Check if user with given email exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'No account found with this email address.']);
        exit;
    }

    // 2. Hash the new password
    $newHash = password_hash($newPassword, PASSWORD_DEFAULT);

    // 3. Update the password in the DB
    $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
    $stmt->execute([$newHash, $user['id']]);

    echo json_encode(['success' => true, 'message' => 'Password has been reset successfully.']);

} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>
