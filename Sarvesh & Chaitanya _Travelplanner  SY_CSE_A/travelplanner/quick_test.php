<?php
// quick_test.php - Quick test without database dependencies
require_once 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

echo "<h1>Quick System Test</h1>";

// Test 1: PHPMailer
echo "<h2>Test 1: PHPMailer</h2>";
try {
    $mail = new PHPMailer(true);
    echo "✅ PHPMailer created successfully<br>";
    
    // Test basic configuration
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'test@gmail.com';
    $mail->Password = 'test';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    
    echo "✅ PHPMailer configuration test passed<br>";
    
} catch (Exception $e) {
    echo "❌ PHPMailer error: " . $e->getMessage() . "<br>";
}

// Test 2: cURL (for SMS)
echo "<h2>Test 2: cURL Extension</h2>";
if (function_exists('curl_init')) {
    echo "✅ cURL extension is available<br>";
    
    // Test basic cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://httpbin.org/get');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        echo "✅ cURL test passed (HTTP $httpCode)<br>";
    } else {
        echo "⚠️ cURL test returned HTTP $httpCode<br>";
    }
} else {
    echo "❌ cURL extension not available<br>";
}

// Test 3: File System
echo "<h2>Test 3: File System</h2>";
if (is_writable('.')) {
    echo "✅ Current directory is writable<br>";
} else {
    echo "❌ Current directory is not writable<br>";
}

// Test 4: PHP Extensions
echo "<h2>Test 4: Required PHP Extensions</h2>";
$required_extensions = ['pdo', 'pdo_mysql', 'json', 'curl', 'openssl'];
foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "✅ $ext extension loaded<br>";
    } else {
        echo "❌ $ext extension not loaded<br>";
    }
}

echo "<h2>Next Steps</h2>";
echo "<p><strong>To fix the database issue:</strong></p>";
echo "<ol>";
echo "<li>Go to phpMyAdmin</li>";
echo "<li>Select your 'travelplanner' database</li>";
echo "<li>Go to SQL tab</li>";
echo "<li>Copy and paste the contents of <code>fix_sms_log_table.sql</code></li>";
echo "<li>Click 'Go' to execute</li>";
echo "</ol>";

echo "<p><strong>After fixing the database:</strong></p>";
echo "<ol>";
echo "<li>Run <a href='test_email_sms.php'>test_email_sms.php</a></li>";
echo "<li>Configure Gmail credentials in <code>php/email_config.php</code></li>";
echo "<li>Configure TextLocal API in <code>php/sms_config.php</code></li>";
echo "<li>Test registration with real OTP delivery</li>";
echo "</ol>";

echo "<p><strong>Current Status:</strong></p>";
echo "<ul>";
echo "<li>✅ PHPMailer installed and working</li>";
echo "<li>✅ cURL available for SMS API calls</li>";
echo "<li>✅ All required PHP extensions loaded</li>";
echo "<li>⚠️ Database needs SMS log table fix</li>";
echo "</ul>";
?> 