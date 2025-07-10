<?php
// verify_wallet_payment.php - Verify Razorpay payment for wallet top-up
session_start();
require_once 'config.php';
require_once 'razorpay_config.php';
require_once '../vendor/autoload.php';

use Razorpay\Api\Api;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit();
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$payment_id = $input['razorpay_payment_id'] ?? '';
$order_id = $input['razorpay_order_id'] ?? '';
$signature = $input['razorpay_signature'] ?? '';

if (!$payment_id || !$order_id || !$signature) {
    echo json_encode(['status' => 'error', 'message' => 'Missing payment parameters']);
    exit();
}

try {
    $api = new Api($key_id, $key_secret);
    
    // Verify signature
    $attributes = [
        'razorpay_payment_id' => $payment_id,
        'razorpay_order_id' => $order_id,
        'razorpay_signature' => $signature
    ];
    
    $api->utility->verifyPaymentSignature($attributes);
    
    // Get payment details
    $payment = $api->payment->fetch($payment_id);
    
    if ($payment->status === 'captured') {
        $amount = $payment->amount / 100; // Convert from paise to rupees
        
        // Update payment order status
        $stmt = $conn->prepare("UPDATE payment_orders SET status = 'completed', razorpay_payment_id = ? WHERE razorpay_order_id = ?");
        $stmt->bind_param("ss", $payment_id, $order_id);
        $stmt->execute();
        
        // Credit user's wallet
        $userId = $_SESSION['user_id'];
        $conn->query("UPDATE users SET wallet_balance = wallet_balance + $amount WHERE id = $userId");
        
        // Add wallet transaction record
        $stmtWallet = $conn->prepare("INSERT INTO wallet_transactions (user_id, amount, type, description, payment_method) VALUES (?, ?, 'credit', ?, 'razorpay')");
        $desc = 'Wallet top-up via Razorpay payment';
        $stmtWallet->bind_param("ids", $userId, $amount, $desc);
        $stmtWallet->execute();
        
        // Get updated wallet balance
        $stmtBalance = $conn->prepare("SELECT wallet_balance FROM users WHERE id = ?");
        $stmtBalance->bind_param("i", $userId);
        $stmtBalance->execute();
        $stmtBalance->bind_result($newBalance);
        $stmtBalance->fetch();
        $stmtBalance->close();
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Wallet credited successfully!',
            'amount_added' => $amount,
            'new_balance' => $newBalance
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Payment not captured. Status: ' . $payment->status]);
    }
    
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Payment verification failed: ' . $e->getMessage()]);
}
?> 