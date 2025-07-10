<?php
// test_forgot_password.php - Test forgot password functionality
require_once 'php/config.php';
require_once 'php/otp_manager.php';

echo "<h2>Forgot Password Test</h2>";

// Test 1: Check if we can send OTP
echo "<h3>Test 1: OTP Sending Test</h3>";

if (isset($_POST['test_otp'])) {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    
    if (empty($username) || empty($email)) {
        echo "❌ Please provide username and email<br>";
    } else {
        // Check if user exists
        $stmt = $conn->prepare("SELECT id, email FROM users WHERE username = ? AND email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            echo "❌ No account found with username: $username and email: $email<br>";
        } else {
            $user = $result->fetch_assoc();
            echo "✅ User found: ID = {$user['id']}<br>";
            
            // Initialize OTP Manager
            $otpManager = new OTPManager($conn);
            
            // Generate email OTP
            $emailOTP = $otpManager->generateOTP();
            echo "✅ OTP generated: $emailOTP<br>";
            
            // Send email OTP
            $emailSent = $otpManager->sendEmailOTP($email, $emailOTP, 'password reset');
            
            if ($emailSent) {
                echo "✅ Email OTP sent successfully<br>";
                
                // Store OTP in database
                $otpManager->storeOTP($user['id'], $email, null, $emailOTP, null);
                echo "✅ OTP stored in database<br>";
                
                echo "<div style='background: #e8f5e8; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
                echo "<strong>Test Results:</strong><br>";
                echo "User ID: {$user['id']}<br>";
                echo "Email: $email<br>";
                echo "OTP: $emailOTP<br>";
                echo "Status: Ready for verification<br>";
                echo "</div>";
                
                // Store test data in session for verification
                session_start();
                $_SESSION['test_user_id'] = $user['id'];
                $_SESSION['test_email_otp'] = $emailOTP;
                
            } else {
                echo "❌ Failed to send email OTP<br>";
            }
        }
    }
}

// Test 2: Check OTP verification
echo "<h3>Test 2: OTP Verification Test</h3>";

if (isset($_POST['verify_otp'])) {
    $inputOTP = $_POST['input_otp'] ?? '';
    
    if (empty($inputOTP)) {
        echo "❌ Please enter OTP<br>";
    } else {
        session_start();
        $storedOTP = $_SESSION['test_email_otp'] ?? '';
        $userId = $_SESSION['test_user_id'] ?? '';
        
        if (empty($storedOTP) || empty($userId)) {
            echo "❌ No test OTP found. Please send OTP first.<br>";
        } else {
            if ($inputOTP === $storedOTP) {
                echo "✅ OTP verified successfully!<br>";
                echo "User ID: $userId<br>";
                echo "OTP: $inputOTP<br>";
                
                // Test password update
                $newPassword = "test123456";
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                
                $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->bind_param("si", $hashedPassword, $userId);
                
                if ($stmt->execute()) {
                    echo "✅ Password updated successfully!<br>";
                    echo "New password: $newPassword<br>";
                    
                    // Clear test data
                    unset($_SESSION['test_user_id']);
                    unset($_SESSION['test_email_otp']);
                } else {
                    echo "❌ Failed to update password<br>";
                }
            } else {
                echo "❌ Invalid OTP. Expected: $storedOTP, Got: $inputOTP<br>";
            }
        }
    }
}

// Test 3: Check database tables
echo "<h3>Test 3: Database Check</h3>";

// Check users table
$result = $conn->query("SELECT COUNT(*) as count FROM users");
$userCount = $result->fetch_assoc()['count'];
echo "Users in database: $userCount<br>";

// Check OTP logs
$result = $conn->query("SELECT COUNT(*) as count FROM otp_log");
$otpCount = $result->fetch_assoc()['count'];
echo "OTP logs in database: $otpCount<br>";

// Show recent OTP logs
echo "<h4>Recent OTP Logs:</h4>";
$result = $conn->query("SELECT * FROM otp_log ORDER BY created_at DESC LIMIT 5");
if ($result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>User ID</th><th>Type</th><th>Status</th><th>Created</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['user_id']}</td>";
        echo "<td>{$row['type']}</td>";
        echo "<td>{$row['status']}</td>";
        echo "<td>{$row['created_at']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "No OTP logs found<br>";
}

?>

<!-- Test Forms -->
<h3>Manual Test Forms</h3>

<form method="post" style="margin: 20px 0; padding: 20px; border: 1px solid #ccc; border-radius: 5px;">
    <h4>Test 1: Send OTP</h4>
    <input type="text" name="username" placeholder="Username" required style="width: 100%; padding: 8px; margin: 5px 0;">
    <input type="email" name="email" placeholder="Email" required style="width: 100%; padding: 8px; margin: 5px 0;">
    <button type="submit" name="test_otp" style="background: #0077cc; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">Send Test OTP</button>
</form>

<form method="post" style="margin: 20px 0; padding: 20px; border: 1px solid #ccc; border-radius: 5px;">
    <h4>Test 2: Verify OTP</h4>
    <input type="text" name="input_otp" placeholder="Enter OTP" required style="width: 100%; padding: 8px; margin: 5px 0;">
    <button type="submit" name="verify_otp" style="background: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">Verify OTP</button>
</form>

<div style="margin: 20px 0;">
    <h4>Test Links</h4>
    <a href="login.html" style="color: #0077cc; text-decoration: none; margin-right: 20px;">Test Login Page</a>
    <a href="index.html" style="color: #0077cc; text-decoration: none; margin-right: 20px;">Test Main Page</a>
    <a href="reset_password.html" style="color: #0077cc; text-decoration: none;">Test Reset Password Page</a>
</div> 