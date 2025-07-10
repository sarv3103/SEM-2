<?php
// php/verify_booking_otp.php - Verify OTP for booking confirmation
session_start();
require_once 'config.php';

header('Content-Type: application/json');

// Set timezone to IST (India Standard Time)
date_default_timezone_set('Asia/Kolkata');

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['email']) || !isset($input['otp'])) {
    echo json_encode(['status' => 'error', 'message' => 'Email and OTP are required']);
    exit();
}

$email = $input['email'];
$otp = $input['otp'];

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid email address']);
    exit();
}

// Validate OTP format
if (!preg_match('/^[0-9]{6}$/', $otp)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid OTP format']);
    exit();
}

try {
    // Debug: Log the verification attempt
    error_log("OTP Verification attempt - Email: $email, OTP: $otp, Current time: " . date('Y-m-d H:i:s'));
    
    // Check OTP in database
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
    
    // Debug: Log the query result
    error_log("OTP Query result rows: " . $result->num_rows);
    
    if ($result->num_rows === 0) {
        // Check if OTP exists but is expired or used
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
            error_log("OTP found but invalid - Used: {$otpRecord['used']}, Expiry: {$otpRecord['expiry']}, Current: " . date('Y-m-d H:i:s'));
            
            if ($otpRecord['used'] == 1) {
                echo json_encode(['status' => 'error', 'message' => 'OTP has already been used']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'OTP has expired (valid for 30 minutes)']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid OTP']);
        }
        exit();
    }
    
    $otpRecord = $result->fetch_assoc();
    
    // Mark OTP as used
    $stmt = $conn->prepare("UPDATE otp_logs SET used = 1 WHERE id = ?");
    $stmt->bind_param("i", $otpRecord['id']);
    $stmt->execute();
    
    // Store verification in session
    $_SESSION['email_verified'] = $email;
    $_SESSION['email_verification_time'] = time();
    
    error_log("OTP verified successfully for email: $email");
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Email verified successfully'
    ]);
    
} catch (Exception $e) {
    error_log("OTP verification error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to verify OTP. Please try again.'
    ]);
}
?> 