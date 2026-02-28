<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

require_once '../db_connect.php';

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    // Check if data is coming as multipart (POST variable) or raw json
    if (isset($_POST['data'])) {
        $data = json_decode($_POST['data'], true);
    } else {
        $jsonData = file_get_contents('php://input');
        $data = json_decode($jsonData, true);
    }

    if (!$data) {
        error_log("CREATE_CUSTOM_TREATMENT: Invalid JSON data. Raw input: " . file_get_contents('php://input'));
        throw new Exception("Invalid JSON data received.");
    }

    // Handle Video Upload if present
    $video_url = null;
    error_log("CREATE_CUSTOM_TREATMENT: Checking for video upload. FILES count: " . count($_FILES));
    if (isset($_FILES['video'])) {
        error_log("CREATE_CUSTOM_TREATMENT: Video part found. Error code: " . $_FILES['video']['error']);
        if ($_FILES['video']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = './uploads/educational/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileName = time() . '_' . basename($_FILES['video']['name']);
            $fileName = preg_replace("/[^a-zA-Z0-9_\-\.]/", "", $fileName); // sanitize filename
            $targetPath = $uploadDir . $fileName;
            
            error_log("CREATE_CUSTOM_TREATMENT: Attempting to move file to: " . $targetPath);
            if (move_uploaded_file($_FILES['video']['tmp_name'], $targetPath)) {
                $video_url = 'uploads/educational/' . $fileName;
                error_log("CREATE_CUSTOM_TREATMENT: File moved successfully. Stored URL: " . $video_url);
            } else {
                error_log("CREATE_CUSTOM_TREATMENT: Failed to move uploaded file.");
                throw new Exception("Failed to upload the video file.");
            }
        }
    } else {
        error_log("CREATE_CUSTOM_TREATMENT: No 'video' part in FILES.");
    }

    // Extract core operation details
    $specialization_id = $data['specialization_id'] ?? null;
    $name = $data['name'] ?? null;
    $description = $data['description'] ?? null;
    $success_rate = $data['success_rate'] ?? null;
    
    // Automatically generate slug from name (lowercase, spaces to underscores)
    $slug = strtolower(str_replace(' ', '_', $name));

    if (!$specialization_id || !$name) {
        throw new Exception("Missing required operation type fields (specialization_id, name).");
    }

    // Begin database transaction to ensure all or nothing is saved
    $pdo->beginTransaction();

    // 1. Insert Core Operation Type
    $sql_ot = "INSERT INTO operation_types (specialization_id, name, slug, description, success_rate, video_url) 
               VALUES (:spec_id, :name, :slug, :desc, :success_rate, :video_url)";
    $stmt_ot = $pdo->prepare($sql_ot);
    $stmt_ot->execute([
        ':spec_id' => $specialization_id,
        ':name' => $name,
        ':slug' => $slug,
        ':desc' => $description,
        ':success_rate' => $success_rate,
        ':video_url' => $video_url
    ]);
    
    // Retrieve the auto-generated ID
    $operation_type_id = $pdo->lastInsertId();

    // 2. Insert Procedure Steps
    if (isset($data['procedure_steps']) && is_array($data['procedure_steps'])) {
        $sql_steps = "INSERT INTO procedure_steps (operation_type_id, step_number, title, description) 
                      VALUES (:ot_id, :step_num, :title, :desc)";
        $stmt_steps = $pdo->prepare($sql_steps);
        
        foreach ($data['procedure_steps'] as $index => $step) {
            $stmt_steps->execute([
                ':ot_id' => $operation_type_id,
                ':step_num' => $index + 1, // Auto-incrementing step order starting from 1
                ':title' => $step['title'] ?? '',
                ':desc' => $step['description'] ?? null
            ]);
        }
    }

    // 3. Insert Key Topics
    if (isset($data['key_topics']) && is_array($data['key_topics'])) {
        $sql_topics = "INSERT INTO key_topics (operation_type_id, topic, display_order) 
                       VALUES (:ot_id, :topic, :display_order)";
        $stmt_topics = $pdo->prepare($sql_topics);
        
        foreach ($data['key_topics'] as $index => $topic) {
            $stmt_topics->execute([
                ':ot_id' => $operation_type_id,
                ':topic' => $topic['topic'] ?? '',
                ':display_order' => $index + 1
            ]);
        }
    }

    // 4. Insert Benefits
    if (isset($data['benefits']) && is_array($data['benefits'])) {
        $sql_benefits = "INSERT INTO procedure_benefits (operation_type_id, title, description, display_order) 
                         VALUES (:ot_id, :title, :desc, :display_order)";
        $stmt_benefits = $pdo->prepare($sql_benefits);
        
        foreach ($data['benefits'] as $index => $benefit) {
            $stmt_benefits->execute([
                ':ot_id' => $operation_type_id,
                ':title' => $benefit['title'] ?? '',
                ':desc' => $benefit['description'] ?? null,
                ':display_order' => $index + 1
            ]);
        }
    }

    // 5. Insert Risks
    if (isset($data['risks']) && is_array($data['risks'])) {
        $sql_risks = "INSERT INTO procedure_risks (operation_type_id, title, description, risk_percentage) 
                      VALUES (:ot_id, :title, :desc, :risk_percent)";
        $stmt_risks = $pdo->prepare($sql_risks);
        
        foreach ($data['risks'] as $risk) {
            $stmt_risks->execute([
                ':ot_id' => $operation_type_id,
                ':title' => $risk['title'] ?? '',
                ':desc' => $risk['description'] ?? null,
                ':risk_percent' => isset($risk['risk_percentage']) ? (float)$risk['risk_percentage'] : null
            ]);
        }
    }

    // 6. Insert Alternatives
    if (isset($data['alternatives']) && is_array($data['alternatives'])) {
        $sql_alts = "INSERT INTO procedure_alternatives (operation_type_id, name, description, pros, cons, display_order) 
                     VALUES (:ot_id, :name, :desc, :pros, :cons, :display_order)";
        $stmt_alts = $pdo->prepare($sql_alts);
        
        foreach ($data['alternatives'] as $index => $alt) {
            $stmt_alts->execute([
                ':ot_id' => $operation_type_id,
                ':name' => $alt['name'] ?? '',
                ':desc' => $alt['description'] ?? null,
                ':pros' => $alt['pros'] ?? null,
                ':cons' => $alt['cons'] ?? null,
                ':display_order' => $index + 1
            ]);
        }
    }

    // 7. Insert Quiz Questions
    if (isset($data['quizzes']) && is_array($data['quizzes'])) {
        $sql_quizzes = "INSERT INTO quiz_questions (operation_type_id, language, question_text, options, correct_option_index) 
                        VALUES (:ot_id, :lang, :question, :options, :correct_index)";
        $stmt_quizzes = $pdo->prepare($sql_quizzes);
        
        foreach ($data['quizzes'] as $quiz) {
            $lang = isset($quiz['language']) ? $quiz['language'] : 'en';
            
            $stmt_quizzes->execute([
                ':ot_id' => $operation_type_id,
                ':lang' => $lang,
                ':question' => $quiz['question_text'] ?? '',
                ':options' => isset($quiz['options']) ? json_encode($quiz['options']) : '[]',
                ':correct_index' => isset($quiz['correct_option_index']) ? (int)$quiz['correct_option_index'] : 0
            ]);
        }
    }

    // 8. Insert Checklist Items (if doctor supplied them)
    if (isset($data['checklists']) && is_array($data['checklists']) && count($data['checklists']) > 0) {
        $sql_cl = "INSERT INTO procedure_checklists (operation_type_id, title, description, tag, display_order) 
                   VALUES (:ot_id, :title, :desc, :tag, :display_order)";
        $stmt_cl = $pdo->prepare($sql_cl);
        
        foreach ($data['checklists'] as $index => $item) {
            $stmt_cl->execute([
                ':ot_id'         => $operation_type_id,
                ':title'         => $item['title'] ?? '',
                ':desc'          => $item['description'] ?? null,
                ':tag'           => $item['tag'] ?? 'GENERAL',
                ':display_order' => $index + 1
            ]);
        }
    } else {
        // 9. Auto-insert a basic general consent checklist so patient always sees something
        $default_checklists = [
            ['title' => 'Procedure Understanding', 'description' => 'I have been explained the details of this procedure and I understand them.', 'tag' => 'GENERAL'],
            ['title' => 'Risk Acknowledgement', 'description' => 'I understand the risks, side effects, and possible complications of this treatment.', 'tag' => 'GENERAL'],
            ['title' => 'Alternative Options', 'description' => 'I have been informed about alternative treatments available to me.', 'tag' => 'GENERAL'],
            ['title' => 'Questions Answered', 'description' => 'All my questions and concerns regarding this treatment have been answered to my satisfaction.', 'tag' => 'GENERAL'],
            ['title' => 'Voluntary Consent', 'description' => 'I am giving my consent voluntarily and I know I can withdraw it at any time.', 'tag' => 'GENERAL'],
        ];
        $sql_cl_default = "INSERT INTO procedure_checklists (operation_type_id, title, description, tag, display_order) 
                           VALUES (:ot_id, :title, :desc, :tag, :display_order)";
        $stmt_cl_default = $pdo->prepare($sql_cl_default);
        foreach ($default_checklists as $index => $item) {
            $stmt_cl_default->execute([
                ':ot_id'         => $operation_type_id,
                ':title'         => $item['title'],
                ':desc'          => $item['description'],
                ':tag'           => $item['tag'],
                ':display_order' => $index + 1
            ]);
        }
    }

    // Commit transaction if all inserts succeed
    $pdo->commit();

    echo json_encode([
        'success' => true, 
        'message' => 'Custom treatment created successfully',
        'operation_type_id' => $operation_type_id
    ]);

} catch (Exception $e) {
    // Rollback changes if anything fails within the transaction
    if ($pdo && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    echo json_encode([
        'success' => false, 
        'error' => $e->getMessage()
    ]);
}
?>
