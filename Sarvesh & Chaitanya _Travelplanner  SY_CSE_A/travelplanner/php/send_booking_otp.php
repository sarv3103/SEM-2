<?php
session_start();
require_once 'config.php';
require_once 'email_config.php';

header('Content-Type: application/json');

// Set timezone to IST (India Standard Time)
date_default_timezone_set('Asia/Kolkata');

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['email']) || !isset($input['mobile'])) {
    echo json_encode(['status' => 'error', 'message' => 'Email and mobile number are required']);
    exit();
}

$email = $input['email'];
$mobile = $input['mobile'];

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid email address']);
    exit();
}

// Validate mobile number
if (!preg_match('/^[0-9]{10}$/', $mobile)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid mobile number']);
    exit();
}

try {
    // Generate 6-digit OTP
    $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    
    // Store OTP in database with expiration (30 minutes instead of 10)
    $expiry = date('Y-m-d H:i:s', strtotime('+30 minutes'));
    
    // Debug: Log OTP creation
    error_log("Creating OTP for email: $email, OTP: $otp, Expiry: $expiry");
    
    $stmt = $conn->prepare("
        INSERT INTO otp_logs (email, mobile, otp, expiry, type) 
        VALUES (?, ?, ?, ?, 'booking_verification')
        ON DUPLICATE KEY UPDATE 
        otp = VALUES(otp), 
        expiry = VALUES(expiry), 
        created_at = CURRENT_TIMESTAMP,
        used = 0
    ");
    
    $stmt->bind_param("ssss", $email, $mobile, $otp, $expiry);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to store OTP: " . $stmt->error);
    }
    
    // Send OTP via EmailService
    $emailService = new EmailService();
    $emailSent = $emailService->sendOTP($email, $otp, 'booking verification');
    
    if ($emailSent) {
        echo json_encode([
            'status' => 'success',
            'message' => 'OTP sent successfully to your email (valid for 30 minutes)'
        ]);
    } else {
        throw new Exception("Failed to send email");
    }
    
} catch (Exception $e) {
    error_log("OTP sending error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to send OTP. Please check your email configuration or try again later.'
    ]);
}
?> 