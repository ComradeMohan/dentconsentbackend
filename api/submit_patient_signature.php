<?php
require_once __DIR__ . '/../db_connect.php';

$data = json_decode(file_get_contents("php://input"), true);

try {
    $treatmentId = $data['treatment_id'] ?? null;
    $signatureBase64 = $data['signature_base64'] ?? null; 
    $isConfirmed = $data['is_confirmed'] ?? false;

    if (!$treatmentId || !$signatureBase64 || !$isConfirmed) {
        echo json_encode(['success' => false, 'error' => 'Missing required fields']);
        exit;
    }

    // 1. Process and save the Base64 image
    $base64_string = $signatureBase64;
    // Check if the base64 string has a data URI scheme and strip it
    if (strpos($base64_string, 'data:image/') === 0) {
        $parts = explode(',', $base64_string);
        $base64_string = $parts[1];
    }
    
    $image_data = base64_decode($base64_string);
    if ($image_data === false) {
        echo json_encode(['success' => false, 'error' => 'Invalid base64 signature data']);
        exit;
    }

    $upload_dir = 'uploads/signatures/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $hashed_filename = 'pat_sig_' . md5($treatmentId . time() . rand()) . '.png';
    $upload_path = $upload_dir . $hashed_filename;
    
    if (!file_put_contents($upload_path, $image_data)) {
        echo json_encode(['success' => false, 'error' => 'Failed to save signature image to disk']);
        exit;
    }

    // 2. Save the filepath into treatments 
    $stmt = $pdo->prepare("
        UPDATE treatments 
        SET patient_signature = ? 
        WHERE id = ?
    ");
    
    $stmt->execute([$upload_path, $treatmentId]);

    if ($stmt->rowCount() > 0) {
        $stmt_ar = $pdo->prepare("
            SELECT t.anesthesia_required, o.specialization_id, o.name as operation_name,
                   pp.full_name as patient_name, u_pat.email as patient_email,
                   dp.full_name as doctor_name, u_doc.email as doctor_email
            FROM treatments t 
            LEFT JOIN operation_types o ON t.operation_type_id = o.id 
            LEFT JOIN patient_profiles pp ON t.patient_id = pp.user_id
            LEFT JOIN users u_pat ON t.patient_id = u_pat.id
            LEFT JOIN doctor_profiles dp ON t.doctor_id = dp.user_id
            LEFT JOIN users u_doc ON t.doctor_id = u_doc.id
            WHERE t.id = ?
        ");
        $stmt_ar->execute([$treatmentId]);
        $arData = $stmt_ar->fetch(PDO::FETCH_ASSOC);
        $anesthesiaRequired = $arData ? $arData['anesthesia_required'] : 0;
        $specId = $arData ? $arData['specialization_id'] : 1;
        
        // Execute the PDF Generator formally based on specialization
        if ($specId == 2) {
            require_once __DIR__ . '/generate_prosthodontics_pdf.php';
            $pdfResult = generateProsthodonticsPdf($pdo, $treatmentId);
        } else {
            require_once __DIR__ . '/generate_implant_pdf.php';
            $pdfResult = generateImplantPdf($pdo, $treatmentId);
        }

        $anesthesiaPdfResult = ['success' => false];
        
        if ($anesthesiaRequired == 1) {
            require_once __DIR__ . '/generate_anesthesia_pdf.php';
            $anesthesiaPdfResult = generateAnesthesiaPdf($pdo, $treatmentId);
        }
        
        if ($pdfResult['success']) {
            $localPath = $pdfResult['local_path'];
            $aUrl = $anesthesiaPdfResult['success'] ? $anesthesiaPdfResult['local_path'] : null;

            $updatePdfStmt = $pdo->prepare("UPDATE treatments SET status = 'Completed', consent_pdf_url = ?, anesthesia_pdf_url = ?, implant_pdf_url = NULL WHERE id = ?");
            $updatePdfStmt->execute([$localPath, $aUrl, $treatmentId]);
            
            $resp = [
                'success' => true, 
                'pdf_url' => $pdfResult['pdf_url'],
                'anesthesia_pdf_url' => $anesthesiaPdfResult['success'] ? $anesthesiaPdfResult['pdf_url'] : null,
                'implant_pdf_url' => null
            ];
            error_log(print_r($resp, true), 3, 'doc_log.txt');
            
            // Fast Response to Client
            ob_start();
            echo json_encode($resp);
            $size = ob_get_length();
            header("Content-Length: $size");
            header('Connection: close');
            ob_end_flush();
            @ob_flush();
            flush();
            if (session_id()) session_write_close();
            if (function_exists('fastcgi_finish_request')) fastcgi_finish_request();

            // Auto-send emails with attachments
            require_once __DIR__ . '/email_service.php';
            $emailService = new EmailService();
            $pdfsToAttach = [__DIR__ . '/' . $localPath];
            if ($aUrl) {
                $pdfsToAttach[] = __DIR__ . '/' . $aUrl;
            }

            $patientEmail = $arData['patient_email'] ?? '';
            $doctorEmail = $arData['doctor_email'] ?? '';
            $patientName = $arData['patient_name'] ?? 'Patient';
            $doctorName = $arData['doctor_name'] ?? 'Doctor';
            $opName = $arData['operation_name'] ?? 'Treatment';

            if (!empty($patientEmail)) {
                $emailService->sendConsentPDF($patientEmail, $patientName, $doctorName, $opName, $pdfsToAttach);
            }
            if (!empty($doctorEmail)) {
                $emailService->sendConsentPDF($doctorEmail, $patientName, $doctorName, $opName, $pdfsToAttach);
            }
        } else {
            $resp = ['success' => false, 'error' => 'Signature Saved but PDF Failed: ' . $pdfResult['error']];
            error_log(print_r($resp, true), 3, 'doc_log.txt');
            echo json_encode($resp);
        }
    } else {
        $resp = ['success' => false, 'error' => 'Treatment ID not found or signature already saved'];
        error_log(print_r($resp, true), 3, 'doc_log.txt');
        echo json_encode($resp);
    }
} catch (Exception $e) {
    $resp = ['success' => false, 'error' => $e->getMessage()];
    error_log(print_r($resp, true), 3, 'doc_log.txt');
    echo json_encode($resp);
}
?>
