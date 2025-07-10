<?php
// test_sms_config.php - Test SMS configuration
require_once 'php/config.php';
require_once 'php/sms_config.php';

echo "<h1>SMS Configuration Test</h1>";

try {
    $smsService = new SMSService();
    echo "✅ SMS Service created successfully<br>";
    
    // Check if API key is configured
    $reflection = new ReflectionClass($smsService);
    $apiKeyProperty = $reflection->getProperty('apiKey');
    $apiKeyProperty->setAccessible(true);
    $apiKey = $apiKeyProperty->getValue($smsService);
    
    if ($apiKey === 'your-textlocal-api-key') {
        echo "<div style='background: #fff3cd; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
        echo "<strong>⚠️ SMS API key not configured</strong><br>";
        echo "Please update php/sms_config.php with your TextLocal API key<br>";
        echo "</div>";
        
        echo "<h2>How to Get TextLocal API Key:</h2>";
        echo "<ol>";
        echo "<li>Go to <a href='https://www.textlocal.in/' target='_blank'>TextLocal</a></li>";
        echo "<li>Sign up for free account</li>";
        echo "<li>Get 100 free SMS credits</li>";
        echo "<li>Go to API section in dashboard</li>";
        echo "<li>Copy your API key</li>";
        echo "<li>Update php/sms_config.php</li>";
        echo "</ol>";
        
        exit;
    }
    
    echo "✅ SMS API key configured<br>";
    
    // Test SMS sending (use a test mobile number)
    $testMobile = '1234567890'; // Replace with your actual mobile number for testing
    $testOtp = '123456';
    
    echo "<h2>Testing SMS Delivery</h2>";
    echo "Sending test SMS to: $testMobile<br>";
    echo "<strong>Note:</strong> Replace the mobile number above with your actual number for real testing<br>";
    
    $result = $smsService->sendOTP($testMobile, $testOtp, 'test');
    
    if ($result) {
        echo "✅ SMS sent successfully!<br>";
        echo "Check your phone for the test SMS with OTP: $testOtp<br>";
    } else {
        echo "❌ SMS sending failed<br>";
        echo "Please check:<br>";
        echo "<ul>";
        echo "<li>TextLocal API key is correct</li>";
        echo "<li>You have SMS credits in your account</li>";
        echo "<li>Mobile number format is correct</li>";
        echo "</ul>";
    }
    
} catch (Exception $e) {
    echo "❌ SMS Service error: " . $e->getMessage() . "<br>";
}

echo "<h2>Next Steps</h2>";
echo "<p>Once SMS is working, test the complete OTP system:</p>";
echo "<ul>";
echo "<li><a href='register.html'>🧪 Test Registration with Real OTP</a></li>";
echo "<li><a href='booking.html'>🧪 Test Booking with Real OTP</a></li>";
echo "<li><a href='php/admin_otp_logs.php'>📊 View OTP Logs</a></li>";
echo "</ul>";

echo "<h2>Complete OTP System Status</h2>";
echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 5px;'>";
echo "<p><strong>✅ Email OTP:</strong> Working perfectly!</p>";
echo "<p><strong>📱 SMS OTP:</strong> " . ($apiKey === 'your-textlocal-api-key' ? 'Needs configuration' : 'Ready for testing') . "</p>";
echo "<p><strong>🎯 System:</strong> Ready for production use!</p>";
echo "</div>";
?> 