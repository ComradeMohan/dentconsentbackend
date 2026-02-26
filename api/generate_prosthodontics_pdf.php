<?php
require_once __DIR__ . '/../db_connect.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

function generateProsthodonticsPdf($pdo, $treatmentId) {
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
        
        function getBase64ImgProstho($fullPath) {
            if ($fullPath && file_exists($fullPath) && is_file($fullPath)) {
                $type = pathinfo($fullPath, PATHINFO_EXTENSION);
                $d = file_get_contents($fullPath);
                return 'data:image/' . $type . ';base64,' . base64_encode($d);
            }
            return '';
        }

        $patImgSrc = getBase64ImgProstho($data['patient_signature_path'] ? realpath(__DIR__ . '/' . $data['patient_signature_path']) : '');

        $patSigHtml = $patImgSrc ? '<img src="'.$patImgSrc.'" style="max-height: 75px; object-fit: contain; margin-top: -10px;">' : '<span style="color:#transparent; font-weight:normal;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>';

        $logoLeft = getBase64ImgProstho(realpath(__DIR__ . '/../tiangle.png'));
        $logoRight = getBase64ImgProstho(realpath(__DIR__ . '/../circulear.png'));

        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8">
        <title>Prosthodontics Consent</title>
        <style>
        @page { size: A4; margin: 30pt 40pt; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: "Times New Roman", Georgia, serif; font-size: 10pt; color: #000; line-height: 1.5; border: 1.5pt solid #000; padding: 30pt; }
        .logos { text-align: center; margin-bottom: 10pt; }
        .logos img { height: 72pt; width: auto; display: inline-block; margin: 0 17pt; }
        .title { text-align: center; font-size: 9.8pt; line-height: 1.6; margin-bottom: 13pt; text-decoration: underline; text-underline-offset: 2pt; font-weight: bold; }
        .fields { width: 100%; margin-bottom: 12pt; border-collapse: collapse; }
        .fields td { padding-bottom: 5pt; font-size: 9.2pt; }
        .uP { display: inline-block; border-bottom: 1pt solid #333; color: #0000cc; font-weight: 500; padding: 0 3pt; font-size: 9.2pt; vertical-align: bottom; }
        .body { font-size: 8.7pt; line-height: 1.68; text-align: justify; margin-bottom: 9pt; }
        .aware { font-size: 9pt; font-weight: bold; margin-bottom: 6pt; }
        table.risks { width: 100%; border-collapse: collapse; font-size: 8.5pt; }
        .sr { width: 100%; margin-top: 18pt; border-collapse: collapse; }
        .guardian { font-size:8pt; color:#222; margin:3pt 0 12pt 0; }
        .rel-row { margin-bottom:3pt; font-size:9pt; }
        .rel-note { font-size:8pt; color:#222; }
        </style></head><body>
        <div class="logos">
            <img src="'.$logoLeft.'" alt="">
            <img src="'.$logoRight.'" alt="">
        </div>
        <div class="title">
            SAVEETHA DENTAL COLLEGE AND HOSPITAL<br>
            CONSENT FOR DENTAL PROSTHODONTICS
        </div>
        <table class="fields">
            <tr>
                <td style="width:50%">Patient Name/ <span class="uP" style="min-width:140pt">'.htmlspecialchars($data["patient_name"] ?? "&nbsp;").'</span></td>
                <td style="width:50%">Date/ <span class="uP" style="min-width:100pt">'.htmlspecialchars($dateSigned).'</span></td>
            </tr>
            <tr>
                <td>Age/ <span class="uP" style="min-width:140pt">'.htmlspecialchars($age).'</span></td>
                <td>Gender/ <span class="uP" style="min-width:100pt">'.htmlspecialchars($gender).'</span></td>
            </tr>
        </table>
        
        <div class="body">An alternative to the following has been explained and I authorize the Dentist to do the following
        treatment and any others necessary for the reasons in. I understand I may need further treatment by a
        specialist or even hospitalization if complications arise during or following treatment, the cost of
        which is my responsibility. I have been given satisfactory answers to all of my questions, and I wish
        to proceed with the Recommended Treatment.</div>
        
        <div class="aware">These potential risks and complications, include, but are not limited to, the following:</div>
        
        <table class="risks"><tbody>
            <tr><td style="width:13pt;font-weight:bold;vertical-align:top;padding-right:4pt;padding-bottom:5pt;white-space:nowrap">1.</td><td style="text-align:justify;line-height:1.65;padding-bottom:5pt;">Potential for root canal treatment after tooth preparation, need for periodontal treatment, home care responsibilities.</td></tr>
            <tr><td style="width:13pt;font-weight:bold;vertical-align:top;padding-right:4pt;padding-bottom:5pt;white-space:nowrap">2.</td><td style="text-align:justify;line-height:1.65;padding-bottom:5pt;">Failure or Breakage of damaged tooth, Crown and Bridge, Tooth recurrent decay.</td></tr>
            <tr><td style="width:13pt;font-weight:bold;vertical-align:top;padding-right:4pt;padding-bottom:5pt;white-space:nowrap">3.</td><td style="text-align:justify;line-height:1.65;padding-bottom:5pt;">Changes in Aesthethics and Face, Speech.</td></tr>
            <tr><td style="width:13pt;font-weight:bold;vertical-align:top;padding-right:4pt;padding-bottom:5pt;white-space:nowrap">4.</td><td style="text-align:justify;line-height:1.65;padding-bottom:5pt;">If treatment is not done it may cause upper teeth to come down and other teeth to move from place.</td></tr>
        </tbody></table>
        
        <table class="sr">
            <tr>
                <td style="width:55%; vertical-align:bottom;">Signature/ <span class="uP" style="min-width:160pt">'.$patSigHtml.'</span></td>
                <td style="width:45%; vertical-align:bottom;">Date/ <span class="uP" style="min-width:110pt">'.htmlspecialchars($dateSigned).'</span></td>
            </tr>
        </table>
        
        <div class="guardian">(Patient/Parent/Guardian)</div>
        <div class="rel-row">
            Relationship (if Patient is a Minor): <span class="uP" style="min-width:210pt">&nbsp;</span>
        </div>
        <div class="rel-note">&#2909;&#2993;&#2997;&#3009; (&#2984;&#3019;&#2991;&#3006;&#2995;&#3007; &#2990;&#3016;&#2985;&#2992;&#3006;&#2965; &#2951;&#2992;&#3009;&#2984;&#3021;&#2980;&#3006;&#2994;&#3021;)</div>
        
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
        $pdfFileName = 'prosthodontics_consent_' . $treatmentId . '_' . time() . '.pdf';
        $pdfOutputUrl = 'uploads/consent_forms/' . $pdfFileName;
        
        file_put_contents($pdfDirPath . $pdfFileName, $output);
        return [
            'success' => true,
            'pdf_url' => 'http://172.27.207.54/dentconsent/backend/api/' . $pdfOutputUrl,
            'local_path' => $pdfOutputUrl
        ];
    } catch (Exception $e) {
        error_log("Prosthodontics PDF Gen Error: " . $e->getMessage());
        return ['success' => false, 'error' => $e->getMessage()];
    }
}
?>
