<?php
// Test OTP verification
require_once 'php/config.php';

echo "<h2>OTP Verification Test</h2>";

// Check if otp_logs table exists and has data
$result = $conn->query("SELECT * FROM otp_logs WHERE type = 'booking_verification' ORDER BY created_at DESC LIMIT 5");

if ($result && $result->num_rows > 0) {
    echo "✅ Found OTP records in database:<br>";
    while ($row = $result->fetch_assoc()) {
        echo "Email: {$row['email']}, OTP: {$row['otp']}, Used: {$row['used']}, Expiry: {$row['expiry']}<br>";
    }
} else {
    echo "❌ No OTP records found<br>";
}

// Test OTP verification logic
if (isset($_POST['test_email']) && isset($_POST['test_otp'])) {
    $email = $_POST['test_email'];
    $otp = $_POST['test_otp'];
    
    echo "<h3>Testing OTP Verification</h3>";
    
    $stmt = $conn->prepare("
        SELECT * FROM otp_logs 
        WHERE email = ? AND otp = ? AND type = 'booking_verification' 
        AND expiry > NOW() AND used = 0
        ORDER BY created_at DESC 
        LIMIT 1
    ");
    
    $stmt->bind_param("ss", $email, $otp);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo "✅ OTP verification would succeed<br>";
    } else {
        echo "❌ OTP verification would fail<br>";
        
        // Check why it failed
        $stmt = $conn->prepare("
            SELECT * FROM otp_logs 
            WHERE email = ? AND otp = ? AND type = 'booking_verification'
            ORDER BY created_at DESC 
            LIMIT 1
        ");
        $stmt->bind_param("ss", $email, $otp);
        $stmt->execute();
        $checkResult = $stmt->get_result();
        
        if ($checkResult->num_rows > 0) {
            $otpRecord = $checkResult->fetch_assoc();
            if ($otpRecord['used'] == 1) {
                echo "Reason: OTP already used<br>";
            } else {
                echo "Reason: OTP expired (expiry: {$otpRecord['expiry']})<br>";
            }
        } else {
            echo "Reason: OTP not found<br>";
        }
    }
}
?>

<form method="POST">
    <h3>Test OTP Verification</h3>
    <p>Email: <input type="email" name="test_email" required></p>
    <p>OTP: <input type="text" name="test_otp" maxlength="6" required></p>
    <button type="submit">Test Verification</button>
</form> 