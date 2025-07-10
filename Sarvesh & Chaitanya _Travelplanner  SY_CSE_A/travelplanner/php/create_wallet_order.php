<?php
// create_wallet_order.php - Create Razorpay order for wallet top-up
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Log the start of the script
error_log("create_wallet_order.php: Script started");

try {
    require_once 'config.php';
    error_log("create_wallet_order.php: config.php loaded successfully");
} catch (Exception $e) {
    error_log("create_wallet_order.php: Error loading config.php: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Configuration error']);
    exit();
}

try {
    require_once 'razorpay_config.php';
    error_log("create_wallet_order.php: razorpay_config.php loaded successfully");
} catch (Exception $e) {
    error_log("create_wallet_order.php: Error loading razorpay_config.php: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Razorpay configuration error']);
    exit();
}

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
$realAmount = floatval($input['amount'] ?? 0); // The actual amount for reference
$amount = 1; // Always charge ₹1 for demo/testing

if ($realAmount <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid amount']);
    exit();
}

$userId = $_SESSION['user_id'];

try {
    error_log("create_wallet_order.php: Creating RazorpayService instance");
    // Use the RazorpayService class
    $razorpayService = new RazorpayService();
    
    error_log("create_wallet_order.php: Creating order for amount: " . $amount);
    // Create order for wallet top-up
    $order = $razorpayService->createOrder($amount);
    
    error_log("create_wallet_order.php: Order created successfully: " . $order->id);
    
    // Store order in database with user_id and NULL booking_id for wallet top-ups
    $stmt = $conn->prepare("INSERT INTO payment_orders (user_id, booking_id, razorpay_order_id, amount, status, payment_method, created_at, original_amount) VALUES (?, NULL, ?, ?, 'pending', 'razorpay', NOW(), ?)");
    $stmt->bind_param("isdd", $userId, $order->id, $amount, $realAmount);
    $stmt->execute();
    
    error_log("create_wallet_order.php: Order stored in database");
    
    echo json_encode([
        'status' => 'success',
        'order_id' => $order->id,
        'key_id' => $key_id, // This is defined in razorpay_config.php
        'amount' => $amount,
        'original_amount' => $realAmount
    ]);
    
} catch (Exception $e) {
    error_log("create_wallet_order.php: Error creating order: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Error creating order: ' . $e->getMessage()]);
}
?> 