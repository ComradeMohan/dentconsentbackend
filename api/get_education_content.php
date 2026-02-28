<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

require_once '../db_connect.php';

try {
    $operationTypeId = $_GET['operation_type_id'] ?? null;

    if (!$operationTypeId) {
        echo json_encode(['success' => false, 'error' => 'operation_type_id is required']);
        exit;
    }

    // Fetch Benefits
    $stmtBenefits = $pdo->prepare("SELECT title, description, display_order FROM procedure_benefits WHERE operation_type_id = ? ORDER BY display_order ASC");
    $stmtBenefits->execute([$operationTypeId]);
    $benefits = $stmtBenefits->fetchAll(PDO::FETCH_ASSOC);

    // Fetch Risks
    $stmtRisks = $pdo->prepare("SELECT title, description, risk_percentage, display_order FROM procedure_risks WHERE operation_type_id = ? ORDER BY display_order ASC");
    $stmtRisks->execute([$operationTypeId]);
    $risks = $stmtRisks->fetchAll(PDO::FETCH_ASSOC);

    // Fetch Key Topics
    $stmtTopics = $pdo->prepare("SELECT topic, display_order FROM key_topics WHERE operation_type_id = ? ORDER BY display_order ASC");
    $stmtTopics->execute([$operationTypeId]);
    $keyTopics = $stmtTopics->fetchAll(PDO::FETCH_ASSOC);

    // Fetch Checklists
    $stmtChecklists = $pdo->prepare("SELECT title, description, tag, display_order FROM procedure_checklists WHERE operation_type_id = ? ORDER BY display_order ASC");
    $stmtChecklists->execute([$operationTypeId]);
    $checklists = $stmtChecklists->fetchAll(PDO::FETCH_ASSOC);

    // Fetch Operation Success Rate and Video URL
    $stmtOpType = $pdo->prepare("SELECT success_rate, video_url FROM operation_types WHERE id = ?");
    $stmtOpType->execute([$operationTypeId]);
    $opTypeResult = $stmtOpType->fetch(PDO::FETCH_ASSOC);
    $successRate = $opTypeResult ? (float)$opTypeResult['success_rate'] : null;
    $videoUrl = $opTypeResult ? $opTypeResult['video_url'] : null;


    // Fetch Procedure Alternatives
    $stmtAlts = $pdo->prepare("SELECT name, description, pros, cons FROM procedure_alternatives WHERE operation_type_id = ? ORDER BY display_order ASC, id ASC");
    $stmtAlts->execute([$operationTypeId]);
    $alternatives = $stmtAlts->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'benefits' => $benefits,
        'risks' => $risks,
        'key_topics' => $keyTopics,
        'checklists' => $checklists,
        'alternatives' => $alternatives,
        'success_rate' => $successRate,
        'video_url' => $videoUrl
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
