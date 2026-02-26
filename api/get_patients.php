<?php
require_once '../db_connect.php';

$query = $_GET['q'] ?? '';

try {
    if (empty($query)) {
        $stmt = $pdo->query("SELECT user_id as id, full_name as name, created_at FROM patient_profiles LIMIT 20");
    } else {
        $stmt = $pdo->prepare("SELECT user_id as id, full_name as name, created_at FROM patient_profiles WHERE full_name LIKE ? OR user_id IN (SELECT id FROM users WHERE email LIKE ?) LIMIT 20");
        $stmt->execute(["%$query%", "%$query%"]);
    }
    
    $patients = $stmt->fetchAll();
    echo json_encode(['success' => true, 'patients' => $patients]);

} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch patients: ' . $e->getMessage()]);
}
?>
