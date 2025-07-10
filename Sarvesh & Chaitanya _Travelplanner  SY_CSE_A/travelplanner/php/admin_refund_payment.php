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
$remarks = $_POST['remarks'] ?? 'Admin refund';

if (!$paymentId) {
    echo json_encode(['success' => false, 'message' => 'Missing payment ID']);
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
        WHERE po.id = ? AND po.status = 'completed'
    ");
    $paymentStmt->bind_param("i", $paymentId);
    $paymentStmt->execute();
    $paymentResult = $paymentStmt->get_result();
    
    if ($paymentResult->num_rows === 0) {
        throw new Exception("Payment record not found or not completed");
    }
    
    $payment = $paymentResult->fetch_assoc();

    // Update payment status to refunded
    $updatePaymentStmt = $conn->prepare("
        UPDATE payment_orders 
        SET status = 'refunded', 
            remarks = CONCAT(IFNULL(remarks, ''), ' | Refunded: ', ?), 
            updated_at = NOW() 
        WHERE id = ?
    ");
    $updatePaymentStmt->bind_param("si", $remarks, $paymentId);
    $updatePaymentStmt->execute();

    // Add refund amount to user's wallet
    $walletStmt = $conn->prepare("SELECT id, balance FROM wallet WHERE user_id = ?");
    $walletStmt->bind_param("i", $payment['user_id']);
    $walletStmt->execute();
    $walletResult = $walletStmt->get_result();
    
    if ($walletResult->num_rows === 0) {
        // Create new wallet
        $createWalletStmt = $conn->prepare("INSERT INTO wallet (user_id, balance, last_updated) VALUES (?, ?, NOW())");
        $createWalletStmt->bind_param("id", $payment['user_id'], $payment['amount']);
        $createWalletStmt->execute();
        $newBalance = $payment['amount'];
    } else {
        // Update existing wallet
        $wallet = $walletResult->fetch_assoc();
        $newBalance = $wallet['balance'] + $payment['amount'];
        
        $updateWalletStmt = $conn->prepare("UPDATE wallet SET balance = ?, last_updated = NOW() WHERE user_id = ?");
        $updateWalletStmt->bind_param("di", $newBalance, $payment['user_id']);
        $updateWalletStmt->execute();
    }

    // Create refund payment record
    $refundOrderId = 'refund_' . time() . '_' . $payment['user_id'];
    $refundStmt = $conn->prepare("
        INSERT INTO payment_orders (user_id, order_id, amount, status, payment_method, reference, remarks, created_at, updated_at) 
        VALUES (?, ?, ?, 'completed', 'admin_refund', ?, ?, NOW(), NOW())
    ");
    $refundStmt->bind_param("isds", $payment['user_id'], $refundOrderId, $payment['amount'], $payment['payment_id'], $remarks);
    $refundStmt->execute();

    // If this was a booking payment, update booking status
    if (strpos($payment['order_id'], 'booking_') === 0) {
        $bookingId = str_replace('booking_', '', $payment['order_id']);
        
        // Update booking status to cancelled
        $updateBookingStmt = $conn->prepare("
            UPDATE bookings 
            SET status = 'cancelled', 
                payment_status = 'refunded'
            WHERE id = ?
        ");
        $updateBookingStmt->bind_param("i", $bookingId);
        $updateBookingStmt->execute();
    }

    // Log the admin action
    $adminId = $_SESSION['admin_id'] ?? 1;
    $logStmt = $conn->prepare("
        INSERT INTO admin_actions (admin_id, action_type, target_user_id, amount, reason, remarks, created_at) 
        VALUES (?, 'payment_refund', ?, ?, 'admin_refund', ?, NOW())
    ");
    $logStmt->bind_param("iids", $adminId, $payment['user_id'], $payment['amount'], $remarks);
    $logStmt->execute();

    // Commit transaction
    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => "Payment refunded successfully. ₹{$payment['amount']} added to {$payment['username']}'s wallet. New balance: ₹{$newBalance}"
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