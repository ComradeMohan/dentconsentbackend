<?php
// backend/api/generate_consent_pdf.php
require_once __DIR__ . '/../db_connect.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// We assume this is either included or called directly with treatment_id
$isIncluded = count(get_included_files()) > 1;

function generateConsentPdf($pdo, $treatmentId) {
    try {
    // 1. Fetch comprehensive treatment data
    $stmt = $pdo->prepare("
        SELECT 
            t.id as consent_id,
            t.operation_type_id,
            cr.signed_at,
            cr.quiz_score,
            cr.is_checklist_confirmed,
            t.patient_signature as patient_signature_path,
            pp.full_name as patient_name,
            u_pat.email as patient_email,
            pp.mobile_number as mobile,
            dp.full_name as doctor_name,
            u_doc.email as doctor_email,
            dp.council_id,
            dp.specialization,
            dp.signature_url as doctor_signature_path,
            ot.name as operation_name,
            ot.description as operation_description,
            ev.video_url as video_title
        FROM treatments t
        LEFT JOIN consent_records cr ON t.id = cr.treatment_id
        LEFT JOIN patient_profiles pp ON t.patient_id = pp.user_id
        LEFT JOIN doctor_profiles dp ON t.doctor_id = dp.user_id
        LEFT JOIN users u_pat ON t.patient_id = u_pat.id
        LEFT JOIN users u_doc ON t.doctor_id = u_doc.id
        LEFT JOIN operation_types ot ON t.operation_type_id = ot.id
        LEFT JOIN educational_videos ev ON ot.id = ev.operation_type_id 
        WHERE t.id = ?
    ");
    $stmt->execute([$treatmentId]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$data) {
        throw new Exception("Consent data not found.");
    }

    // 2. Fetch Benefits
    $stmtBen = $pdo->prepare("SELECT title, description FROM procedure_benefits WHERE operation_type_id = ?");
    $stmtBen->execute([$data['operation_type_id']]);
    $benefitsHtml = '';
    while ($row = $stmtBen->fetch()) {
        $benefitsHtml .= "<li><strong>" . htmlspecialchars($row['title']) . ":</strong> " . htmlspecialchars($row['description']) . "</li>";
    }

    // 3. Fetch Risks
    $stmtRisk = $pdo->prepare("SELECT title, description FROM procedure_risks WHERE operation_type_id = ?");
    $stmtRisk->execute([$data['operation_type_id']]);
    $risksHtml = '';
    while ($row = $stmtRisk->fetch()) {
        $risksHtml .= "<li><strong>" . htmlspecialchars($row['title']) . ":</strong> " . htmlspecialchars($row['description']) . "</li>";
    }

    // 4. Fallback values
    $dateSigned = $data['signed_at'] ? date('Y-m-d H:i A', strtotime($data['signed_at'])) : date('Y-m-d H:i A');
    $patSigUrl = $data['patient_signature_path'] ? 'http://172.27.207.54/dentconsent/backend/api/' . $data['patient_signature_path'] : '';
    $docSigUrl = $data['doctor_signature_path'] ? 'http://172.27.207.54/dentconsent/backend/api/' . $data['doctor_signature_path'] : '';
    
    // We have to grab actual file contents and base64 encode them for DomPDF to render remote/local images cleanly without allow_url_fopen tricks
    function getBase64Img($url, $defaultFile) {
        $basePath = realpath(__DIR__ . '/../api/'); // Adjust based on your actual docroot
        
        // Strip the base URL to find local path
        $localPath = str_replace('http://172.27.207.54/dentconsent/backend/api/', '', $url);
        
        $fullPath = $basePath . '/' . $localPath;
        if (file_exists($fullPath) && is_file($fullPath)) {
            $type = pathinfo($fullPath, PATHINFO_EXTENSION);
            $data = file_get_contents($fullPath);
            return 'data:image/' . $type . ';base64,' . base64_encode($data);
        }
        return ''; // Or return a placeholder
    }

    $patImgSrc = getBase64Img($patSigUrl, $data['patient_signature_path']);
    $docImgSrc = getBase64Img($docSigUrl, $data['doctor_signature_path']);

    $patImgTag = $patImgSrc ? '<img src="'.$patImgSrc.'" width="200" style="max-height: 100px; object-fit: contain;">' : '<span style="color:#999; line-height: 120px;">[No Signature]</span>';
    $docImgTag = $docImgSrc ? '<img src="'.$docImgSrc.'" width="200" style="max-height: 100px; object-fit: contain;">' : '<span style="color:#999; line-height: 120px;">[No Signature]</span>';

    // 5. HTML Template parsing
    $html = '<!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; margin: 30px; line-height: 1.6; }
            h1 { text-align: center; color: #006666; font-size: 24px; }
            .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #006666; padding-bottom: 10px; }
            .info-box { border: 1px solid #ddd; padding: 15px; margin: 15px 0; background: #f9f9f9; }
            .quiz-score { font-size: 24px; color: #00aa00; font-weight: bold; text-align: center; margin-top: 10px; }
            table { width: 100%; border-collapse: collapse; margin: 20px 0; page-break-inside: avoid; }
            td { padding: 12px; vertical-align: top; width: 50%; }
            .signature { border: 1px dashed #999; height: 120px; text-align: center; width: 300px; padding-top: 10px; }
        </style>
    </head>
    <body>
    
    <div class="header">
        <h1>Digital Informed Consent & Patient Understanding Form</h1>
        <p><strong>Educational Version - College Project</strong></p>
        <p>Consent ID: ' . htmlspecialchars($data['consent_id'] ?? '') . ' | Date: ' . htmlspecialchars($dateSigned) . '</p>
    </div>
    
    <div class="info-box">
        <strong>Patient:</strong> ' . htmlspecialchars($data['patient_name'] ?? '') . ' (' . htmlspecialchars($data['mobile'] ?? '') . ') <br>
        <strong>Doctor:</strong> Dr. ' . htmlspecialchars($data['doctor_name'] ?? '') . ' (' . htmlspecialchars($data['council_id'] ?? '') . ') <br>
        <strong>Specialization:</strong> ' . htmlspecialchars($data['specialization'] ?? '') . '
    </div>
    
    <h2>Procedure: ' . htmlspecialchars($data['operation_name'] ?? '') . '</h2>
    <p>' . htmlspecialchars($data['operation_description'] ?? '') . '</p>
    
    <div class="info-box">
        <strong>Proof of Educational Understanding</strong><br><br>
        Video Watched: <strong>' . htmlspecialchars($data['video_title'] ?? 'N/A') . '</strong><br>
        <div class="quiz-score">Quiz Score: ' . htmlspecialchars($data['quiz_score'] ?? '0') . '% &#x2705;</div>
    </div>
    
    <h3>Benefits I Understood</h3>
    <ul>' . $benefitsHtml . '</ul>
    
    <h3>Risks I Understood</h3>
    <ul>' . $risksHtml . '</ul>
    
    <p style="font-style:italic; margin-top:30px;">
        I confirm that I have been educated through video and quiz about this procedure, its benefits, risks, and alternatives. 
        All my doubts have been cleared. I voluntarily give my consent.
    </p>
    
    <table>
        <tr>
            <td>
                <strong>Patient Signature</strong><br>
                <div class="signature">'. $patImgTag .'</div>
                ' . htmlspecialchars($data['patient_name'] ?? '') . '<br>Date: ' . htmlspecialchars($dateSigned) . '
            </td>
            <td>
                <strong>Doctor Signature</strong><br>
                <div class="signature">'. $docImgTag .'</div>
                Dr. ' . htmlspecialchars($data['doctor_name'] ?? '') . '<br>Date: ' . htmlspecialchars($dateSigned) . '
            </td>
        </tr>
    </table>
    
    <div style="text-align:center; margin-top:40px; font-size:10px; color:#666;">
        Educational Digital Consent • Generated for Academic Purpose • DentalGuru College Project
    </div>
    
    </body>
    </html>';

    // 6. Init DomPDF
    $options = new Options();
    $options->set('isRemoteEnabled', true);
    $options->set('isHtml5ParserEnabled', true);
    $dompdf = new Dompdf($options);

    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    $output = $dompdf->output();
    
    // 7. Save to disk
    $pdfDirPath = __DIR__ . '/uploads/consent_forms/';
    if (!is_dir($pdfDirPath)) {
        mkdir($pdfDirPath, 0777, true);
    }
    $pdfFileName = 'consent_' . $treatmentId . '_' . time() . '.pdf';
    $pdfOutputUrl = 'uploads/consent_forms/' . $pdfFileName;
    
    file_put_contents($pdfDirPath . $pdfFileName, $output);
    $fullPdfPath = $pdfDirPath . $pdfFileName;

    // 8. Send the PDF via EmailService
    require_once __DIR__ . '/email_service.php';
    $emailService = new EmailService();
    
    // Send to Patient
    if (!empty($data['patient_email'])) {
        $emailService->sendConsentPDF(
            $data['patient_email'], 
            $data['patient_name'], 
            $data['doctor_name'], 
            $data['operation_name'], 
            $fullPdfPath
        );
    }
    
    // Send to Doctor
    if (!empty($data['doctor_email'])) {
        $emailService->sendConsentPDF(
            $data['doctor_email'], 
            $data['patient_name'], 
            $data['doctor_name'], 
            $data['operation_name'], 
            $fullPdfPath
        );
    }

    return [
        'success' => true,
        'pdf_url' => 'http://172.27.207.54/dentconsent/backend/api/' . $pdfOutputUrl,
        'local_path' => $pdfOutputUrl
    ];

    } catch (Exception $e) {
        error_log("PDF Gen Error: " . $e->getMessage());
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

// Support direct execution via HTTP GET if not included by another script
if (!$isIncluded) {
    header("Content-Type: application/json; charset=UTF-8");
    $tid = $_GET['treatment_id'] ?? null;
    if (!$tid) {
        echo json_encode(['success' => false, 'error' => 'Missing treatment ID']);
        exit;
    }
    $result = generateConsentPdf($pdo, $tid);
    echo json_encode($result);
}
?>
