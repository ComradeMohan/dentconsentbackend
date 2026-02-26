<?php
require_once '../db_connect.php';

$data = json_decode(file_get_contents('php://input'), true);

$doctorId        = $data['doctor_id'] ?? null;
$patientId       = $data['patient_id'] ?? null;
$operationTypeId = $data['operation_type_id'] ?? null;
$clinicalNotes   = $data['clinical_notes'] ?? '';

if (!$doctorId || !$patientId || !$operationTypeId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'doctor_id, patient_id, and operation_type_id are required']);
    exit;
}

try {
    // Resolve operation name from server table
    $opStmt = $pdo->prepare("SELECT name, slug FROM operation_types WHERE id = ?");
    $opStmt->execute([$operationTypeId]);
    $opType = $opStmt->fetch();

    if (!$opType) {
        echo json_encode(['success' => false, 'error' => 'Invalid operation_type_id']);
        exit;
    }

    $categoryName = $opType['name'];
$anesthesiaRequired = isset($data['anesthesia_required']) && $data['anesthesia_required'] ? 1 : 0;

$stmt = $pdo->prepare("INSERT INTO treatments (doctor_id, patient_id, operation_type_id, category, anesthesia_required, clinical_notes, status) VALUES (?, ?, ?, ?, ?, ?, 'in_progress')");
$stmt->execute([$doctorId, $patientId, $operationTypeId, $categoryName, $anesthesiaRequired, $clinicalNotes]);
$treatmentId = $pdo->lastInsertId();

    echo json_encode(['success' => true, 'treatment_id' => $treatmentId]);

} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to create treatment: ' . $e->getMessage()]);
}
?>
