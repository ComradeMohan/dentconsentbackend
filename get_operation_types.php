<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

require_once 'db_connect.php';

try {
    $specializationId = $_GET['specialization_id'] ?? null;

    if ($specializationId) {
        $stmt = $pdo->prepare("SELECT ot.id, ot.specialization_id, ot.name, ot.slug, ot.description, ot.success_rate, ot.icon, s.name AS specialization_name FROM operation_types ot JOIN specializations s ON ot.specialization_id = s.id WHERE ot.specialization_id = ? ORDER BY ot.id");
        $stmt->execute([$specializationId]);
    } else {
        $stmt = $pdo->query("SELECT ot.id, ot.specialization_id, ot.name, ot.slug, ot.description, ot.success_rate, ot.icon, s.name AS specialization_name FROM operation_types ot JOIN specializations s ON ot.specialization_id = s.id ORDER BY s.id, ot.id");
    }

    $operations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Also fetch specializations list
    $specs = $pdo->query("SELECT * FROM specializations ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'operations' => $operations,
        'specializations' => $specs
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
