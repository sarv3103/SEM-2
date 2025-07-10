<?php
// Payment Verification Diagnostic Tool
// This script will help identify and fix payment verification issues

session_start();
require_once 'config.php';
require_once 'razorpay_config.php';
require_once '../vendor/autoload.php';

use Razorpay\Api\Api;

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit();
}

$diagnostic_results = [];
$fixes_applied = [];

// Function to run diagnostics
function runDiagnostics($conn, $key_id, $key_secret) {
    global $diagnostic_results;
    
    // Test 1: Database Connection
    $diagnostic_results['database'] = [
        'status' => 'checking',
        'message' => 'Testing database connection...'
    ];
    
    if ($conn->connect_error) {
        $diagnostic_results['database'] = [
            'status' => 'error',
            'message' => 'Database connection failed: ' . $conn->connect_error
        ];
    } else {
        $diagnostic_results['database'] = [
            'status' => 'success',
            'message' => 'Database connection successful'
        ];
    }
    
    // Test 2: Razorpay API Connection
    $diagnostic_results['razorpay_api'] = [
        'status' => 'checking',
        'message' => 'Testing Razorpay API connection...'
    ];
    
    try {
        $api = new Api($key_id, $key_secret);
        // Try to fetch a test payment (this will fail but verify API connection)
        $api->payment->fetch('test_payment_id');
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'No such payment') !== false) {
            $diagnostic_results['razorpay_api'] = [
                'status' => 'success',
                'message' => 'Razorpay API connection successful (expected error for test payment)'
            ];
        } else {
            $diagnostic_results['razorpay_api'] = [
                'status' => 'error',
                'message' => 'Razorpay API connection failed: ' . $e->getMessage()
            ];
        }
    }
    
    // Test 3: Required Database Tables
    $diagnostic_results['database_tables'] = [
        'status' => 'checking',
        'message' => 'Checking required database tables...'
    ];
    
    $required_tables = ['bookings', 'payment_orders', 'users', 'wallet_transactions'];
    $missing_tables = [];
    
    foreach ($required_tables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result->num_rows === 0) {
            $missing_tables[] = $table;
        }
    }
    
    if (empty($missing_tables)) {
        $diagnostic_results['database_tables'] = [
            'status' => 'success',
            'message' => 'All required tables exist'
        ];
    } else {
        $diagnostic_results['database_tables'] = [
            'status' => 'error',
            'message' => 'Missing tables: ' . implode(', ', $missing_tables)
        ];
    }
    
    // Test 4: Check for pending payments
    $diagnostic_results['pending_payments'] = [
        'status' => 'checking',
        'message' => 'Checking for pending payments...'
    ];
    
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count 
        FROM payment_orders 
        WHERE status = 'pending' OR status = 'failed'
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    $pending_count = $result->fetch_assoc()['count'];
    
    $diagnostic_results['pending_payments'] = [
        'status' => 'info',
        'message' => "Found $pending_count pending/failed payments"
    ];
    
    // Test 5: Check webhook configuration
    $diagnostic_results['webhook_config'] = [
        'status' => 'checking',
        'message' => 'Checking webhook configuration...'
    ];
    
    // Check if webhook secret is set
    $webhook_secret = 'your_webhook_secret_here';
    if ($webhook_secret === 'your_webhook_secret_here') {
        $diagnostic_results['webhook_config'] = [
            'status' => 'warning',
            'message' => 'Webhook secret is not configured (using default value)'
        ];
    } else {
        $diagnostic_results['webhook_config'] = [
            'status' => 'success',
            'message' => 'Webhook secret is configured'
        ];
    }
    
    // Test 6: Check file permissions
    $diagnostic_results['file_permissions'] = [
        'status' => 'checking',
        'message' => 'Checking file permissions...'
    ];
    
    $files_to_check = [
        'verify_payment.php',
        'razorpay_webhook.php',
        'admin_verify_payment.php'
    ];
    
    $permission_issues = [];
    foreach ($files_to_check as $file) {
        if (!file_exists($file)) {
            $permission_issues[] = "$file not found";
        } elseif (!is_readable($file)) {
            $permission_issues[] = "$file not readable";
        }
    }
    
    if (empty($permission_issues)) {
        $diagnostic_results['file_permissions'] = [
            'status' => 'success',
            'message' => 'All payment verification files are accessible'
        ];
    } else {
        $diagnostic_results['file_permissions'] = [
            'status' => 'error',
            'message' => 'File permission issues: ' . implode(', ', $permission_issues)
        ];
    }
}

// Function to apply fixes
function applyFixes($conn) {
    global $fixes_applied;
    
    // Fix 1: Create missing tables if they don't exist
    $fixes_applied['create_tables'] = [
        'status' => 'checking',
        'message' => 'Creating missing tables...'
    ];
    
    // Create payment_orders table if it doesn't exist
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
        )
    ";
    
    if ($conn->query($create_payment_orders)) {
        $fixes_applied['create_tables'] = [
            'status' => 'success',
            'message' => 'Payment orders table created/verified'
        ];
    } else {
        $fixes_applied['create_tables'] = [
            'status' => 'error',
            'message' => 'Failed to create payment orders table: ' . $conn->error
        ];
    }
    
    // Fix 2: Add missing columns to bookings table
    $fixes_applied['add_booking_columns'] = [
        'status' => 'checking',
        'message' => 'Adding missing columns to bookings table...'
    ];
    
    $columns_to_add = [
        'payment_status' => "ALTER TABLE bookings ADD COLUMN IF NOT EXISTS payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending'",
        'payment_date' => "ALTER TABLE bookings ADD COLUMN IF NOT EXISTS payment_date TIMESTAMP NULL",
        'razorpay_payment_id' => "ALTER TABLE bookings ADD COLUMN IF NOT EXISTS razorpay_payment_id VARCHAR(255) NULL",
        'ticket_sent' => "ALTER TABLE bookings ADD COLUMN IF NOT EXISTS ticket_sent TINYINT(1) DEFAULT 0",
        'webhook_processed' => "ALTER TABLE bookings ADD COLUMN IF NOT EXISTS webhook_processed TINYINT(1) DEFAULT 0"
    ];
    
    $columns_added = 0;
    foreach ($columns_to_add as $column => $sql) {
        if ($conn->query($sql)) {
            $columns_added++;
        }
    }
    
    $fixes_applied['add_booking_columns'] = [
        'status' => 'success',
        'message' => "Added/verified $columns_added columns to bookings table"
    ];
    
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
        )
    ";
    
    if ($conn->query($create_wallet_transactions)) {
        $fixes_applied['wallet_transactions'] = [
            'status' => 'success',
            'message' => 'Wallet transactions table created/verified'
        ];
    } else {
        $fixes_applied['wallet_transactions'] = [
            'status' => 'error',
            'message' => 'Failed to create wallet transactions table: ' . $conn->error
        ];
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'run_diagnostics':
                runDiagnostics($conn, $key_id, $key_secret);
                break;
            case 'apply_fixes':
                applyFixes($conn);
                break;
            case 'test_payment_verification':
                $test_payment_id = $_POST['test_payment_id'] ?? '';
                $test_order_id = $_POST['test_order_id'] ?? '';
                
                if ($test_payment_id && $test_order_id) {
                    try {
                        $api = new Api($key_id, $key_secret);
                        $payment = $api->payment->fetch($test_payment_id);
                        $diagnostic_results['test_verification'] = [
                            'status' => 'success',
                            'message' => 'Payment found: ' . $payment['status']
                        ];
                    } catch (Exception $e) {
                        $diagnostic_results['test_verification'] = [
                            'status' => 'error',
                            'message' => 'Payment verification test failed: ' . $e->getMessage()
                        ];
                    }
                }
                break;
        }
    }
}

// Run initial diagnostics
if (empty($diagnostic_results)) {
    runDiagnostics($conn, $key_id, $key_secret);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Verification Diagnostic Tool</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .status-success { color: #28a745; }
        .status-error { color: #dc3545; }
        .status-warning { color: #ffc107; }
        .status-info { color: #17a2b8; }
        .status-checking { color: #6c757d; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1>Payment Verification Diagnostic Tool</h1>
        <p class="text-muted">This tool helps identify and fix payment verification issues.</p>
        
        <div class="row mb-4">
            <div class="col-md-6">
                <form method="POST" class="d-inline">
                    <input type="hidden" name="action" value="run_diagnostics">
                    <button type="submit" class="btn btn-primary">Run Diagnostics</button>
                </form>
            </div>
            <div class="col-md-6">
                <form method="POST" class="d-inline">
                    <input type="hidden" name="action" value="apply_fixes">
                    <button type="submit" class="btn btn-success">Apply Fixes</button>
                </form>
            </div>
        </div>
        
        <!-- Diagnostic Results -->
        <div class="card mb-4">
            <div class="card-header">
                <h5>Diagnostic Results</h5>
            </div>
            <div class="card-body">
                <?php foreach ($diagnostic_results as $test => $result): ?>
                    <div class="mb-3">
                        <strong><?= ucwords(str_replace('_', ' ', $test)) ?>:</strong>
                        <span class="status-<?= $result['status'] ?>"><?= $result['message'] ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Fixes Applied -->
        <?php if (!empty($fixes_applied)): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Fixes Applied</h5>
                </div>
                <div class="card-body">
                    <?php foreach ($fixes_applied as $fix => $result): ?>
                        <div class="mb-3">
                            <strong><?= ucwords(str_replace('_', ' ', $fix)) ?>:</strong>
                            <span class="status-<?= $result['status'] ?>"><?= $result['message'] ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Test Payment Verification -->
        <div class="card">
            <div class="card-header">
                <h5>Test Payment Verification</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="test_payment_verification">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="test_payment_id" class="form-label">Test Payment ID</label>
                                <input type="text" class="form-control" id="test_payment_id" name="test_payment_id" placeholder="Enter a real Razorpay payment ID">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="test_order_id" class="form-label">Test Order ID</label>
                                <input type="text" class="form-control" id="test_order_id" name="test_order_id" placeholder="Enter a real Razorpay order ID">
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-info">Test Verification</button>
                </form>
            </div>
        </div>
        
        <!-- Common Issues and Solutions -->
        <div class="card mt-4">
            <div class="card-header">
                <h5>Common Issues and Solutions</h5>
            </div>
            <div class="card-body">
                <div class="accordion" id="issuesAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#issue1">
                                Payment verification returns "Payment not found"
                            </button>
                        </h2>
                        <div id="issue1" class="accordion-collapse collapse" data-bs-parent="#issuesAccordion">
                            <div class="accordion-body">
                                <strong>Cause:</strong> Incorrect Razorpay API keys or payment ID format.<br>
                                <strong>Solution:</strong> 
                                <ul>
                                    <li>Verify your Razorpay API keys in razorpay_config.php</li>
                                    <li>Ensure you're using the correct environment (test/live)</li>
                                    <li>Check that the payment ID is valid and exists in your Razorpay dashboard</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#issue2">
                                Database errors during payment verification
                            </button>
                        </h2>
                        <div id="issue2" class="accordion-collapse collapse" data-bs-parent="#issuesAccordion">
                            <div class="accordion-body">
                                <strong>Cause:</strong> Missing database tables or columns.<br>
                                <strong>Solution:</strong> 
                                <ul>
                                    <li>Run the "Apply Fixes" button above to create missing tables</li>
                                    <li>Check database connection settings in config.php</li>
                                    <li>Ensure the database user has proper permissions</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#issue3">
                                Webhook not processing payments
                            </button>
                        </h2>
                        <div id="issue3" class="accordion-collapse collapse" data-bs-parent="#issuesAccordion">
                            <div class="accordion-body">
                                <strong>Cause:</strong> Webhook URL not configured or webhook secret mismatch.<br>
                                <strong>Solution:</strong> 
                                <ul>
                                    <li>Configure webhook URL in Razorpay dashboard: yourdomain.com/php/razorpay_webhook.php</li>
                                    <li>Update webhook secret in razorpay_config.php</li>
                                    <li>Ensure webhook endpoint is accessible from internet</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 