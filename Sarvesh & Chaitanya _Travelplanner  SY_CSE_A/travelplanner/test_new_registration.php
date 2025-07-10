<?php
// test_new_registration.php - Test the new registration system
require_once 'php/config.php';
require_once 'php/otp_manager.php';

echo "<h2>🧪 Testing New Registration System</h2>";

// Test 1: Check database structure
echo "<h3>1. Database Structure Check:</h3>";
$result = $conn->query("DESCRIBE users");
if ($result) {
    echo "✅ Users table exists<br>";
    $columns = [];
    while ($row = $result->fetch_assoc()) {
        $columns[] = $row['Field'];
    }
    echo "Columns: " . implode(', ', $columns) . "<br>";
} else {
    echo "❌ Users table not found<br>";
}

// Test 2: Check OTP log table
echo "<h3>2. OTP Log Table Check:</h3>";
$result = $conn->query("DESCRIBE otp_log");
if ($result) {
    echo "✅ OTP log table exists<br>";
} else {
    echo "❌ OTP log table not found<br>";
}

// Test 3: Test OTP generation
echo "<h3>3. OTP Generation Test:</h3>";
$otpManager = new OTPManager($conn);
$testOTP = $otpManager->generateOTP();
echo "Generated OTP: $testOTP<br>";
echo "OTP Length: " . strlen($testOTP) . "<br>";

// Test 4: Test user creation with pending status
echo "<h3>4. Test User Creation (Pending Status):</h3>";
$testEmail = 'test_' . time() . '@example.com';
$testUsername = 'testuser_' . time();

$stmt = $conn->prepare("INSERT INTO users (first_name, last_name, username, email, password, email_verified, status) VALUES (?, ?, ?, ?, ?, 0, 'pending')");
$hashedPassword = password_hash('testpass123', PASSWORD_DEFAULT);
$stmt->bind_param("sssss", 'Test', 'User', $testUsername, $testEmail, $hashedPassword);

if ($stmt->execute()) {
    $userId = $conn->insert_id;
    echo "✅ Test user created with ID: $userId<br>";
    echo "Status: pending<br>";
    echo "Email verified: 0<br>";
    
    // Test 5: Store OTP
    echo "<h3>5. OTP Storage Test:</h3>";
    $otpStored = $otpManager->storeOTP($userId, $testEmail, null, $testOTP, null);
    if ($otpStored) {
        echo "✅ OTP stored successfully<br>";
    } else {
        echo "❌ Failed to store OTP<br>";
    }
    
    // Test 6: Verify OTP
    echo "<h3>6. OTP Verification Test:</h3>";
    $verified = $otpManager->verifyOTP($userId, $testOTP, null);
    if ($verified) {
        echo "✅ OTP verification successful<br>";
        
        // Test 7: Activate user
        echo "<h3>7. User Activation Test:</h3>";
        $stmt = $conn->prepare("UPDATE users SET email_verified = 1, status = 'active' WHERE id = ?");
        $stmt->bind_param("i", $userId);
        
        if ($stmt->execute()) {
            echo "✅ User activated successfully<br>";
        } else {
            echo "❌ Failed to activate user<br>";
        }
    } else {
        echo "❌ OTP verification failed<br>";
    }
    
    // Clean up test user
    $conn->query("DELETE FROM users WHERE id = $userId");
    $conn->query("DELETE FROM otp_log WHERE user_id = $userId");
    echo "<br>🧹 Test user cleaned up<br>";
    
} else {
    echo "❌ Failed to create test user<br>";
}

echo "<h3>🎯 Test Summary:</h3>";
echo "The new registration system should now work correctly with:<br>";
echo "• User creation with 'pending' status<br>";
echo "• OTP storage in database<br>";
echo "• OTP verification from database<br>";
echo "• User activation after successful verification<br>";

echo "<h3>📝 Next Steps:</h3>";
echo "1. <a href='register.html'>Test Registration Form</a><br>";
echo "2. <a href='index.html#auth'>Test Main Page Registration</a><br>";
echo "3. <a href='admin_dashboard.php'>Check Admin Dashboard</a><br>";
?> 