<?php
// php/forgot_password.php - Forgot password functionality with OTP verification
require_once 'config.php';
require_once 'otp_manager.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'send_otp';
    
    if ($action === 'send_otp') {
        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';

        if (empty($username) || empty($email)) {
            echo json_encode(['status' => 'error', 'msg' => 'Please fill in username and email']);
            exit;
        }

        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['status' => 'error', 'msg' => 'Invalid email format']);
            exit;
        }

        // Check if user exists
        $stmt = $conn->prepare("SELECT id, email FROM users WHERE username = ? AND email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            echo json_encode(['status' => 'error', 'msg' => 'No account found with these details']);
            exit;
        }
        
        $user = $result->fetch_assoc();
        
        // Initialize OTP Manager
        $otpManager = new OTPManager($conn);
        
        // Generate email OTP
        $emailOTP = $otpManager->generateOTP();
        $emailSent = $otpManager->sendEmailOTP($email, $emailOTP, 'password reset');
        
        // Store OTP in database
        $otpManager->storeOTP($user['id'], $email, null, $emailOTP, null);
        
        echo json_encode([
            'status' => 'success',
            'msg' => 'OTP sent to your email for password reset verification',
            'user_id' => $user['id'],
            'email_sent' => $emailSent
        ]);
        
    } elseif ($action === 'verify_otp') {
        $userId = $_POST['user_id'] ?? '';
        $emailOTP = $_POST['email_otp'] ?? '';
        
        if (empty($userId) || empty($emailOTP)) {
            echo json_encode(['status' => 'error', 'msg' => 'User ID and OTP are required']);
            exit;
        }
        
        // Initialize OTP Manager
        $otpManager = new OTPManager($conn);
        
        // Verify OTP
        $verified = $otpManager->verifyOTP($userId, $emailOTP, null);
        
        if ($verified) {
            // Generate reset token for password change
            $reset_token = bin2hex(random_bytes(32));
            $reset_expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Store reset token (you might want to create a separate table for this)
            // For now, we'll just return success
            echo json_encode([
                'status' => 'success',
                'msg' => 'OTP verified successfully! You can now reset your password.',
                'user_id' => $userId,
                'reset_token' => $reset_token
            ]);
        } else {
            echo json_encode(['status' => 'error', 'msg' => 'Invalid or expired OTP']);
        }
        
    } elseif ($action === 'reset_password') {
        $userId = $_POST['user_id'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if (empty($userId) || empty($newPassword) || empty($confirmPassword)) {
            echo json_encode(['status' => 'error', 'msg' => 'All fields are required']);
            exit;
        }
        
        if ($newPassword !== $confirmPassword) {
            echo json_encode(['status' => 'error', 'msg' => 'Passwords do not match']);
            exit;
        }
        
        if (strlen($newPassword) < 6) {
            echo json_encode(['status' => 'error', 'msg' => 'Password should be at least 6 characters']);
            exit;
        }
        
        // Hash new password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        // Update password in database
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hashedPassword, $userId);
        
        if ($stmt->execute()) {
            echo json_encode([
                'status' => 'success',
                'msg' => 'Password reset successfully! You can now login with your new password.'
            ]);
        } else {
            echo json_encode(['status' => 'error', 'msg' => 'Failed to reset password. Please try again.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'msg' => 'Invalid action']);
    }
} else {
    echo json_encode(['status' => 'error', 'msg' => 'Invalid request method']);
}
?>
