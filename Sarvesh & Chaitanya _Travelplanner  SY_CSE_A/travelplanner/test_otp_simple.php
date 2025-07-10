<?php
// Simple OTP test
require_once 'php/config.php';
require_once 'php/email_config.php';

echo "<h2>OTP System Test</h2>";

// Test 1: Check if EmailService class exists
if (class_exists('EmailService')) {
    echo "✅ EmailService class found<br>";
    
    // Test 2: Try to create EmailService instance
    try {
        $emailService = new EmailService();
        echo "✅ EmailService instance created successfully<br>";
        
        // Test 3: Try to send a test OTP
        $testEmail = 'test@example.com'; // Replace with your email for testing
        $testOTP = '123456';
        
        echo "Attempting to send OTP to: $testEmail<br>";
        $result = $emailService->sendOTP($testEmail, $testOTP, 'test');
        
        if ($result) {
            echo "✅ OTP sent successfully!<br>";
        } else {
            echo "❌ Failed to send OTP<br>";
        }
        
    } catch (Exception $e) {
        echo "❌ Error creating EmailService: " . $e->getMessage() . "<br>";
    }
} else {
    echo "❌ EmailService class not found<br>";
}

// Test 4: Check database connection
if ($conn->ping()) {
    echo "✅ Database connection working<br>";
} else {
    echo "❌ Database connection failed<br>";
}

// Test 5: Check if otp_logs table exists
$result = $conn->query("SHOW TABLES LIKE 'otp_logs'");
if ($result && $result->num_rows > 0) {
    echo "✅ otp_logs table exists<br>";
} else {
    echo "❌ otp_logs table not found<br>";
}

echo "<br><strong>If OTP is failing, check:</strong><br>";
echo "1. Gmail credentials in php/email_config.php<br>";
echo "2. Gmail 2-factor authentication is enabled<br>";
echo "3. App password is generated and correct<br>";
echo "4. SMTP settings are correct<br>";
?> 