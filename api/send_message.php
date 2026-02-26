<?php
require_once '../db_connect.php';

// Get JSON input
$data = json_decode(file_get_contents('php://input'), true);

$senderId = $data['sender_id'] ?? null;
$receiverId = $data['receiver_id'] ?? null;
$message = $data['message'] ?? '';

if (!$senderId || !$receiverId || empty($message)) {
    http_response_code(400);
    echo json_encode(['error' => 'Sender ID, Receiver ID, and Message are required']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
    $stmt->execute([$senderId, $receiverId, $message]);
    $messageId = $pdo->lastInsertId();

    echo json_encode(['success' => true, 'message_id' => $messageId]);

} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to send message: ' . $e->getMessage()]);
}
?>
