<?php
require_once 'db_connect.php';

// Create language column if not exists
try {
    $pdo->exec("ALTER TABLE quiz_questions ADD COLUMN language VARCHAR(10) DEFAULT 'en' AFTER operation_type_id");
    echo "Added language column.\n";
} catch (Exception $e) {
    echo "Language column probably exists: " . $e->getMessage() . "\n";
}

// Clear old questions
$pdo->exec("TRUNCATE TABLE quiz_questions");
echo "Cleared old questions.\n";

$slugMap = [
    "single_tooth_implant" => "single_tooth_implant",
    "multiple_tooth_implant" => "multiple_tooth_implant",
    "full_arch_implant" => "full_arch_implant",
    "implant_supported_bridge" => "implant_supported_bridge",
    "implant_supported_denture" => "implant_supported_denture",
    "bone_grafting" => "bone_grafting_support",
    "crown" => "dental_crown",
    "bridge" => "dental_bridge",
    "complete_denture" => "complete_denture",
    "partial_denture" => "partial_denture",
    "veneer" => "dental_veneer",
    "full_mouth_rehabilitation" => "full_mouth_rehabilitation"
];

$stmt = $pdo->query("SELECT id, slug FROM operation_types");
$opTypes = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // slug => id
$slugToId = [];
foreach ($opTypes as $id => $slug) {
    $slugToId[$slug] = $id;
}

$files = [
    'en' => 'quiz_english.json',
    'hi' => 'quiz_hindi.json',
    'te' => 'quiz_telugu.json'
];

$insertStmt = $pdo->prepare("INSERT INTO quiz_questions (operation_type_id, language, question_text, options, correct_option_index) VALUES (?, ?, ?, ?, ?)");

$totalImported = 0;

foreach ($files as $lang => $file) {
    if (!file_exists($file)) {
        echo "File $file not found.\n";
        continue;
    }
    
    $jsonContent = file_get_contents($file);
    $data = json_decode($jsonContent, true);
    
    if (isset($data['procedures'])) {
        foreach ($data['procedures'] as $jsonKey => $procData) {
            if (isset($slugMap[$jsonKey])) {
                $dbSlug = $slugMap[$jsonKey];
                if (isset($slugToId[$dbSlug])) {
                    $opId = $slugToId[$dbSlug];
                    
                    foreach ($procData['questions'] as $q) {
                        $correctLetter = $q['correct'];
                        $correctIndex = 0;
                        if ($correctLetter === 'B') $correctIndex = 1;
                        if ($correctLetter === 'C') $correctIndex = 2;
                        if ($correctLetter === 'D') $correctIndex = 3;
                        
                        $insertStmt->execute([
                            $opId,
                            $lang,
                            $q['question'],
                            json_encode($q['options'], JSON_UNESCAPED_UNICODE),
                            $correctIndex
                        ]);
                        $totalImported++;
                    }
                } else {
                    echo "Slug $dbSlug not found in DB\n";
                }
            } else {
                echo "JSON key $jsonKey not mapped\n";
            }
        }
    }
}

echo "Successfully imported $totalImported questions.\n";
?>
