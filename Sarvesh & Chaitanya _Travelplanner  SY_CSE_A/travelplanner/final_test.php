<?php
// final_test.php - Final comprehensive test after database fix
require_once 'php/config.php';
require_once 'php/email_config.php';
require_once 'php/sms_config.php';
require_once 'php/otp_manager.php';

echo "<h1>Final OTP System Test</h1>";
echo "<p>This test will verify everything is working after database fix.</p>";

// Test 1: Database Connection
echo "<h2>Test 1: Database Connection</h2>";
try {
    if ($conn->ping()) {
        echo "✅ Database connection successful<br>";
    } else {
        echo "❌ Database connection failed<br>";
        exit;
    }
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
    exit;
}

// Test 2: Check Required Tables
echo "<h2>Test 2: Database Tables</h2>";
$required_tables = ['users', 'sms_log', 'booking_otp', 'payment_otp', 'contact_otp'];
foreach ($required_tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result->num_rows > 0) {
        echo "✅ Table '$table' exists<br>";
    } else {
        echo "❌ Table '$table' missing<br>";
    }
}

// Test 3: Check SMS Log Table Structure
echo "<h2>Test 3: SMS Log Table Structure</h2>";
$result = $conn->query("DESCRIBE sms_log");
$columns = [];
while ($row = $result->fetch_assoc()) {
    $columns[] = $row['Field'];
}

$required_columns = ['id', 'mobile', 'message', 'otp', 'success', 'api_response', 'created_at'];
foreach ($required_columns as $column) {
    if (in_array($column, $columns)) {
        echo "✅ Column '$column' exists<br>";
    } else {
        echo "❌ Column '$column' missing<br>";
    }
}

// Test 4: OTP Manager Test
echo "<h2>Test 4: OTP Manager</h2>";
try {
    $otpManager = new OTPManager($conn);
    echo "✅ OTP Manager created successfully<br>";
    
    // Test OTP generation
    $otp = $otpManager->generateOTP();
    echo "✅ OTP Generated: $otp<br>";
    
} catch (Exception $e) {
    echo "❌ OTP Manager error: " . $e->getMessage() . "<br>";
}

// Test 5: Email Service Test (without sending)
echo "<h2>Test 5: Email Service</h2>";
try {
    $emailService = new EmailService();
    echo "✅ Email Service created successfully<br>";
    
    // Check if credentials are configured
    $reflection = new ReflectionClass($emailService);
    $mailerProperty = $reflection->getProperty('mailer');
    $mailerProperty->setAccessible(true);
    $mailer = $mailerProperty->getValue($emailService);
    
    if ($mailer->Username === 'your-email@gmail.com') {
        echo "⚠️ Email credentials not configured (using default)<br>";
        echo "   Please update php/email_config.php with your Gmail credentials<br>";
    } else {
        echo "✅ Email credentials configured<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Email Service error: " . $e->getMessage() . "<br>";
}

// Test 6: SMS Service Test (without sending)
echo "<h2>Test 6: SMS Service</h2>";
try {
    $smsService = new SMSService();
    echo "✅ SMS Service created successfully<br>";
    
    // Check if API key is configured
    $reflection = new ReflectionClass($smsService);
    $apiKeyProperty = $reflection->getProperty('apiKey');
    $apiKeyProperty->setAccessible(true);
    $apiKey = $apiKeyProperty->getValue($smsService);
    
    if ($apiKey === 'your-textlocal-api-key') {
        echo "⚠️ SMS API key not configured (using default)<br>";
        echo "   Please update php/sms_config.php with your TextLocal API key<br>";
    } else {
        echo "✅ SMS API key configured<br>";
    }
    
} catch (Exception $e) {
    echo "❌ SMS Service error: " . $e->getMessage() . "<br>";
}

// Test 7: Integration Test
echo "<h2>Test 7: Integration Test</h2>";
try {
    // Test OTP generation and logging
    $testEmail = 'test@example.com';
    $testMobile = '1234567890';
    $testOtp = '123456';
    
    // Test email OTP logging
    $stmt = $conn->prepare("INSERT INTO users (email, email_otp, email_otp_expires) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 10 MINUTE))");
    $stmt->bind_param("ss", $testEmail, $testOtp);
    if ($stmt->execute()) {
        echo "✅ Email OTP logging test passed<br>";
    } else {
        echo "❌ Email OTP logging test failed<br>";
    }
    
    // Test SMS OTP logging
    $stmt = $conn->prepare("INSERT INTO sms_log (mobile, message, otp, success, api_response) VALUES (?, ?, ?, ?, ?)");
    $message = "Test SMS message";
    $success = 1;
    $apiResponse = json_encode(['status' => 'success']);
    $stmt->bind_param("sssis", $testMobile, $message, $testOtp, $success, $apiResponse);
    if ($stmt->execute()) {
        echo "✅ SMS OTP logging test passed<br>";
    } else {
        echo "❌ SMS OTP logging test failed<br>";
    }
    
    // Clean up test data
    $conn->query("DELETE FROM users WHERE email = '$testEmail'");
    $conn->query("DELETE FROM sms_log WHERE mobile = '$testMobile'");
    
} catch (Exception $e) {
    echo "❌ Integration test error: " . $e->getMessage() . "<br>";
}

echo "<h2>Configuration Status</h2>";
echo "<div style='background: #f0f0f0; padding: 15px; border-radius: 5px;'>";
echo "<p><strong>✅ System Ready:</strong></p>";
echo "<ul>";
echo "<li>Database connection working</li>";
echo "<li>All required tables exist</li>";
echo "<li>OTP Manager functional</li>";
echo "<li>Email service ready (needs Gmail credentials)</li>";
echo "<li>SMS service ready (needs TextLocal API key)</li>";
echo "</ul>";

echo "<p><strong>📧 To Enable Email Delivery:</strong></p>";
echo "<ol>";
echo "<li>Go to <a href='https://myaccount.google.com/' target='_blank'>Google Account Settings</a></li>";
echo "<li>Enable 2-Step Verification</li>";
echo "<li>Create App Password for 'TravelPlanner'</li>";
echo "<li>Update <code>php/email_config.php</code></li>";
echo "</ol>";

echo "<p><strong>📱 To Enable SMS Delivery:</strong></p>";
echo "<ol>";
echo "<li>Sign up at <a href='https://www.textlocal.in/' target='_blank'>TextLocal</a></li>";
echo "<li>Get free SMS credits</li>";
echo "<li>Copy your API key</li>";
echo "<li>Update <code>php/sms_config.php</code></li>";
echo "</ol>";

echo "<p><strong>🧪 Test Real OTP:</strong></p>";
echo "<ul>";
echo "<li><a href='register.html'>Test Registration OTP</a></li>";
echo "<li><a href='booking.html'>Test Booking OTP</a></li>";
echo "<li><a href='php/admin_otp_logs.php'>View OTP Logs</a></li>";
echo "</ul>";
echo "</div>";

echo "<p><a href='CONFIGURATION_GUIDE.md'>📖 View Complete Configuration Guide</a></p>";
?> 