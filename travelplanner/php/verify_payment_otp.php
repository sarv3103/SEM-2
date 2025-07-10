<?php
// php/verify_payment_otp.php - Verify OTP for payment verification
require_once 'config.php';
require_once 'session.php';
require_once 'otp_manager.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bookingId = $_POST['booking_id'] ?? '';
    $mobileOTP = $_POST['mobile_otp'] ?? '';
    $amount = $_POST['amount'] ?? '';
    
    if (empty($bookingId)) {
        echo json_encode(['status' => 'error', 'msg' => 'Booking ID is required']);
        exit;
    }
    
    if (empty($mobileOTP)) {
        echo json_encode(['status' => 'error', 'msg' => 'Mobile OTP is required']);
        exit;
    }
    
    if (empty($amount)) {
        echo json_encode(['status' => 'error', 'msg' => 'Payment amount is required']);
        exit;
    }
    
    // Initialize OTP Manager
    $otpManager = new OTPManager($conn);
    
    // Verify payment OTP
    $verified = $otpManager->verifyPaymentOTP($bookingId, $mobileOTP);
    
    if ($verified) {
        // Process payment here (integrate with payment gateway)
        // For now, we'll just mark it as successful
        
        echo json_encode([
            'status' => 'success',
            'msg' => 'Payment verified successfully! Your booking is confirmed.',
            'booking_id' => $bookingId,
            'amount' => $amount,
            'payment_status' => 'completed'
        ]);
    } else {
        echo json_encode(['status' => 'error', 'msg' => 'Invalid or expired payment OTP']);
    }
} else {
    echo json_encode(['status' => 'error', 'msg' => 'Invalid request method']);
}
?> 