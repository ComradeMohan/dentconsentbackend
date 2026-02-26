<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

require_once '../db_connect.php';

try {
    $operationTypeId = $_GET['operation_type_id'] ?? null;
    $slug            = $_GET['slug'] ?? null;

    if ($operationTypeId) {
        $stmt = $pdo->prepare("
            SELECT ps.id, ps.step_number, ps.title, ps.description, ps.duration_note,
                   ot.name AS operation_name, ot.slug, ot.success_rate, ev.video_url, ev.thumbnail_url
            FROM procedure_steps ps
            JOIN operation_types ot ON ps.operation_type_id = ot.id
            LEFT JOIN educational_videos ev ON ot.id = ev.operation_type_id
            WHERE ps.operation_type_id = ?
            ORDER BY ps.step_number ASC
        ");
        $stmt->execute([$operationTypeId]);
    } elseif ($slug) {
        $stmt = $pdo->prepare("
            SELECT ps.id, ps.step_number, ps.title, ps.description, ps.duration_note,
                   ot.name AS operation_name, ot.slug, ot.success_rate, ev.video_url, ev.thumbnail_url
            FROM procedure_steps ps
            JOIN operation_types ot ON ps.operation_type_id = ot.id
            LEFT JOIN educational_videos ev ON ot.id = ev.operation_type_id
            WHERE ot.slug = ?
            ORDER BY ps.step_number ASC
        ");
        $stmt->execute([$slug]);
    } else {
        echo json_encode(['success' => false, 'error' => 'operation_type_id or slug required']);
        exit;
    }

    $steps = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'steps' => $steps]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
