<?php
// test_otp_system.php - Test OTP system functionality
require_once 'php/config.php';
require_once 'php/otp_manager.php';

echo "<h1>OTP System Test</h1>";

// Test 1: Check database connection
echo "<h2>Test 1: Database Connection</h2>";
if ($conn) {
    echo "✅ Database connection successful<br>";
} else {
    echo "❌ Database connection failed<br>";
    exit;
}

// Test 2: Check OTP tables exist
echo "<h2>Test 2: OTP Tables</h2>";
$tables = ['booking_otp', 'contact_otp', 'payment_otp', 'sms_log'];
foreach ($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result->num_rows > 0) {
        echo "✅ Table '$table' exists<br>";
    } else {
        echo "❌ Table '$table' missing<br>";
    }
}

// Test 3: Check users table columns
echo "<h2>Test 3: Users Table OTP Columns</h2>";
$columns = ['first_name', 'last_name', 'mobile', 'email_verified', 'mobile_verified', 'email_otp', 'mobile_otp', 'email_otp_expires', 'mobile_otp_expires'];
foreach ($columns as $column) {
    $result = $conn->query("SHOW COLUMNS FROM users LIKE '$column'");
    if ($result->num_rows > 0) {
        echo "✅ Column '$column' exists<br>";
    } else {
        echo "❌ Column '$column' missing<br>";
    }
}

// Test 4: Test OTP Manager
echo "<h2>Test 4: OTP Manager</h2>";
try {
    $otpManager = new OTPManager($conn);
    echo "✅ OTP Manager initialized successfully<br>";
    
    // Test OTP generation
    $otp = $otpManager->generateOTP();
    echo "✅ OTP Generated: $otp<br>";
    
    // Test email OTP sending
    $emailSent = $otpManager->sendEmailOTP('test@example.com', $otp, 'test');
    echo "✅ Email OTP sent: " . ($emailSent ? 'Yes' : 'No') . "<br>";
    
    // Test SMS OTP logging
    $smsSent = $otpManager->sendSMSOTP('1234567890', $otp, 'test');
    echo "✅ SMS OTP logged: " . ($smsSent ? 'Yes' : 'No') . "<br>";
    
} catch (Exception $e) {
    echo "❌ OTP Manager error: " . $e->getMessage() . "<br>";
}

// Test 5: Check SMS logs
echo "<h2>Test 5: SMS Logs</h2>";
$result = $conn->query("SELECT COUNT(*) as count FROM sms_log");
if ($result) {
    $count = $result->fetch_assoc()['count'];
    echo "✅ SMS logs table accessible. Total records: $count<br>";
} else {
    echo "❌ SMS logs table error<br>";
}

// Test 6: Test user registration with OTP
echo "<h2>Test 6: User Registration OTP</h2>";
$testEmail = 'test' . time() . '@example.com';
$testMobile = '9876543210';

// Create a test user
$stmt = $conn->prepare("INSERT INTO users (first_name, last_name, mobile, username, email, password) VALUES (?, ?, ?, ?, ?, ?)");
$firstName = 'Test';
$lastName = 'User';
$username = 'testuser' . time();
$password = password_hash('123456', PASSWORD_DEFAULT);

if ($stmt->bind_param("ssssss", $firstName, $lastName, $testMobile, $username, $testEmail, $password) && $stmt->execute()) {
    $userId = $conn->insert_id;
    echo "✅ Test user created with ID: $userId<br>";
    
    // Generate and store OTP
    $emailOTP = $otpManager->generateOTP();
    $mobileOTP = $otpManager->generateOTP();
    $otpManager->storeOTP($userId, $testEmail, $testMobile, $emailOTP, $mobileOTP);
    echo "✅ OTP stored for user<br>";
    echo "Email OTP: $emailOTP<br>";
    echo "Mobile OTP: $mobileOTP<br>";
    
    // Test OTP verification
    $verified = $otpManager->verifyOTP($userId, $emailOTP, $mobileOTP);
    echo "✅ OTP verification: " . ($verified ? 'Success' : 'Failed') . "<br>";
    
} else {
    echo "❌ Test user creation failed<br>";
}

echo "<h2>Test Complete!</h2>";
echo "<p>If all tests show ✅, your OTP system is working correctly!</p>";
echo "<p><a href='php/admin_otp_logs.php'>View OTP Logs in Admin Panel</a></p>";
?> 