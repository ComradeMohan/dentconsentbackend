<?php
require_once '../db_connect.php';

$data = json_decode(file_get_contents("php://input"), true);

try {
    $treatmentId = $data['treatment_id'] ?? null;
    $checklistData = $data['checklist_data'] ?? []; // Expected format: [['item_text': '', 'is_agreed': true]]

    if (!$treatmentId || empty($checklistData)) {
        echo json_encode(['success' => false, 'error' => 'Missing required fields']);
        exit;
    }

    $pdo->beginTransaction();

    // Clear previous records for this treatment
    $pdo->prepare("DELETE FROM consent_checklist_records WHERE treatment_id = ?")->execute([$treatmentId]);

    // Insert new checklist records
    $stmt = $pdo->prepare("
        INSERT INTO consent_checklist_records (treatment_id, item_text, is_agreed)
        VALUES (?, ?, ?)
    ");
    
    foreach ($checklistData as $item) {
        $stmt->execute([
            $treatmentId,
            $item['item_text'] ?? '',
            (isset($item['is_agreed']) && $item['is_agreed']) ? 1 : 0
        ]);
    }

    $pdo->commit();

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
