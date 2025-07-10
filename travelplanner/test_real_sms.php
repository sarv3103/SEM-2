<?php
require_once 'php/config.php';
require_once 'php/sms_config.php';

echo "<h1>Real SMS Test with MSG91</h1>";

// Create SMS service
$smsService = new SMSService();

// Generate a test OTP
$test_otp = rand(100000, 999999);

echo "<h2>Configuration Status</h2>";
echo "<p><strong>Service:</strong> MSG91</p>";
echo "<p><strong>Auth Key:</strong> " . substr('457111ARwKRzZTS26856eb4aP1', 0, 10) . "...</p>";
echo "<p><strong>Sender ID:</strong> TRVLPL</p>";

echo "<h2>Test SMS</h2>";
echo "<p><strong>Generated OTP:</strong> $test_otp</p>";

// Test mobile number - REPLACE WITH YOUR ACTUAL NUMBER
$test_mobile = "9130123270"; // ⚠️ REPLACE WITH YOUR REAL MOBILE NUMBER

echo "<p><strong>Test Mobile:</strong> $test_mobile</p>";
echo "<p><em>⚠️ Replace the mobile number above with your actual number for real testing</em></p>";

// Send SMS
$result = $smsService->sendOTP($test_mobile, $test_otp, 'test');

if ($result) {
    echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px;'>";
    echo "<h3>✅ SMS Sent Successfully!</h3>";
    echo "<p>Check your phone for the SMS with OTP: <strong>$test_otp</strong></p>";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px;'>";
    echo "<h3>❌ SMS Failed</h3>";
    echo "<p>Check the error logs for details.</p>";
    echo "</div>";
}

echo "<h2>Next Steps</h2>";
echo "<ol>";
echo "<li>Replace the mobile number with your actual number</li>";
echo "<li>Run this test again</li>";
echo "<li>Check if you receive the SMS</li>";
echo "<li>If successful, your SMS system is ready!</li>";
echo "</ol>";

echo "<h2>Quick Links</h2>";
echo "<ul>";
echo "<li><a href='test_otp_system.php'>🧪 Test Complete OTP System</a></li>";
echo "<li><a href='php/admin_otp_logs.php'>📊 View SMS Logs</a></li>";
echo "<li><a href='admin_dashboard.php'>🏠 Admin Dashboard</a></li>";
echo "</ul>";
?> 