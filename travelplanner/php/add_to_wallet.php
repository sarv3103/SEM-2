<?php
// add_to_wallet.php - Handle Razorpay wallet top-up and credit user's wallet
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

$userId = $_SESSION['user_id'];
$amount = floatval($_POST['amount'] ?? 0);
$payment_id = $_POST['razorpay_payment_id'] ?? '';
// $order_id = $_POST['razorpay_order_id'] ?? '';

if ($amount <= 0 || !$payment_id) {
    echo json_encode(['status' => 'error', 'message' => 'Missing or invalid parameters']);
    exit();
}

try {
    $api = new Api($key_id, $key_secret);
    $payment = $api->payment->fetch($payment_id);
    if ($payment->status === 'captured') {
        // Credit wallet
        $conn->query("UPDATE users SET wallet_balance = wallet_balance + $amount WHERE id = $userId");
        // Add wallet transaction
        $stmtWallet = $conn->prepare("INSERT INTO wallet_transactions (user_id, amount, type, description) VALUES (?, ?, 'credit', ?)");
        $desc = 'Wallet top-up via Razorpay';
        $stmtWallet->bind_param("ids", $userId, $amount, $desc);
        $stmtWallet->execute();
        // --- Sync wallet table for admin dashboard ---
        $stmtWalletSync = $conn->prepare("INSERT INTO wallet (user_id, balance, last_updated) VALUES (?, (SELECT wallet_balance FROM users WHERE id = ?), NOW()) ON DUPLICATE KEY UPDATE balance = (SELECT wallet_balance FROM users WHERE id = ?), last_updated = NOW()");
        $stmtWalletSync->bind_param("iii", $userId, $userId, $userId);
        $stmtWalletSync->execute();
        // --- End wallet table sync ---
        echo json_encode(['status' => 'success', 'message' => 'Wallet credited successfully', 'wallet_balance' => $amount]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Payment not captured. Status: ' . $payment->status]);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error verifying payment: ' . $e->getMessage()]);
} 