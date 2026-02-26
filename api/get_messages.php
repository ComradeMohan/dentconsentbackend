<?php
require_once '../db_connect.php';

$user1Id = $_GET['user1_id'] ?? null;
$user2Id = $_GET['user2_id'] ?? null;

if (!$user1Id || !$user2Id) {
    http_response_code(400);
    echo json_encode(['error' => 'Both user IDs are required']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT id, sender_id, receiver_id, message, created_at 
        FROM messages 
        WHERE (sender_id = ? AND receiver_id = ?) 
           OR (sender_id = ? AND receiver_id = ?) 
        ORDER BY created_at ASC
    ");
    
    $stmt->execute([$user1Id, $user2Id, $user2Id, $user1Id]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'messages' => $messages]);

} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch messages: ' . $e->getMessage()]);
}
?>
