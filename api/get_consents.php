<?php
require_once '../db_connect.php';

$patientId = $_GET['patient_id'] ?? null;

if (!$patientId) {
    http_response_code(400);
    echo json_encode(['error' => 'Patient ID is required']);
    exit;
}

try {
    // Fetch treatments and their consent records
    $stmt = $pdo->prepare("
        SELECT 
            t.id as treatment_id, 
            t.category, 
            t.status, 
            t.created_at as treatment_date,
            cr.signed_at,
            cr.signature_path,
            d.full_name as doctor_name
        FROM treatments t
        LEFT JOIN consent_records cr ON t.id = cr.treatment_id
        JOIN doctor_profiles d ON t.doctor_id = d.user_id
        WHERE t.patient_id = ?
        ORDER BY t.created_at DESC
    ");
    $stmt->execute([$patientId]);
    $consents = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'consents' => $consents]);

} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch consents: ' . $e->getMessage()]);
}
?>
