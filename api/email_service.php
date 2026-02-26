<?php
// email_service.php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/smtp_config.php';

class EmailService {
    private $mailer;

    public function __construct() {
        $this->mailer = new PHPMailer(true);
        $this->setUpSMTP();
    }

    private function setUpSMTP() {
        // Server settings
        $this->mailer->isSMTP();
        $this->mailer->Host       = SMTP_HOST;
        $this->mailer->SMTPAuth   = true;
        $this->mailer->Username   = SMTP_USERNAME;
        $this->mailer->Password   = SMTP_PASSWORD;
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port       = SMTP_PORT;
        
        $this->mailer->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $this->mailer->isHTML(true);
    }

    /**
     * Sends an OTP to the user
     */
    public function sendOTP($toEmail, $otp, $purpose = 'Registration') {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($toEmail);

            $this->mailer->Subject = "Your DentConsent OTP Code: $otp";
            
            $htmlBody = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden;'>
                    <div style='background: linear-gradient(135deg, #1E3A8A, #3B82F6); padding: 20px; text-align: center; color: white;'>
                        <h2 style='margin: 0;'>DentConsent</h2>
                    </div>
                    <div style='padding: 30px; background-color: #ffffff;'>
                        <h3 style='color: #1E293B; margin-top: 0;'>Secure $purpose</h3>
                        <p style='color: #475569; font-size: 16px; line-height: 1.5;'>Please use the verification code below to complete your $purpose.</p>
                        <div style='background-color: #F8FAFC; border: 1px dashed #CBD5E1; padding: 15px; text-align: center; border-radius: 8px; margin: 25px 0;'>
                            <span style='font-size: 32px; font-weight: bold; letter-spacing: 5px; color: #1E3A8A;'>$otp</span>
                        </div>
                        <p style='color: #64748B; font-size: 14px;'>This code will expire in 10 minutes. Do not share this code with anyone.</p>
                    </div>
                    <div style='background-color: #F1F5F9; padding: 15px; text-align: center; color: #94A3B8; font-size: 12px;'>
                        &copy; " . date('Y') . " DentConsent. All rights reserved.
                    </div>
                </div>
            ";

            $this->mailer->Body = $htmlBody;
            $this->mailer->AltBody = "Your DentConsent OTP for $purpose is: $otp. It will expire in 10 minutes.";

            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            error_log("Email sending failed to $toEmail. Error: {$this->mailer->ErrorInfo}");
            return false;
        }
    }

    /**
     * Sends the generated Consent PDF to doctor and patient
     */
    public function sendConsentPDF($toEmail, $patientName, $doctorName, $treatmentName, $pdfFilePath) {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($toEmail);
            
            $this->mailer->Subject = "Consent Document: $treatmentName - $patientName";
            
            $htmlBody = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                    <div style='background: linear-gradient(135deg, #1E3A8A, #3B82F6); padding: 20px; text-align: center; color: white; border-radius: 8px 8px 0 0;'>
                        <h2 style='margin: 0;'>Signed Medical Consent</h2>
                    </div>
                    <div style='padding: 30px; background-color: #ffffff; border: 1px solid #e0e0e0; border-top: none; border-radius: 0 0 8px 8px;'>
                        <h3 style='color: #1E293B;'>Hello,</h3>
                        <p style='color: #475569; font-size: 16px;'>The medical consent document for <strong>$treatmentName</strong> has been digitally signed and finalized.</p>
                        <ul style='color: #475569; padding-left: 20px; margin-bottom: 25px;'>
                            <li><strong>Patient:</strong> $patientName</li>
                            <li><strong>Doctor:</strong> Dr. $doctorName</li>
                            <li><strong>Date:</strong> " . date('d M Y') . "</li>
                        </ul>
                        <p style='color: #475569; font-size: 16px;'>Please find the signed PDF document attached for your records.</p>
                    </div>
                </div>
            ";

            $this->mailer->Body = $htmlBody;
            $this->mailer->AltBody = "The signed consent document for $treatmentName (Patient: $patientName) is attached.";

            if (is_array($pdfFilePath)) {
                foreach ($pdfFilePath as $path) {
                    if ($path && file_exists($path)) {
                        $this->mailer->addAttachment($path, basename($path));
                    }
                }
            } else if ($pdfFilePath && file_exists($pdfFilePath)) {
                $this->mailer->addAttachment($pdfFilePath, "Consent_" . preg_replace('/[^A-Za-z0-9\-]/', '', $treatmentName) . ".pdf");
            }

            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            error_log("PDF Email failed to $toEmail. Error: {$this->mailer->ErrorInfo}");
            return false;
        }
    }

    /**
     * Sends a welcome email after successful registration
     */
    public function sendWelcomeEmail($toEmail, $name, $role) {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($toEmail);
            
            $roleDisplay = ucfirst($role);
            $this->mailer->Subject = "Welcome to DentConsent, $name!";
            
            $htmlBody = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                    <div style='background: linear-gradient(135deg, #1E3A8A, #3B82F6); padding: 30px; text-align: center; color: white; border-radius: 8px 8px 0 0;'>
                        <h1 style='margin: 0;'>Welcome!</h1>
                        <p style='margin-top: 10px; opacity: 0.9;'>We're glad to have you.</p>
                    </div>
                    <div style='padding: 30px; border: 1px solid #e0e0e0; border-top: none; border-radius: 0 0 8px 8px;'>
                        <h3 style='color: #1E293B;'>Hello $name,</h3>
                        <p style='color: #475569; font-size: 16px; line-height: 1.6;'>
                            Your account has been successfully created as a <strong>$roleDisplay</strong>. 
                            You can now log in to the DentConsent app to access your dashboard.
                        </p>
                    </div>
                </div>
            ";

            $this->mailer->Body = $htmlBody;
            $this->mailer->AltBody = "Welcome to DentConsent! Your $role account has been created successfully.";

            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            error_log("Welcome Email failed to $toEmail. Error: {$this->mailer->ErrorInfo}");
            return false;
        }
    }
}
?>
