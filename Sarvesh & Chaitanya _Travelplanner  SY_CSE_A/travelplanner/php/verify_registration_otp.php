<?php
// php/verify_registration_otp.php - Verify registration OTP (Email Only)
require_once 'config.php';
require_once 'session.php';
require_once 'otp_manager.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emailOTP = trim($_POST['email_otp'] ?? '');
    $userId = $_POST['user_id'] ?? '';
    
    if (empty($emailOTP)) {
        echo json_encode(['status' => 'error', 'msg' => 'Email OTP is required']);
        exit;
    }
    
    if (empty($userId)) {
        echo json_encode(['status' => 'error', 'msg' => 'User ID is required']);
        exit;
    }
    
    // Initialize OTP Manager
    $otpManager = new OTPManager($conn);
    
    // Verify OTP from database
    $verified = $otpManager->verifyOTP($userId, $emailOTP, null);
    
    if (!$verified) {
        echo json_encode(['status' => 'error', 'msg' => 'Invalid or expired OTP. Please check your email and try again.']);
        exit;
    }
    
    // OTP verified - Update user status to active
    $stmt = $conn->prepare("UPDATE users SET email_verified = 1, status = 'active' WHERE id = ?");
    $stmt->bind_param("i", $userId);
    
    if ($stmt->execute()) {
        // Log successful verification
        $stmt = $conn->prepare("INSERT INTO otp_log (user_id, type, status, details, created_at) VALUES (?, 'registration', 'success', ?, NOW())");
        $details = "User registration completed successfully with email OTP verification.";
        $stmt->bind_param("is", $userId, $details);
        $stmt->execute();
        
        // Clear pending registration from session if exists
        if (isset($_SESSION['pending_registration'])) {
            unset($_SESSION['pending_registration']);
        }
        
        // Registration completed successfully
        echo json_encode([
            'status' => 'success',
            'msg' => 'Registration completed successfully! You can now login.'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'msg' => 'Failed to activate account. Please try again.'
        ]);
    }
    
} else {
    echo json_encode(['status' => 'error', 'msg' => 'Invalid request method']);
}
?> 