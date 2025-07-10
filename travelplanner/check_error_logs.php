<?php
// check_error_logs.php - Check error logs for OTP debugging
echo "<h2>🔍 Error Log Check</h2>";

// Get the error log path
$errorLogPath = ini_get('error_log');
if (empty($errorLogPath)) {
    $errorLogPath = 'C:/xampp/php/logs/php_error_log';
}

echo "<h3>Error Log Path: $errorLogPath</h3>";

// Check if error log file exists
if (file_exists($errorLogPath)) {
    echo "<h3>✅ Error log file exists</h3>";
    
    // Read the last 50 lines of the error log
    $lines = file($errorLogPath);
    $recentLines = array_slice($lines, -50);
    
    echo "<h3>Recent Error Log Entries (Last 50 lines):</h3>";
    echo "<div style='background: #f5f5f5; padding: 10px; border-radius: 5px; font-family: monospace; font-size: 12px; max-height: 400px; overflow-y: auto;'>";
    
    foreach ($recentLines as $line) {
        if (strpos($line, 'Registration') !== false || strpos($line, 'Session') !== false || strpos($line, 'OTP') !== false) {
            echo htmlspecialchars($line) . "<br>";
        }
    }
    
    echo "</div>";
} else {
    echo "<h3>❌ Error log file not found at: $errorLogPath</h3>";
    
    // Try alternative locations
    $alternativePaths = [
        'C:/xampp/apache/logs/error.log',
        'C:/xampp/apache/logs/php_error.log',
        'C:/xampp/php/logs/php_error.log',
        'error_log'
    ];
    
    echo "<h3>Trying alternative paths:</h3>";
    foreach ($alternativePaths as $path) {
        if (file_exists($path)) {
            echo "✅ Found at: $path<br>";
            break;
        } else {
            echo "❌ Not found at: $path<br>";
        }
    }
}

// Test session functionality
echo "<h3>Session Test:</h3>";
session_start();
echo "Session ID: " . session_id() . "<br>";
echo "Session Status: " . session_status() . "<br>";

// Test OTP generation
echo "<h3>OTP Generation Test:</h3>";
require_once 'php/otp_manager.php';
require_once 'php/config.php';

$otpManager = new OTPManager($conn);
$testOTP = $otpManager->generateOTP();
echo "Generated OTP: $testOTP<br>";
echo "OTP Length: " . strlen($testOTP) . "<br>";

// Test session storage
echo "<h3>Session Storage Test:</h3>";
$_SESSION['test_data'] = [
    'otp' => $testOTP,
    'timestamp' => time()
];
echo "Test data stored in session<br>";
echo "Session data: <pre>" . print_r($_SESSION, true) . "</pre><br>";

echo "<h3>Test Links:</h3>";
echo "<a href='register.html'>Test Registration</a> | ";
echo "<a href='debug_otp_issue.php'>Debug OTP Issue</a> | ";
echo "<a href='admin_dashboard.php'>Admin Dashboard</a>";
?> 