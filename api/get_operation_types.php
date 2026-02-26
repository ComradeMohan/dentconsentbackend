<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

require_once '../db_connect.php';

try {
    $specializationId   = $_GET['specialization_id'] ?? null;
    $specializationName = $_GET['specialization_name'] ?? null;

    $params = [];
    $where  = "";

    if ($specializationId) {
        $where  = "WHERE ot.specialization_id = ?";
        $params = [$specializationId];
    } elseif ($specializationName) {
        // Match by first 7 chars of DB specialization name within the doctor's string
        // e.g. "Implantologist" contains "Implant" (first 7 of "Implantology") → TRUE
        // e.g. "Prosthodontist" contains "Prostho" (first 7 of "Prosthodontics") → TRUE
        $where  = "WHERE INSTR(LOWER(?), LOWER(LEFT(s.name, 7))) > 0";
        $params = [$specializationName];
    }

    $sql = "SELECT ot.id, ot.specialization_id, ot.name, ot.slug, ot.description,
                   ot.success_rate, ot.icon, s.name AS specialization_name,
                   ev.video_url, ev.thumbnail_url
            FROM operation_types ot
            JOIN specializations s ON ot.specialization_id = s.id
            LEFT JOIN educational_videos ev ON ot.id = ev.operation_type_id
            $where
            ORDER BY s.id, ot.id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $operations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $specs = $pdo->query("SELECT * FROM specializations ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'operations' => $operations, 'specializations' => $specs]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>

