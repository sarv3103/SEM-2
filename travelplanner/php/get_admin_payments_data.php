<?php
session_start();
require_once 'config.php';
require_once 'session.php';

// Only allow admin access
requireAdmin();

header('Content-Type: application/json');

try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }

    // Get all wallet top-up payments (Razorpay only)
    $paymentsQuery = "
        SELECT 
            po.id,
            po.payment_id,
            po.order_id,
            po.booking_id,
            po.user_id,
            po.amount,
            po.status,
            po.payment_method,
            po.razorpay_payment_id,
            po.created_at,
            u.username,
            u.email as user_email,
            'wallet_topup' as payment_type
        FROM payment_orders po
        LEFT JOIN users u ON po.user_id = u.id
        WHERE po.payment_method = 'razorpay' AND (po.order_id LIKE 'wallet_%')
        ORDER BY po.created_at DESC
    ";
    
    $paymentsResult = $conn->query($paymentsQuery);
    $payments = [];
    
    if ($paymentsResult) {
        while ($row = $paymentsResult->fetch_assoc()) {
            $payments[] = $row;
        }
    }

    // Get wallet balances for all users
    $walletsQuery = "
        SELECT 
            u.id as user_id,
            u.username,
            u.email,
            COALESCE(w.balance, 0) as balance,
            w.last_updated
        FROM users u
        LEFT JOIN wallet w ON u.id = w.user_id
        WHERE u.is_admin = 0
        ORDER BY w.balance DESC, u.username
    ";
    
    $walletsResult = $conn->query($walletsQuery);
    $wallets = [];
    
    if ($walletsResult) {
        while ($row = $walletsResult->fetch_assoc()) {
            $wallets[] = $row;
        }
    }

    // Calculate summary statistics
    $totalWallet = array_sum(array_column($wallets, 'balance'));
    $completedPayments = count(array_filter($payments, function($p) { return $p['status'] === 'completed'; }));
    $pendingPayments = count(array_filter($payments, function($p) { return $p['status'] === 'pending'; }));
    $failedPayments = count(array_filter($payments, function($p) { return $p['status'] === 'failed'; }));

    $summary = [
        'total_wallet' => $totalWallet,
        'completed_payments' => $completedPayments,
        'pending_payments' => $pendingPayments,
        'failed_payments' => $failedPayments,
        'total_payments' => count($payments)
    ];

    echo json_encode([
        'success' => true,
        'data' => [
            'payments' => $payments,
            'wallets' => $wallets
        ],
        'summary' => $summary
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?> 