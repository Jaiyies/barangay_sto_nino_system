<?php
// test_mail.php
require_once 'config/mail_config.php';

$result = sendEmail(
    'barangay.stonino.paranaque@gmail.com',  // Send sa barangay email
    'Test Email - Barangay Sto. Niño',
    '<h1>✅ Test Successful!</h1>
     <p>Your email configuration is working.</p>
     <p>Date: ' . date('Y-m-d H:i:s') . '</p>'
);

if ($result) {
    echo "✅ Email sent successfully! Check your inbox.";
} else {
    echo "❌ Email failed. Check your configuration.";
}
?>