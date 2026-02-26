<?php
require 'db_connect.php';
require 'api/generate_consent_pdf.php';
$stmt = $pdo->query('SELECT id FROM treatments ORDER BY updated_at DESC LIMIT 1');
$row = $stmt->fetch(PDO::FETCH_ASSOC);
print_r(generateConsentPdf($pdo, $row['id']));
?>
