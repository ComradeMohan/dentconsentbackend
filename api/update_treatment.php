<?php
require_once '../db_connect.php';

$data = json_decode(file_get_contents('php://input'), true);

$treatmentId = $data['treatment_id'] ?? null;
$clinicalNotes = $data['clinical_notes'] ?? '';
$anesthesiaRequired = isset($data['anesthesia_required']) && $data['anesthesia_required'] ? 1 : 0;

if (!$treatmentId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'treatment_id is required']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE treatments SET clinical_notes = ?, anesthesia_required = ? WHERE id = ?");
    $stmt->execute([$clinicalNotes, $anesthesiaRequired, $treatmentId]);

    echo json_encode(['success' => true]);
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to update treatment: ' . $e->getMessage()]);
}
?>
