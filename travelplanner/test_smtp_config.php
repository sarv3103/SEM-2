<?php
/**
 * SMTP Configuration Test
 * This file helps you test and configure your Gmail SMTP settings
 */

require_once __DIR__ . '/vendor/phpmailer/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/vendor/phpmailer/phpmailer/src/SMTP.php';
require_once __DIR__ . '/vendor/phpmailer/phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Configuration - UPDATE THESE VALUES
$mailHost = 'smtp.gmail.com';
$mailUsername = 'sarveshtravelplanner@gmail.com';
$mailPassword = 'YOUR_APP_PASSWORD'; // Replace with your Gmail App Password
$mailFrom = 'sarveshtravelplanner@gmail.com';
$mailFromName = 'TravelPlanner';
$testEmail = 'your-test-email@gmail.com'; // Replace with your test email

echo "<h2>SMTP Configuration Test</h2>";

// Check if credentials are configured
if ($mailPassword === 'YOUR_APP_PASSWORD') {
    echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
    echo "<h3>⚠️ Configuration Required</h3>";
    echo "<p><strong>You need to set up Gmail App Password:</strong></p>";
    echo "<ol>";
    echo "<li>Go to your Google Account settings: <a href='https://myaccount.google.com/' target='_blank'>https://myaccount.google.com/</a></li>";
    echo "<li>Navigate to <strong>Security</strong> → <strong>2-Step Verification</strong></li>";
    echo "<li>Scroll down and click <strong>App passwords</strong></li>";
    echo "<li>Select 'Mail' as the app and 'Other' as the device</li>";
    echo "<li>Enter 'TravelPlanner' as the name</li>";
    echo "<li>Click <strong>Generate</strong></li>";
    echo "<li>Copy the 16-character password (e.g., 'abcd efgh ijkl mnop')</li>";
    echo "<li>Update the <code>\$mailPassword</code> variable in this file</li>";
    echo "<li>Also update <code>php/admin_reply_message.php</code> with the same password</li>";
    echo "</ol>";
    echo "<p><strong>Important:</strong> Use the App Password, NOT your regular Gmail password!</p>";
    echo "</div>";
    exit;
}

if ($testEmail === 'your-test-email@gmail.com') {
    echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
    echo "<h3>⚠️ Test Email Required</h3>";
    echo "<p>Please update the <code>\$testEmail</code> variable with your test email address.</p>";
    echo "</div>";
    exit;
}

echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
echo "<h3>✅ Configuration Found</h3>";
echo "<p><strong>SMTP Host:</strong> $mailHost</p>";
echo "<p><strong>Username:</strong> $mailUsername</p>";
echo "<p><strong>Test Email:</strong> $testEmail</p>";
echo "</div>";

// Test SMTP connection
echo "<h3>Testing SMTP Connection...</h3>";

$mail = new PHPMailer(true);

try {
    // Enable debug output
    $mail->SMTPDebug = 2; // Show detailed debug output
    $mail->Debugoutput = function($str, $level) {
        echo "<div style='background: #f8f9fa; border: 1px solid #dee2e6; padding: 10px; margin: 5px 0; border-radius: 3px; font-family: monospace; font-size: 12px;'>";
        echo htmlspecialchars($str);
        echo "</div>";
    };
    
    $mail->isSMTP();
    $mail->Host = $mailHost;
    $mail->SMTPAuth = true;
    $mail->Username = $mailUsername;
    $mail->Password = $mailPassword;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    
    // Additional SMTP settings for better compatibility
    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );
    
    $mail->setFrom($mailFrom, $mailFromName);
    $mail->addAddress($testEmail);
    $mail->isHTML(true);
    $mail->Subject = 'TravelPlanner SMTP Test - ' . date('Y-m-d H:i:s');
    $mail->Body = "
        <html>
        <body style='font-family: Arial, sans-serif;'>
            <h2 style='color: #0077cc;'>✅ SMTP Test Successful!</h2>
            <p>This email confirms that your Gmail SMTP configuration is working correctly.</p>
            <p><strong>Test Details:</strong></p>
            <ul>
                <li>SMTP Host: $mailHost</li>
                <li>Username: $mailUsername</li>
                <li>Test Time: " . date('Y-m-d H:i:s') . "</li>
            </ul>
            <p>You can now use the admin dashboard to reply to contact messages.</p>
            <hr>
            <small style='color: #888;'>This is a test email from TravelPlanner Admin System</small>
        </body>
        </html>
    ";
    $mail->AltBody = "SMTP Test Successful! Your Gmail SMTP configuration is working correctly.";
    
    $mail->send();
    
    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
    echo "<h3>✅ Test Email Sent Successfully!</h3>";
    echo "<p>Check your email at <strong>$testEmail</strong> for the test message.</p>";
    echo "<p>Your SMTP configuration is working correctly. You can now use the admin dashboard to reply to contact messages.</p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
    echo "<h3>❌ SMTP Test Failed</h3>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($mail->ErrorInfo) . "</p>";
    echo "<p><strong>Common Solutions:</strong></p>";
    echo "<ul>";
    echo "<li>Make sure you're using a Gmail App Password, not your regular password</li>";
    echo "<li>Ensure 2-Step Verification is enabled on your Google Account</li>";
    echo "<li>Check that the App Password was generated for 'Mail' application</li>";
    echo "<li>Verify the email address and password are correct</li>";
    echo "<li>Make sure your Gmail account allows 'less secure app access' or use App Passwords</li>";
    echo "</ul>";
    echo "</div>";
}

echo "<hr>";
echo "<p><strong>Next Steps:</strong></p>";
echo "<ol>";
echo "<li>If the test was successful, update <code>php/admin_reply_message.php</code> with the same password</li>";
echo "<li>Delete this test file for security</li>";
echo "<li>Use the admin dashboard to reply to contact messages</li>";
echo "</ol>";
?> 