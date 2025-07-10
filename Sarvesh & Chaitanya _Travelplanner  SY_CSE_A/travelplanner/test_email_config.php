<?php
// test_email_config.php - Test email configuration
require_once 'php/email_config.php';

echo "<h1>Email Configuration Test</h1>";

try {
    $emailService = new EmailService();
    echo "✅ Email Service created successfully<br>";
    
    // Test email sending to your own email
    $testEmail = 'sarveshkulkarni3103@gmail.com'; // Your email
    $testOtp = '123456';
    
    echo "<h2>Testing Email Delivery</h2>";
    echo "Sending test email to: $testEmail<br>";
    
    $result = $emailService->sendOTP($testEmail, $testOtp, 'test');
    
    if ($result) {
        echo "✅ Email sent successfully!<br>";
        echo "Check your inbox for the test email with OTP: $testOtp<br>";
    } else {
        echo "❌ Email sending failed<br>";
        echo "Please check:<br>";
        echo "<ul>";
        echo "<li>Gmail app password is correct (16 characters)</li>";
        echo "<li>2-Step Verification is enabled</li>";
        echo "<li>Internet connection is working</li>";
        echo "</ul>";
    }
    
} catch (Exception $e) {
    echo "❌ Email Service error: " . $e->getMessage() . "<br>";
    echo "<h3>Troubleshooting:</h3>";
    echo "<ol>";
    echo "<li>Go to <a href='https://myaccount.google.com/' target='_blank'>Google Account Settings</a></li>";
    echo "<li>Enable 2-Step Verification if not already enabled</li>";
    echo "<li>Go to 'App passwords' and create a new one for 'TravelPlanner'</li>";
    echo "<li>Copy the 16-character password and update php/email_config.php</li>";
    echo "</ol>";
}

echo "<h2>Next Steps</h2>";
echo "<p>If email is working, proceed to configure SMS:</p>";
echo "<ol>";
echo "<li>Sign up at <a href='https://www.textlocal.in/' target='_blank'>TextLocal</a></li>";
echo "<li>Get your API key</li>";
echo "<li>Update <code>php/sms_config.php</code></li>";
echo "<li>Test registration with real OTP delivery</li>";
echo "</ol>";

echo "<p><a href='register.html'>🧪 Test Registration with Real OTP</a></p>";
echo "<p><a href='php/admin_otp_logs.php'>📊 View OTP Logs</a></p>";
?> 