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

$userId = $_POST['user_id'] ?? '';
$amount = $_POST['amount'] ?? '';
$reason = $_POST['reason'] ?? '';
$remarks = $_POST['remarks'] ?? '';

if (!$userId || !$amount || !$reason) {
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

    // Validate user exists
    $userStmt = $conn->prepare("SELECT id, username FROM users WHERE id = ? AND is_admin = 0");
    $userStmt->bind_param("i", $userId);
    $userStmt->execute();
    $userResult = $userStmt->get_result();
    
    if ($userResult->num_rows === 0) {
        throw new Exception("User not found");
    }
    
    $user = $userResult->fetch_assoc();

    // Check if wallet exists, create if not
    $walletStmt = $conn->prepare("SELECT id, balance FROM wallet WHERE user_id = ?");
    $walletStmt->bind_param("i", $userId);
    $walletStmt->execute();
    $walletResult = $walletStmt->get_result();
    
    if ($walletResult->num_rows === 0) {
        // Create new wallet
        $createWalletStmt = $conn->prepare("INSERT INTO wallet (user_id, balance, last_updated) VALUES (?, ?, NOW())");
        $createWalletStmt->bind_param("id", $userId, $amount);
        $createWalletStmt->execute();
        $newBalance = $amount;
    } else {
        // Update existing wallet
        $wallet = $walletResult->fetch_assoc();
        $newBalance = $wallet['balance'] + $amount;
        
        $updateWalletStmt = $conn->prepare("UPDATE wallet SET balance = ?, last_updated = NOW() WHERE user_id = ?");
        $updateWalletStmt->bind_param("di", $newBalance, $userId);
        $updateWalletStmt->execute();
    }

    // Create payment order record for the credit
    $orderId = 'wallet_' . time() . '_' . $userId;
    $paymentStmt = $conn->prepare("
        INSERT INTO payment_orders (user_id, order_id, amount, status, payment_method, reference, remarks, created_at, updated_at) 
        VALUES (?, ?, ?, 'completed', 'admin_credit', ?, ?, NOW(), NOW())
    ");
    $paymentStmt->bind_param("isds", $userId, $orderId, $amount, $reason, $remarks);
    $paymentStmt->execute();

    // Log the transaction
    $logStmt = $conn->prepare("
        INSERT INTO admin_actions (admin_id, action_type, target_user_id, amount, reason, remarks, created_at) 
        VALUES (?, 'wallet_credit', ?, ?, ?, ?, NOW())
    ");
    $adminId = $_SESSION['admin_id'] ?? 1; // Default admin ID if not set
    $logStmt->bind_param("iidss", $adminId, $userId, $amount, $reason, $remarks);
    $logStmt->execute();

    // Commit transaction
    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => "Successfully added ₹{$amount} to {$user['username']}'s wallet. New balance: ₹{$newBalance}",
        'new_balance' => $newBalance
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