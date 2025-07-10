<?php
// get_wallet_transactions.php - Get wallet transaction history for logged-in user
session_start();
require_once 'config.php';
require_once 'session.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit();
}

try {
    $userId = $_SESSION['user_id'];
    
    // Get wallet transactions
    $stmt = $conn->prepare("
        SELECT amount, type, description, created_at 
        FROM wallet_transactions 
        WHERE user_id = ? 
        ORDER BY created_at DESC 
        LIMIT 20
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $transactions = [];
    while ($row = $result->fetch_assoc()) {
        $transactions[] = [
            'amount' => $row['amount'],
            'type' => $row['type'],
            'description' => $row['description'],
            'created_at' => date('d M Y, h:i A', strtotime($row['created_at']))
        ];
    }
    
    echo json_encode([
        'status' => 'success',
        'transactions' => $transactions
    ]);
    
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to load transactions: ' . $e->getMessage()]);
}
?> 
 