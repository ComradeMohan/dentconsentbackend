<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

require_once '../db_connect.php';

try {
    $stmt = $pdo->prepare("SELECT * FROM general_education ORDER BY id ASC");
    $stmt->execute();
    $educationItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'education_items' => $educationItems
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
