<?php
session_start();
require_once 'config.php';
require_once 'session.php';

// Only allow admin access
requireAdmin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$paymentId = $_POST['payment_id'] ?? '';
$orderId = $_POST['order_id'] ?? '';
$amount = $_POST['amount'] ?? '';
$remarks = $_POST['remarks'] ?? '';
$paymentRecordId = $_POST['payment_record_id'] ?? '';

if (!$paymentId || !$orderId || !$amount) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }

    // Start transaction
    $conn->begin_transaction();

    // Get the payment record
    $paymentStmt = $conn->prepare("
        SELECT po.*, u.username 
        FROM payment_orders po 
        LEFT JOIN users u ON po.user_id = u.id 
        WHERE po.id = ?
    ");
    $paymentStmt->bind_param("i", $paymentRecordId);
    $paymentStmt->execute();
    $paymentResult = $paymentStmt->get_result();
    
    if ($paymentResult->num_rows === 0) {
        throw new Exception("Payment record not found");
    }
    
    $payment = $paymentResult->fetch_assoc();

    // Update payment record
    $updatePaymentStmt = $conn->prepare("
        UPDATE payment_orders 
        SET status = 'completed', 
            payment_id = ?, 
            reference = ?, 
            remarks = CONCAT(IFNULL(remarks, ''), ' | Admin verified: ', ?), 
            updated_at = NOW() 
        WHERE id = ?
    ");
    $updatePaymentStmt->bind_param("sssi", $paymentId, $orderId, $remarks, $paymentRecordId);
    $updatePaymentStmt->execute();

    // If this is a booking payment, update the booking status
    if (strpos($payment['order_id'], 'booking_') === 0) {
        $bookingId = str_replace('booking_', '', $payment['order_id']);
        
        // Update booking status
        $updateBookingStmt = $conn->prepare("
            UPDATE bookings 
            SET status = 'completed', 
                payment_status = 'completed', 
                razorpay_payment_id = ? 
            WHERE id = ?
        ");
        $updateBookingStmt->bind_param("si", $paymentId, $bookingId);
        $updateBookingStmt->execute();
    }

    // Log the admin action
    $adminId = $_SESSION['admin_id'] ?? 1;
    $logStmt = $conn->prepare("
        INSERT INTO admin_actions (admin_id, action_type, target_user_id, amount, reason, remarks, created_at) 
        VALUES (?, 'manual_payment_verification', ?, ?, 'manual_verification', ?, NOW())
    ");
    $logStmt->bind_param("iids", $adminId, $payment['user_id'], $amount, $remarks);
    $logStmt->execute();

    // Commit transaction
    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => "Payment verified successfully for {$payment['username']}. Amount: ₹{$amount}"
    ]);

} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollback();
    }
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

if (isset($conn)) {
    $conn->close();
}
?> 