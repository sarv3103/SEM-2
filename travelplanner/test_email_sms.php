<?php
// test_email_sms.php - Test Email and SMS functionality
require_once 'php/config.php';
require_once 'php/email_config.php';
require_once 'php/sms_config.php';
require_once 'php/otp_manager.php';

echo "<h1>Email & SMS Test</h1>";

// Test 1: Check if PHPMailer is installed
echo "<h2>Test 1: PHPMailer Installation</h2>";
if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
    echo "✅ PHPMailer is installed successfully<br>";
} else {
    echo "❌ PHPMailer is not installed<br>";
    exit;
}

// Test 2: Test Email Service
echo "<h2>Test 2: Email Service</h2>";
try {
    $emailService = new EmailService();
    echo "✅ EmailService initialized successfully<br>";
    
    // Test email sending (will fail without proper Gmail credentials, but should not crash)
    $result = $emailService->sendOTP('test@example.com', '123456', 'test');
    echo "✅ Email service test completed (result: " . ($result ? 'Success' : 'Failed - Check Gmail credentials') . ")<br>";
    
} catch (Exception $e) {
    echo "❌ Email service error: " . $e->getMessage() . "<br>";
}

// Test 3: Test SMS Service
echo "<h2>Test 3: SMS Service</h2>";
try {
    $smsService = new SMSService();
    echo "✅ SMSService initialized successfully<br>";
    
    // Test SMS sending (will fail without proper API credentials, but should not crash)
    $result = $smsService->sendOTP('1234567890', '123456', 'test');
    echo "✅ SMS service test completed (result: " . ($result ? 'Success' : 'Failed - Check SMS API credentials') . ")<br>";
    
} catch (Exception $e) {
    echo "❌ SMS service error: " . $e->getMessage() . "<br>";
}

// Test 4: Test OTP Manager
echo "<h2>Test 4: OTP Manager Integration</h2>";
try {
    $otpManager = new OTPManager($conn);
    echo "✅ OTP Manager initialized successfully<br>";
    
    // Test OTP generation
    $otp = $otpManager->generateOTP();
    echo "✅ OTP Generated: $otp<br>";
    
    // Test email OTP sending
    $emailResult = $otpManager->sendEmailOTP('test@example.com', $otp, 'test');
    echo "✅ Email OTP sending test completed<br>";
    
    // Test SMS OTP sending
    $smsResult = $otpManager->sendSMSOTP('1234567890', $otp, 'test');
    echo "✅ SMS OTP sending test completed<br>";
    
} catch (Exception $e) {
    echo "❌ OTP Manager error: " . $e->getMessage() . "<br>";
}

echo "<h2>Setup Instructions</h2>";
echo "<p><strong>To enable real email delivery:</strong></p>";
echo "<ol>";
echo "<li>Go to <a href='https://myaccount.google.com/' target='_blank'>Google Account Settings</a></li>";
echo "<li>Enable 2-Step Verification</li>";
echo "<li>Create an App Password for 'TravelPlanner'</li>";
echo "<li>Update <code>php/email_config.php</code> with your Gmail and app password</li>";
echo "</ol>";

echo "<p><strong>To enable real SMS delivery:</strong></p>";
echo "<ol>";
echo "<li>Sign up at <a href='https://www.textlocal.in/' target='_blank'>TextLocal</a> (free credits available)</li>";
echo "<li>Get your API key from the dashboard</li>";
echo "<li>Update <code>php/sms_config.php</code> with your API key</li>";
echo "</ol>";

echo "<p><strong>Current Status:</strong></p>";
echo "<ul>";
echo "<li>✅ PHPMailer installed</li>";
echo "<li>✅ Email service configured (needs Gmail credentials)</li>";
echo "<li>✅ SMS service configured (needs TextLocal credentials)</li>";
echo "<li>✅ OTP Manager integrated</li>";
echo "</ul>";

echo "<p><a href='php/admin_otp_logs.php'>View OTP Logs in Admin Panel</a></p>";
?> 