<?php
// test_registration_fix.php - Test the fixed registration system
require_once 'php/config.php';
require_once 'php/session.php';

echo "<h2>Registration System Test</h2>";

// Test 1: Check if session is working
echo "<h3>Test 1: Session Check</h3>";
if (session_status() === PHP_SESSION_ACTIVE) {
    echo "✅ Session is active<br>";
} else {
    echo "❌ Session is not active<br>";
}

// Test 2: Check database connection
echo "<h3>Test 2: Database Connection</h3>";
if ($conn) {
    echo "✅ Database connection successful<br>";
    
    // Check if users table exists
    $result = $conn->query("SHOW TABLES LIKE 'users'");
    if ($result->num_rows > 0) {
        echo "✅ Users table exists<br>";
        
        // Check table structure
        $result = $conn->query("DESCRIBE users");
        echo "<h4>Users Table Structure:</h4>";
        echo "<table border='1'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['Field'] . "</td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['Null'] . "</td>";
            echo "<td>" . $row['Key'] . "</td>";
            echo "<td>" . $row['Default'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "❌ Users table does not exist<br>";
    }
} else {
    echo "❌ Database connection failed<br>";
}

// Test 3: Check OTP logs table
echo "<h3>Test 3: OTP Logs Table</h3>";
$result = $conn->query("SHOW TABLES LIKE 'otp_log'");
if ($result->num_rows > 0) {
    echo "✅ OTP logs table exists<br>";
} else {
    echo "❌ OTP logs table does not exist<br>";
}

// Test 4: Check email configuration
echo "<h3>Test 4: Email Configuration</h3>";
if (defined('SMTP_HOST') && defined('SMTP_USERNAME')) {
    echo "✅ Email configuration constants defined<br>";
    echo "SMTP Host: " . SMTP_HOST . "<br>";
    echo "SMTP Username: " . SMTP_USERNAME . "<br>";
} else {
    echo "❌ Email configuration constants not defined<br>";
}

// Test 5: Check if OTP Manager exists
echo "<h3>Test 5: OTP Manager</h3>";
if (file_exists('php/otp_manager.php')) {
    echo "✅ OTP Manager file exists<br>";
    require_once 'php/otp_manager.php';
    $otpManager = new OTPManager($conn);
    echo "✅ OTP Manager class instantiated successfully<br>";
} else {
    echo "❌ OTP Manager file does not exist<br>";
}

echo "<h3>Test Complete!</h3>";
echo "<p>If all tests show ✅, the registration system should work properly.</p>";
echo "<p><a href='register.html'>Test Registration Form</a></p>";
echo "<p><a href='index.html'>Test Main Page Registration</a></p>";
?> 