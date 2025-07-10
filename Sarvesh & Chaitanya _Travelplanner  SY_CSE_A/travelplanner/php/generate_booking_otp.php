<?php
// php/generate_booking_otp.php - Generate OTP for booking confirmation
require_once 'config.php';
require_once 'session.php';
require_once 'otp_manager.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bookingId = $_POST['booking_id'] ?? '';
    $email = $_POST['email'] ?? '';
    $mobile = $_POST['mobile'] ?? '';
    
    if (empty($bookingId)) {
        echo json_encode(['status' => 'error', 'msg' => 'Booking ID is required']);
        exit;
    }
    
    if (empty($email) && empty($mobile)) {
        echo json_encode(['status' => 'error', 'msg' => 'Email or mobile is required']);
        exit;
    }
    
    $userId = $_SESSION['user_id'] ?? 0;
    
    // Initialize OTP Manager
    $otpManager = new OTPManager($conn);
    
    $emailOTP = null;
    $mobileOTP = null;
    $emailSent = false;
    $smsSent = false;
    
    // Generate and send email OTP
    if (!empty($email)) {
        $emailOTP = $otpManager->generateOTP();
        $emailSent = $otpManager->sendEmailOTP($email, $emailOTP, 'booking confirmation');
    }
    
    // Generate and send mobile OTP
    if (!empty($mobile)) {
        $mobileOTP = $otpManager->generateOTP();
        $smsSent = $otpManager->sendSMSOTP($mobile, $mobileOTP, 'booking confirmation');
    }
    
    // Store OTPs in database
    $otpManager->storeBookingOTP($bookingId, $userId, $emailOTP, $mobileOTP);
    
    echo json_encode([
        'status' => 'success',
        'msg' => 'OTP sent successfully for booking confirmation',
        'email_sent' => $emailSent,
        'sms_sent' => $smsSent,
        'booking_id' => $bookingId
    ]);
} else {
    echo json_encode(['status' => 'error', 'msg' => 'Invalid request method']);
}
?> 