<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

require_once '../db_connect.php';

try {
    $operationTypeId = $_GET['operation_type_id'] ?? null;
    $language = $_GET['language'] ?? 'en'; // default english

    if (!$operationTypeId) {
        echo json_encode(['success' => false, 'error' => 'operation_type_id is required']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT question_text as question, options, correct_option_index FROM quiz_questions WHERE operation_type_id = ? AND language = ?");
    $stmt->execute([$operationTypeId, $language]);
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // If no questions found in requested language, fallback to English
    if (empty($questions) && $language !== 'en') {
        $stmt->execute([$operationTypeId, 'en']);
        $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Decode options from JSON string back to array for each question
    foreach ($questions as &$q) {
        $q['options'] = json_decode($q['options'], true);
    }

    echo json_encode(['success' => true, 'questions' => $questions]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
