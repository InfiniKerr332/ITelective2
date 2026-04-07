<?php
/**
 * WMSU ARL Hub: Mailer Configuration
 */
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

function sendEmail($toEmail, $toName, $subject, $bodyHTML, $bodyText) {
    $mail = new PHPMailer(true);
    try {
        // SMTP Server Settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        
        $smtpUsername = 'kerrzaragoza43@gmail.com'; // IMPORTANT: Replace with the actual email
        $mail->Username   = $smtpUsername;
        $mail->Password   = 'kaqmrowfbxmftxib';     // Your App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Recipients
        // Use the authenticated email as the Sender to prevent Gmail from blocking/modifying it
        $mail->setFrom($smtpUsername, 'WMSU ARL Hub');
        $mail->addAddress($toEmail, $toName);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $bodyHTML;
        $mail->AltBody = $bodyText;

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}
