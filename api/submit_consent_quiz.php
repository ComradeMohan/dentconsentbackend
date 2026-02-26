<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

require_once '../db_connect.php';

try {
    $data = json_decode(file_get_contents("php://input"), true);
    
    $treatmentId = $data['treatment_id'] ?? null;
    $quizScore = $data['quiz_score'] ?? null;
    $totalQuestions = $data['total_questions'] ?? null;
    $quizData = $data['quiz_data'] ?? []; // Expected format: [['question_text': '', 'selected_option': '', 'is_correct': true]]

    if (!$treatmentId || $quizScore === null || $totalQuestions === null) {
        echo json_encode(['success' => false, 'error' => 'Missing required fields']);
        exit;
    }

    $pdo->beginTransaction();

    // Insert or update overall consent record score
    $stmt = $pdo->prepare("
        INSERT INTO consent_records (treatment_id, quiz_score, total_questions) 
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE 
            quiz_score = VALUES(quiz_score),
            total_questions = VALUES(total_questions)
    ");
    
    $stmt->execute([$treatmentId, $quizScore, $totalQuestions]);

    // Clear previous attempts for this treatment so we can neatly re-insert
    $pdo->prepare("DELETE FROM quiz_attempts WHERE treatment_id = ?")->execute([$treatmentId]);

    // Insert each question's result
    if (!empty($quizData)) {
        $stmtAttempt = $pdo->prepare("
            INSERT INTO quiz_attempts (treatment_id, question_text, selected_option, is_correct)
            VALUES (?, ?, ?, ?)
        ");
        
        foreach ($quizData as $qd) {
            $stmtAttempt->execute([
                $treatmentId,
                $qd['question_text'] ?? '',
                $qd['selected_option'] ?? '',
                (isset($qd['is_correct']) && $qd['is_correct']) ? 1 : 0
            ]);
        }
    }

    // If perfect score, update treatment status to 'educated'
    if ($quizScore === $totalQuestions && $totalQuestions > 0) {
        $pdo->prepare("UPDATE treatments SET status = 'educated' WHERE id = ? AND status != 'completed'")->execute([$treatmentId]);
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
