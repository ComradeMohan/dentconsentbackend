<?php
require_once '../db_connect.php';

$data = json_decode(file_get_contents('php://input'), true);
$treatmentId = $data['treatment_id'] ?? null;

if (!$treatmentId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'treatment_id is required']);
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM treatments WHERE id = ?");
    $stmt->execute([$treatmentId]);

    echo json_encode(['success' => true]);
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to delete treatment: ' . $e->getMessage()]);
}
?>
