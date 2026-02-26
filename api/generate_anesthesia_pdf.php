<?php
require_once __DIR__ . '/../db_connect.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

function generateAnesthesiaPdf($pdo, $treatmentId) {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                t.id as consent_id,
                cr.signed_at,
                t.patient_signature as patient_signature_path,
                pp.full_name as patient_name,
                pp.mobile_number as mobile,
                pp.gender,
                dp.full_name as doctor_name,
                dp.signature_url as doctor_signature_path
            FROM treatments t
            LEFT JOIN consent_records cr ON t.id = cr.treatment_id
            LEFT JOIN patient_profiles pp ON t.patient_id = pp.user_id
            LEFT JOIN doctor_profiles dp ON t.doctor_id = dp.user_id
            WHERE t.id = ?
        ");
        $stmt->execute([$treatmentId]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            throw new Exception("Consent data not found.");
        }

        // Calculate Age if DOB is supplied instead. Wait, 'patient_profiles' has 'dob' in SQL.
        $stmt_age = $pdo->prepare("SELECT dob, gender FROM patient_profiles WHERE user_id = (SELECT patient_id FROM treatments WHERE id = ?)");
        $stmt_age->execute([$treatmentId]);
        $pInfo = $stmt_age->fetch();
        $age = '';
        if ($pInfo && $pInfo['dob']) {
           $dob = new DateTime($pInfo['dob']);
           $now = new DateTime();
           $age = $now->diff($dob)->y . " yrs";
        }
        $gender = $pInfo ? $pInfo['gender'] : '';

        $dateSigned = $data['signed_at'] ? date('d/m/Y', strtotime($data['signed_at'])) : date('d/m/Y');
        
        // Base64 Images Local
        function getBase64ImgA($fullPath) {
            if ($fullPath && file_exists($fullPath) && is_file($fullPath)) {
                $type = pathinfo($fullPath, PATHINFO_EXTENSION);
                $d = file_get_contents($fullPath);
                return 'data:image/' . $type . ';base64,' . base64_encode($d);
            }
            return '';
        }

        $patImgSrc = getBase64ImgA($data['patient_signature_path'] ? realpath(__DIR__ . '/' . $data['patient_signature_path']) : '');

        $patSigHtml = $patImgSrc ? '<img src="'.$patImgSrc.'" style="max-height: 75px; object-fit: contain; margin-top: -10px;">' : '<span style="color:#transparent; font-weight:normal;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>';

        $logoLeft = getBase64ImgA(realpath(__DIR__ . '/../tiangle.png'));
        $logoRight = getBase64ImgA(realpath(__DIR__ . '/../circulear.png'));

        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8">
        <title>Consent Form</title>
        <style>
        @page { size: A4; margin: 30pt 40pt; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: "Times New Roman", Georgia, serif; font-size: 10pt; color: #000; line-height: 1.5; border: 1.5pt solid #000; padding: 30pt; }
        .logos { text-align: center; margin-bottom: 12pt; }
        .logos img { height: 72pt; width: auto; display: inline-block; margin: 0 19pt; }
        .title { text-align: center; font-weight: bold; font-size: 10pt; text-decoration: underline; line-height: 1.6; margin-bottom: 14pt; }
        .fields { width: 100%; margin-bottom: 13pt; }
        .fields td { padding-bottom: 5pt; font-size: 9.2pt; }
        .uP { display: inline-block; border-bottom: 1pt solid #333; color: #0000cc; font-weight: 500; padding: 0 3pt; font-size: 9.2pt; vertical-align: bottom; }
        .body { font-size: 8.8pt; line-height: 1.68; text-align: justify; margin-bottom: 9pt; }
        table.risks { width: 100%; border-collapse: collapse; font-size: 8.6pt; }
        .sr { width: 100%; margin-top: 20pt; }
        </style></head><body>
        <div class="logos">
            <img src="'.$logoLeft.'" alt="">
            <img src="'.$logoRight.'" alt="">
        </div>
        <div class="title">SAVEETHA DENTAL COLLEGE AND HOSPITAL<br>CONSENT FOR LOCAL ANESTHESIA</div>
        <table class="fields">
            <tr>
                <td style="width:50%">Patient Name: <span class="uP" style="min-width:140pt">'.htmlspecialchars($data["patient_name"] ?? "&nbsp;").'</span></td>
                <td style="width:50%">Date: <span class="uP" style="min-width:100pt">'.htmlspecialchars($dateSigned).'</span></td>
            </tr>
            <tr>
                <td>Age: <span class="uP" style="min-width:140pt">'.htmlspecialchars($age).'</span></td>
                <td>Gender: <span class="uP" style="min-width:100pt">'.htmlspecialchars($gender).'</span></td>
            </tr>
        </table>
        
        <div class="body" style="font-weight:bold">An alternative to the following has been explained and I authorize the Dentist to do the following treatment and any others necessary for the reasons in. I understand I may need further treatment by a specialist or even hospitalization if complications arise during or following treatment, the cost of which is my responsibility. I have been given satisfactory answers to all of my questions, and I wish to proceed with the Recommended Treatment.</div>
        <div class="body">These potential risks and complications, include, but are not limited to, the following:</div>
        
        <table class="risks"><tbody>
            <tr><td style="width:12pt;font-weight:bold;vertical-align:top;padding-right:4pt;padding-bottom:5pt">1.</td><td style="text-align:justify;line-height:1.65;padding-bottom:5pt;font-size:8.6pt">Drug reactions and side effects, Post-operative bleeding, oozing, infection and/or bone infection</td></tr>
            <tr><td style="width:12pt;font-weight:bold;vertical-align:top;padding-right:4pt;padding-bottom:5pt">2.</td><td style="text-align:justify;line-height:1.65;padding-bottom:5pt;font-size:8.6pt">Bruising and/or swelling, restricted mouth opening for several days or weeks</td></tr>
            <tr><td style="width:12pt;font-weight:bold;vertical-align:top;padding-right:4pt;padding-bottom:5pt">3.</td><td style="text-align:justify;line-height:1.65;padding-bottom:5pt;font-size:8.6pt">Possible involvement of the nerves of the lower jaw</td></tr>
            <tr><td style="width:12pt;font-weight:bold;vertical-align:top;padding-right:4pt;padding-bottom:5pt">4.</td><td style="text-align:justify;line-height:1.65;padding-bottom:5pt;font-size:8.6pt">Temporary facial Nerve Paralysis</td></tr>
            <tr><td style="width:12pt;font-weight:bold;vertical-align:top;padding-right:4pt;padding-bottom:5pt">5.</td><td style="text-align:justify;line-height:1.65;padding-bottom:5pt;font-size:8.6pt">The numbness will last for 3 hours. Kindly avoid biting the injected area</td></tr>
            <tr><td style="width:12pt;font-weight:bold;vertical-align:top;padding-right:4pt;padding-bottom:5pt">6.</td><td style="text-align:justify;line-height:1.65;padding-bottom:5pt;font-size:8.6pt">Further treatment cannot be proceeded without Anaesthesia</td></tr>
        </tbody></table>
        
        <table class="sr">
            <tr>
                <td style="vertical-align:bottom; width:50%">Signature: <span class="uP" style="min-width:150pt">'.$patSigHtml.'</span><br><span style="font-size:8pt;color:#444;white-space:nowrap">(Patient/Parent/Guardian)</span></td>
                <td></td>
            </tr>
            <tr><td colspan="2" style="height:10pt;"></td></tr>
            <tr>
                <td style="vertical-align:bottom;">Date: <span class="uP" style="min-width:110pt">'.htmlspecialchars($dateSigned).'</span></td>
                <td style="vertical-align:bottom;">Relationship (if Minor): <span class="uP" style="min-width:110pt;color:transparent;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></td>
            </tr>
        </table>
        
        <p style="font-size:7.5pt;color:#555;margin-top:13pt;font-style:italic">Note: Patient to sign personally.</p>
        </body></html>';

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($options);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $output = $dompdf->output();
        
        $pdfDirPath = __DIR__ . '/uploads/consent_forms/';
        if (!is_dir($pdfDirPath)) {
            mkdir($pdfDirPath, 0777, true);
        }
        $pdfFileName = 'anesthesia_consent_' . $treatmentId . '_' . time() . '.pdf';
        $pdfOutputUrl = 'uploads/consent_forms/' . $pdfFileName;
        
        file_put_contents($pdfDirPath . $pdfFileName, $output);
        return [
            'success' => true,
            'pdf_url' => 'http://172.27.207.54/dentconsent/backend/api/' . $pdfOutputUrl,
            'local_path' => $pdfOutputUrl
        ];
    } catch (Exception $e) {
        error_log("Anesthesia PDF Gen Error: " . $e->getMessage());
        return ['success' => false, 'error' => $e->getMessage()];
    }
}
?>
