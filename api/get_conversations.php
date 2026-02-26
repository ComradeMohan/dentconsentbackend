<?php
require_once '../db_connect.php';

$userId = $_GET['user_id'] ?? null;

if (!$userId) {
    http_response_code(400);
    echo json_encode(['error' => 'User ID is required']);
    exit;
}

try {
    // Determine the role of the user requesting
    $stmtRole = $pdo->prepare("SELECT role FROM users WHERE id = ?");
    $stmtRole->execute([$userId]);
    $user = $stmtRole->fetch();
    
    if (!$user) {
        http_response_code(404);
        echo json_encode(['error' => 'User not found']);
        exit;
    }

    $role = $user['role'];
    $conversations = [];

    // Doctor requesting conversations -> fetch from patients they treated OR users they exchanged messages with
    if ($role === 'doctor') {
        $stmt = $pdo->prepare("
            SELECT DISTINCT u.id as userId, p.full_name as fullName, u.role, u.profile_image, 
            (SELECT message FROM messages WHERE (sender_id = ? AND receiver_id = u.id) OR (sender_id = u.id AND receiver_id = ?) ORDER BY created_at DESC LIMIT 1) as lastMessage,
            (SELECT created_at FROM messages WHERE (sender_id = ? AND receiver_id = u.id) OR (sender_id = u.id AND receiver_id = ?) ORDER BY created_at DESC LIMIT 1) as lastMessageTime
            FROM users u
            JOIN patient_profiles p ON u.id = p.user_id
            LEFT JOIN treatments t ON t.patient_id = u.id
            WHERE t.doctor_id = ? OR u.id IN (SELECT sender_id FROM messages WHERE receiver_id = ?) OR u.id IN (SELECT receiver_id FROM messages WHERE sender_id = ?)
        ");
        $stmt->execute([$userId, $userId, $userId, $userId, $userId, $userId, $userId]);
        $conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } 
    // Patient requesting conversations -> fetch from doctors that treat them OR users they exchanged messages with
    else {
        $stmt = $pdo->prepare("
            SELECT DISTINCT u.id as userId, d.full_name as fullName, u.role, u.profile_image, 
            (SELECT message FROM messages WHERE (sender_id = ? AND receiver_id = u.id) OR (sender_id = u.id AND receiver_id = ?) ORDER BY created_at DESC LIMIT 1) as lastMessage,
            (SELECT created_at FROM messages WHERE (sender_id = ? AND receiver_id = u.id) OR (sender_id = u.id AND receiver_id = ?) ORDER BY created_at DESC LIMIT 1) as lastMessageTime
            FROM users u
            JOIN doctor_profiles d ON u.id = d.user_id
            LEFT JOIN treatments t ON t.doctor_id = u.id
            WHERE t.patient_id = ? OR u.id IN (SELECT sender_id FROM messages WHERE receiver_id = ?) OR u.id IN (SELECT receiver_id FROM messages WHERE sender_id = ?)
        ");
        $stmt->execute([$userId, $userId, $userId, $userId, $userId, $userId, $userId]);
        $conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Sort by most recent message
    usort($conversations, function($a, $b) {
        $timeA = strtotime($a['lastMessageTime'] ?? '1970-01-01');
        $timeB = strtotime($b['lastMessageTime'] ?? '1970-01-01');
        return $timeB - $timeA;
    });

    echo json_encode(['success' => true, 'conversations' => $conversations]);

} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch conversations: ' . $e->getMessage()]);
}
?>
