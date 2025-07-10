<?php
// Simple Payment Verification Debug Script
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

$debug_results = [];

// Test 1: Database Connection
$debug_results['database'] = $conn->connect_error ? 'FAILED: ' . $conn->connect_error : 'SUCCESS';

// Test 2: Razorpay API Keys
$debug_results['razorpay_keys'] = 'Key ID: ' . substr($key_id, 0, 10) . '... | Key Secret: ' . substr($key_secret, 0, 10) . '...';

// Test 3: Required Tables
$tables = ['bookings', 'payment_orders', 'users'];
$missing_tables = [];
foreach ($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result->num_rows === 0) {
        $missing_tables[] = $table;
    }
}
$debug_results['tables'] = empty($missing_tables) ? 'SUCCESS' : 'MISSING: ' . implode(', ', $missing_tables);

// Test 4: Pending Payments
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM payment_orders WHERE status = 'pending' OR status = 'failed'");
$stmt->execute();
$result = $stmt->get_result();
$pending_count = $result->fetch_assoc()['count'];
$debug_results['pending_payments'] = "Found $pending_count pending/failed payments";

// Handle test verification
if ($_POST && isset($_POST['test_payment_id'])) {
    try {
        $api = new Api($key_id, $key_secret);
        $payment = $api->payment->fetch($_POST['test_payment_id']);
        $debug_results['test_result'] = 'SUCCESS: Payment found - Status: ' . $payment['status'];
    } catch (Exception $e) {
        $debug_results['test_result'] = 'ERROR: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Payment Debug</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h2>Payment Verification Debug</h2>
        
        <div class="card mb-4">
            <div class="card-header">System Status</div>
            <div class="card-body">
                <?php foreach ($debug_results as $test => $result): ?>
                    <div class="mb-2">
                        <strong><?= ucwords(str_replace('_', ' ', $test)) ?>:</strong> 
                        <span class="<?= strpos($result, 'SUCCESS') !== false ? 'text-success' : (strpos($result, 'ERROR') !== false ? 'text-danger' : 'text-info') ?>">
                            <?= $result ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">Test Payment Verification</div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label>Payment ID:</label>
                        <input type="text" name="test_payment_id" class="form-control" placeholder="Enter Razorpay payment ID">
                    </div>
                    <button type="submit" class="btn btn-primary">Test</button>
                </form>
            </div>
        </div>
        
        <div class="mt-4">
            <h4>Common Issues:</h4>
            <ul>
                <li><strong>Payment not found:</strong> Check API keys and payment ID format</li>
                <li><strong>Database errors:</strong> Run database setup scripts</li>
                <li><strong>Webhook issues:</strong> Configure webhook URL in Razorpay dashboard</li>
            </ul>
        </div>
    </div>
</body>
</html> 