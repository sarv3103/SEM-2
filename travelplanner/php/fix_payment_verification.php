<?php
// Fix Payment Verification Issues
session_start();
require_once 'config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit();
}

$fixes_applied = [];

// Fix 1: Create payment_orders table if it doesn't exist
$create_payment_orders = "
CREATE TABLE IF NOT EXISTS payment_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    razorpay_order_id VARCHAR(255) NOT NULL,
    razorpay_payment_id VARCHAR(255) NULL,
    amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    payment_date TIMESTAMP NULL,
    webhook_processed TINYINT(1) DEFAULT 0,
    INDEX idx_razorpay_order_id (razorpay_order_id),
    INDEX idx_booking_id (booking_id)
)";

if ($conn->query($create_payment_orders)) {
    $fixes_applied[] = "✅ Payment orders table created/verified";
} else {
    $fixes_applied[] = "❌ Failed to create payment orders table: " . $conn->error;
}

// Fix 2: Add missing columns to bookings table
$columns_to_add = [
    'payment_status' => "ALTER TABLE bookings ADD COLUMN IF NOT EXISTS payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending'",
    'payment_date' => "ALTER TABLE bookings ADD COLUMN IF NOT EXISTS payment_date TIMESTAMP NULL",
    'razorpay_payment_id' => "ALTER TABLE bookings ADD COLUMN IF NOT EXISTS razorpay_payment_id VARCHAR(255) NULL",
    'ticket_sent' => "ALTER TABLE bookings ADD COLUMN IF NOT EXISTS ticket_sent TINYINT(1) DEFAULT 0",
    'webhook_processed' => "ALTER TABLE bookings ADD COLUMN IF NOT EXISTS webhook_processed TINYINT(1) DEFAULT 0"
];

foreach ($columns_to_add as $column => $sql) {
    if ($conn->query($sql)) {
        $fixes_applied[] = "✅ Added/verified column: $column";
    } else {
        $fixes_applied[] = "❌ Failed to add column $column: " . $conn->error;
    }
}

// Fix 3: Create wallet_transactions table if it doesn't exist
$create_wallet_transactions = "
CREATE TABLE IF NOT EXISTS wallet_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    type ENUM('credit', 'debit') NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id)
)";

if ($conn->query($create_wallet_transactions)) {
    $fixes_applied[] = "✅ Wallet transactions table created/verified";
} else {
    $fixes_applied[] = "❌ Failed to create wallet transactions table: " . $conn->error;
}

// Fix 4: Update existing bookings without payment_status
$update_bookings = "UPDATE bookings SET payment_status = 'pending' WHERE payment_status IS NULL";
if ($conn->query($update_bookings)) {
    $fixes_applied[] = "✅ Updated existing bookings with default payment status";
} else {
    $fixes_applied[] = "❌ Failed to update bookings: " . $conn->error;
}

// Fix 5: Check for orphaned payment orders
$check_orphaned = "SELECT COUNT(*) as count FROM payment_orders po LEFT JOIN bookings b ON po.booking_id = b.id WHERE b.id IS NULL";
$result = $conn->query($check_orphaned);
$orphaned_count = $result->fetch_assoc()['count'];
if ($orphaned_count > 0) {
    $fixes_applied[] = "⚠️ Found $orphaned_count orphaned payment orders (bookings deleted)";
} else {
    $fixes_applied[] = "✅ No orphaned payment orders found";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Fix Payment Verification</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h2>Payment Verification Fixes Applied</h2>
        
        <div class="card">
            <div class="card-header">Database Fixes</div>
            <div class="card-body">
                <?php foreach ($fixes_applied as $fix): ?>
                    <div class="mb-2"><?= $fix ?></div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="mt-4">
            <h4>Next Steps:</h4>
            <ol>
                <li>Test payment verification using the debug tool</li>
                <li>Check Razorpay API keys in razorpay_config.php</li>
                <li>Configure webhook URL in Razorpay dashboard</li>
                <li>Test with a real payment ID</li>
            </ol>
        </div>
        
        <div class="mt-3">
            <a href="payment_debug.php" class="btn btn-primary">Run Debug Tool</a>
            <a href="admin_verify_payment.php" class="btn btn-secondary">Go to Payment Verification</a>
        </div>
    </div>
</body>
</html> 