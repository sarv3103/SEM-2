<?php
// debug_otp_issue.php - Debug OTP verification issues
require_once 'php/config.php';
require_once 'php/session.php';

echo "<h2>🔍 OTP Verification Debug</h2>";

// Debug session
echo "<h3>Session Debug:</h3>";
echo "Session ID: " . session_id() . "<br>";
echo "Session Status: " . session_status() . "<br>";
echo "Session Data: <pre>" . print_r($_SESSION, true) . "</pre><br>";

// Debug POST data
echo "<h3>POST Data:</h3>";
echo "<pre>" . print_r($_POST, true) . "</pre><br>";

// Check if pending registration exists
if (isset($_SESSION['pending_registration'])) {
    echo "<h3>Pending Registration Data:</h3>";
    echo "<pre>" . print_r($_SESSION['pending_registration'], true) . "</pre><br>";
    
    $registrationData = $_SESSION['pending_registration'];
    $inputOTP = $_POST['email_otp'] ?? '';
    
    echo "<h3>OTP Comparison:</h3>";
    echo "Input OTP: '$inputOTP'<br>";
    echo "Stored OTP: '{$registrationData['email_otp']}'<br>";
    echo "Match: " . ($inputOTP === $registrationData['email_otp'] ? 'YES' : 'NO') . "<br>";
    echo "Length comparison: " . strlen($inputOTP) . " vs " . strlen($registrationData['email_otp']) . "<br>";
    
    if ($inputOTP === $registrationData['email_otp']) {
        echo "<h3>✅ OTP Verification Success!</h3>";
        
        // Create user account
        $first_name = $registrationData['first_name'];
        $last_name = $registrationData['last_name'];
        $username = $registrationData['username'];
        $email = $registrationData['email'];
        $password = $registrationData['password'];
        
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert new user
        $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, username, email, password, email_verified, status) VALUES (?, ?, ?, ?, ?, 1, 'active')");
        $stmt->bind_param("sssss", $first_name, $last_name, $username, $email, $hashed_password);
        
        if ($stmt->execute()) {
            $userId = $conn->insert_id;
            echo "<h3>✅ User Account Created!</h3>";
            echo "User ID: $userId<br>";
            echo "Username: $username<br>";
            echo "Email: $email<br>";
            
            // Clear session
            unset($_SESSION['pending_registration']);
            echo "<h3>✅ Session Cleared</h3>";
            
            echo "<h3>🎉 Registration Complete!</h3>";
            echo "<p>You can now login with username: <strong>$username</strong> and your password.</p>";
            
        } else {
            echo "<h3>❌ Failed to create user account</h3>";
            echo "Error: " . $stmt->error . "<br>";
        }
    } else {
        echo "<h3>❌ OTP Verification Failed</h3>";
        echo "The OTPs don't match. Please check your email and try again.<br>";
    }
} else {
    echo "<h3>❌ No Pending Registration Found</h3>";
    echo "Session data is missing. Please start the registration process again.<br>";
}

// Check database for recent OTP logs
echo "<h3>Recent OTP Logs:</h3>";
$result = $conn->query("SELECT * FROM otp_log ORDER BY created_at DESC LIMIT 5");
if ($result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>User ID</th><th>Type</th><th>Status</th><th>Details</th><th>Created</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['user_id']}</td>";
        echo "<td>{$row['type']}</td>";
        echo "<td>{$row['status']}</td>";
        echo "<td>{$row['details']}</td>";
        echo "<td>{$row['created_at']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "No OTP logs found<br>";
}

echo "<h3>Test Links:</h3>";
echo "<a href='register.html'>Test Registration</a> | ";
echo "<a href='debug_forgot_password_flow.html'>Test Forgot Password</a> | ";
echo "<a href='admin_dashboard.php'>Admin Dashboard</a>";
?> 