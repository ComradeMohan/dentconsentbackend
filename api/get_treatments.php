<?php
require_once '../db_connect.php';

$userId = $_GET['user_id'] ?? null;
$role   = $_GET['role'] ?? 'patient';

if (!$userId) {
    http_response_code(400);
    echo json_encode(['error' => 'User ID is required']);
    exit;
}

try {
    if ($role === 'doctor') {
        $stmt = $pdo->prepare("
            SELECT t.id, t.doctor_id, t.patient_id, t.operation_type_id, t.category,
                   t.status, t.clinical_notes, t.created_at, t.patient_signature, t.consent_pdf_url,
                   t.anesthesia_pdf_url, t.anesthesia_required,
                   p.full_name AS patient_name,
                   u_p.profile_image AS patient_image,
                   ot.success_rate
            FROM treatments t
            JOIN patient_profiles p ON t.patient_id = p.user_id
            JOIN users u_p ON t.patient_id = u_p.id
            LEFT JOIN operation_types ot ON t.operation_type_id = ot.id
            WHERE t.doctor_id = ?
            ORDER BY t.created_at DESC
        ");
    } else {
        $stmt = $pdo->prepare("
            SELECT t.id, t.doctor_id, t.patient_id, t.operation_type_id, t.category,
                   t.status, t.clinical_notes, t.created_at, t.patient_signature, t.consent_pdf_url,
                   t.anesthesia_pdf_url, t.anesthesia_required,
                   d.full_name AS doctor_name,
                   u_d.profile_image AS doctor_image,
                   ot.success_rate
            FROM treatments t
            JOIN doctor_profiles d ON t.doctor_id = d.user_id
            JOIN users u_d ON t.doctor_id = u_d.id
            LEFT JOIN operation_types ot ON t.operation_type_id = ot.id
            WHERE t.patient_id = ?
            ORDER BY t.created_at DESC
        ");
    }

    $stmt->execute([$userId]);
    $treatments = $stmt->fetchAll();

    echo json_encode(['success' => true, 'treatments' => $treatments]);

} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch treatments: ' . $e->getMessage()]);
}
?>
