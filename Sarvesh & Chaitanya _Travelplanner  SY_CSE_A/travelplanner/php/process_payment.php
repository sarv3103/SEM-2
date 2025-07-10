<?php
session_start();
require_once 'config.php';
require_once 'razorpay_config.php';

header('Content-Type: application/json');

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['booking_id']) || !isset($input['amount'])) {
    echo json_encode(['status' => 'error', 'message' => 'Booking ID and amount are required']);
    exit();
}

$bookingId = $input['booking_id'];
$originalAmount = $input['amount'];
$testAmount = 1; // Always charge ₹1 for testing

try {
    // Initialize Razorpay
    $razorpay = new RazorpayService();
    
    // Create Razorpay order with ₹1
    $order = $razorpay->createOrder($testAmount);
    
    // Store order details in database with original amount
    $stmt = $conn->prepare("
        INSERT INTO payment_orders (booking_id, razorpay_order_id, amount, status, created_at) 
        VALUES (?, ?, ?, 'pending', NOW())
    ");
    $stmt->bind_param("ssd", $bookingId, $order->id, $originalAmount);
    $stmt->execute();
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Payment order created successfully (₹1 test payment)',
        'order_id' => $order->id,
        'amount' => $testAmount, // ₹1 for Razorpay
        'original_amount' => $originalAmount, // Original amount for display
        'currency' => 'INR',
        'key_id' => 'rzp_live_2JdrplZN9MSywf' // Live key for production
    ]);
    
} catch (Exception $e) {
    error_log("Payment processing error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Payment processing failed: ' . $e->getMessage()
    ]);
}
?> 