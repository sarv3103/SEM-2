<?php
// php/generate_payment_otp.php - Generate OTP for payment verification
require_once 'config.php';
require_once 'session.php';
require_once 'otp_manager.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bookingId = $_POST['booking_id'] ?? '';
    $mobile = $_POST['mobile'] ?? '';
    $amount = $_POST['amount'] ?? '';
    
    if (empty($bookingId)) {
        echo json_encode(['status' => 'error', 'msg' => 'Booking ID is required']);
        exit;
    }
    
    if (empty($mobile)) {
        echo json_encode(['status' => 'error', 'msg' => 'Mobile number is required for payment verification']);
        exit;
    }
    
    if (empty($amount)) {
        echo json_encode(['status' => 'error', 'msg' => 'Payment amount is required']);
        exit;
    }
    
    $userId = $_SESSION['user_id'] ?? 0;
    
    // Initialize OTP Manager
    $otpManager = new OTPManager($conn);
    
    // Generate mobile OTP for payment
    $mobileOTP = $otpManager->generateOTP();
    $smsSent = $otpManager->sendSMSOTP($mobile, $mobileOTP, 'payment verification');
    
    // Store payment OTP in database
    $otpManager->storePaymentOTP($bookingId, $userId, $mobileOTP);
    
    echo json_encode([
        'status' => 'success',
        'msg' => 'Payment OTP sent successfully to your mobile',
        'sms_sent' => $smsSent,
        'booking_id' => $bookingId,
        'amount' => $amount
    ]);
} else {
    echo json_encode(['status' => 'error', 'msg' => 'Invalid request method']);
}
?> 