<?php
// config/mail_config.php

require_once __DIR__ . '/../phpmailer/src/Exception.php';
require_once __DIR__ . '/../phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendEmail($to, $subject, $body) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->Port = 587;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->SMTPAuth = true;
        $mail->Username = 'barangay.stonino.paranaque@gmail.com';
        $mail->Password = 'vgsf qtuo gawx qyup';
        $mail->setFrom('barangay.stonino.paranaque@gmail.com', 'Barangay Sto. Niño');
        $mail->addAddress($to);

        // ✅ FIX: Set character encoding to UTF-8
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email failed: " . $mail->ErrorInfo);
        return false;
    }
}
?>