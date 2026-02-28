<?php
require_once 'db_connect.php';

$tables_to_check = ['operation_types', 'quizzes', 'quiz_questions', 'quiz_attempts'];
$result = [];

foreach ($tables_to_check as $table) {
    try {
        $stmt = $pdo->query("DESCRIBE $table");
        if ($stmt) {
            $result[$table] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (Exception $e) {
        $result[$table] = "Table not found";
    }
}

echo json_encode($result, JSON_PRETTY_PRINT);
?>
