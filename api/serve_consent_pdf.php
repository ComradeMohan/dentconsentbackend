<?php
/**
 * serve_consent_pdf.php
 * Streams the saved consent PDF file directly to the browser.
 * Usage: GET ?treatment_id=13
 */
header("Access-Control-Allow-Origin: *");

require_once '../db_connect.php';

$treatmentId = $_GET['treatment_id'] ?? null;

if (!$treatmentId) {
    http_response_code(400);
    header("Content-Type: application/json");
    echo json_encode(['error' => 'Missing treatment_id']);
    exit;
}

try {
    // Fetch the stored PDF path from the database
    $stmt = $pdo->prepare("SELECT consent_pdf_url FROM treatments WHERE id = ?");
    $stmt->execute([$treatmentId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row || empty($row['consent_pdf_url'])) {
        // PDF not yet generated — generate it now
        require_once __DIR__ . '/generate_consent_pdf.php';
        $result = generateConsentPdf($pdo, $treatmentId);

        if (!$result['success']) {
            http_response_code(500);
            header("Content-Type: application/json");
            echo json_encode(['error' => $result['error'] ?? 'PDF generation failed']);
            exit;
        }

        // Save the URL back to the DB
        $updateStmt = $pdo->prepare("UPDATE treatments SET consent_pdf_url = ? WHERE id = ?");
        $updateStmt->execute([$result['local_path'], $treatmentId]);

        $pdfLocalPath = $result['local_path'];
    } else {
        $pdfLocalPath = $row['consent_pdf_url'];
    }

    // Resolve full filesystem path
    $fullPath = __DIR__ . '/' . $pdfLocalPath;

    if (!file_exists($fullPath)) {
        // File was stored but is missing — regenerate
        require_once __DIR__ . '/generate_consent_pdf.php';
        $result = generateConsentPdf($pdo, $treatmentId);
        if (!$result['success']) {
            http_response_code(500);
            header("Content-Type: application/json");
            echo json_encode(['error' => 'PDF file missing and regeneration failed']);
            exit;
        }
        $fullPath = __DIR__ . '/' . $result['local_path'];
        $updateStmt = $pdo->prepare("UPDATE treatments SET consent_pdf_url = ? WHERE id = ?");
        $updateStmt->execute([$result['local_path'], $treatmentId]);
    }

    // Stream the PDF
    $filename = 'consent_treatment_' . $treatmentId . '.pdf';
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="' . $filename . '"');
    header('Content-Length: ' . filesize($fullPath));
    header('Cache-Control: no-cache');
    readfile($fullPath);
    exit;

} catch (Exception $e) {
    http_response_code(500);
    header("Content-Type: application/json");
    echo json_encode(['error' => $e->getMessage()]);
}
?>
