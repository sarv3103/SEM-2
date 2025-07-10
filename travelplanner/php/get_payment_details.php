<?php
session_start();
require_once 'config.php';
require_once 'session.php';

// Only allow admin access
requireAdmin();

$paymentId = $_GET['payment_id'] ?? '';
if (!$paymentId) {
    echo '<div class="alert alert-warning">No payment ID provided.</div>';
    exit;
}

try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }

    // Get payment details with user information
    $stmt = $conn->prepare("
        SELECT 
            po.*,
            u.username,
            u.email as user_email,
            u.mobile as user_mobile,
            u.name as user_name,
            CASE 
                WHEN po.order_id LIKE 'booking_%' THEN 'Booking Payment'
                WHEN po.order_id LIKE 'wallet_%' THEN 'Wallet Top-up'
                WHEN po.order_id LIKE 'refund_%' THEN 'Refund'
                ELSE 'Other'
            END as payment_type
        FROM payment_orders po
        LEFT JOIN users u ON po.user_id = u.id
        WHERE po.id = ?
    ");
    
    $stmt->bind_param("i", $paymentId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo '<div class="alert alert-warning">Payment not found.</div>';
        exit;
    }
    
    $payment = $result->fetch_assoc();
    
    // Get related booking if this is a booking payment
    $bookingDetails = '';
    if (strpos($payment['order_id'], 'booking_') === 0) {
        $bookingId = str_replace('booking_', '', $payment['order_id']);
        $bookingStmt = $conn->prepare("
            SELECT * FROM bookings WHERE id = ?
        ");
        $bookingStmt->bind_param("i", $bookingId);
        $bookingStmt->execute();
        $bookingResult = $bookingStmt->get_result();
        
        if ($bookingResult->num_rows > 0) {
            $booking = $bookingResult->fetch_assoc();
            $bookingDetails = "
                <div class='row'>
                    <div class='col-md-6'>
                        <strong>Booking ID:</strong> {$booking['id']}<br>
                        <strong>Destination:</strong> {$booking['to']}<br>
                        <strong>Travel Date:</strong> {$booking['dates']}<br>
                        <strong>Travelers:</strong> {$booking['travelers']}
                    </div>
                    <div class='col-md-6'>
                        <strong>From:</strong> {$booking['from']}<br>
                        <strong>Category:</strong> {$booking['category']}<br>
                        <strong>Status:</strong> {$booking['status']}<br>
                        <strong>Payment Status:</strong> {$booking['payment_status']}
                    </div>
                </div>
            ";
        }
    }
    
    // Format dates
    $createdDate = $payment['created_at'] ? date('d M Y H:i', strtotime($payment['created_at'])) : 'N/A';
    $updatedDate = $payment['updated_at'] ? date('d M Y H:i', strtotime($payment['updated_at'])) : 'N/A';
    
    // Status badge
    $statusBadge = ($payment['status'] === 'completed') ? 'success' : 
                   (($payment['status'] === 'pending') ? 'warning' : 'danger');
    
    echo "
    <div class='container-fluid'>
        <div class='row mb-3'>
            <div class='col-md-6'>
                <h6 class='text-primary'>Payment Information</h6>
                <p><strong>Payment ID:</strong> {$payment['payment_id']}</p>
                <p><strong>Order ID:</strong> {$payment['order_id']}</p>
                <p><strong>Amount:</strong> ₹" . number_format($payment['amount']) . "</p>
                <p><strong>Status:</strong> <span class='badge bg-{$statusBadge}'>{$payment['status']}</span></p>
                <p><strong>Payment Method:</strong> {$payment['payment_method']}</p>
            </div>
            <div class='col-md-6'>
                <h6 class='text-primary'>User Information</h6>
                <p><strong>Username:</strong> {$payment['username']}</p>
                <p><strong>Name:</strong> {$payment['user_name']}</p>
                <p><strong>Email:</strong> {$payment['user_email']}</p>
                <p><strong>Mobile:</strong> {$payment['user_mobile']}</p>
                <p><strong>Payment Type:</strong> {$payment['payment_type']}</p>
            </div>
        </div>
        
        <div class='row mb-3'>
            <div class='col-md-6'>
                <h6 class='text-primary'>Timestamps</h6>
                <p><strong>Created:</strong> {$createdDate}</p>
                <p><strong>Last Updated:</strong> {$updatedDate}</p>
            </div>
            <div class='col-md-6'>
                <h6 class='text-primary'>Additional Information</h6>
                <p><strong>Reference:</strong> {$payment['reference']}</p>
                <p><strong>Remarks:</strong> {$payment['remarks']}</p>
            </div>
        </div>
        
        " . ($bookingDetails ? "
        <div class='row mb-3'>
            <div class='col-12'>
                <h6 class='text-primary'>Related Booking Details</h6>
                {$bookingDetails}
            </div>
        </div>
        " : "") . "
        
        <div class='alert alert-info'>
            <strong>Note:</strong> This payment record contains all transaction details. Use the action buttons below to manage this payment.
        </div>
    </div>
    ";

} catch (Exception $e) {
    echo '<div class="alert alert-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
}

if (isset($conn)) {
    $conn->close();
}
?> 