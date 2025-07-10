<?php
session_start();
require_once 'config.php';
require_once 'razorpay_config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit();
}

$message = '';
$error = '';

// Handle payment verification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $paymentId = $_POST['payment_id'] ?? '';
    $orderId = $_POST['order_id'] ?? '';
    
    if ($paymentId && $orderId) {
        try {
            $razorpay = new RazorpayService();
            $payment = $razorpay->getPaymentDetails($paymentId);
            
            if ($payment && $payment['status'] === 'captured') {
                // Update payment order status
                $stmt = $conn->prepare("
                    UPDATE payment_orders 
                    SET status = 'completed', 
                        razorpay_payment_id = ?, 
                        payment_date = NOW()
                    WHERE razorpay_order_id = ?
                ");
                $stmt->bind_param("ss", $paymentId, $orderId);
                $stmt->execute();
                
                // Get booking ID
                $stmt = $conn->prepare("SELECT booking_id FROM payment_orders WHERE razorpay_order_id = ?");
                $stmt->bind_param("s", $orderId);
                $stmt->execute();
                $result = $stmt->get_result();
                $orderData = $result->fetch_assoc();
                
                if ($orderData) {
                    $bookingId = $orderData['booking_id'];
                    
                    // Update booking payment status
                    $stmt = $conn->prepare("
                        UPDATE bookings 
                        SET payment_status = 'paid', 
                            payment_date = NOW()
                        WHERE id = ?
                    ");
                    $stmt->bind_param("i", $bookingId);
                    $stmt->execute();
                    
                    $stmt = $conn->prepare("SELECT * FROM bookings WHERE id = ?");
                    $stmt->bind_param("i", $bookingId);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $booking = $result->fetch_assoc();
                    if ($booking) {
                        $ticketSent = $booking['ticket_sent'] ?? 0;
                        if (!$ticketSent) {
                            $ticketData = generateTicket($booking, $paymentId);
                            $emailSent = sendTicketEmail($booking, $ticketData['pdf'], $paymentId);
                            $stmt = $conn->prepare("UPDATE bookings SET ticket_sent = 1 WHERE id = ?");
                            $stmt->bind_param("i", $bookingId);
                            $stmt->execute();
                        } else {
                            $emailSent = true;
                        }
                        $message = "✅ Payment verified successfully! Booking ID: " . $booking['booking_id'] . ($emailSent ? " Ticket sent to email." : " Email sending failed.");
                    }
                }
            } else {
                $error = "Payment not captured. Status: " . ($payment['status'] ?? 'Unknown');
            }
        } catch (Exception $e) {
            $error = "Error verifying payment: " . $e->getMessage();
        }
    } else {
        $error = "Payment ID and Order ID are required.";
    }
}

// Get pending payments
$stmt = $conn->prepare("
    SELECT po.*, b.booking_id, b.name, b.contact_email, b.destination, b.fare
    FROM payment_orders po
    JOIN bookings b ON po.booking_id = b.id
    WHERE po.status = 'pending' OR po.status = 'failed'
    ORDER BY po.created_at DESC
    LIMIT 20
");
$stmt->execute();
$pendingPayments = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Verification</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1>Payment Verification</h1>
        
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Verify Payment</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label for="payment_id" class="form-label">Payment ID</label>
                                <input type="text" class="form-control" id="payment_id" name="payment_id" required>
                            </div>
                            <div class="mb-3">
                                <label for="order_id" class="form-label">Order ID</label>
                                <input type="text" class="form-control" id="order_id" name="order_id" required>
                            </div>
                            <button type="submit" class="btn btn-success">Verify Payment</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h5>Pending/Failed Payments</h5>
            </div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Booking ID</th>
                            <th>Customer</th>
                            <th>Destination</th>
                            <th>Amount</th>
                            <th>Order ID</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($payment = $pendingPayments->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($payment['booking_id']); ?></td>
                            <td><?php echo htmlspecialchars($payment['name']); ?></td>
                            <td><?php echo htmlspecialchars($payment['destination']); ?></td>
                            <td>₹<?php echo number_format($payment['fare']); ?></td>
                            <td><?php echo htmlspecialchars($payment['razorpay_order_id']); ?></td>
                            <td><?php echo ucfirst($payment['status']); ?></td>
                            <td>
                                <button class="btn btn-sm btn-info" onclick="fillForm('<?php echo $payment['razorpay_order_id']; ?>')">
                                    Verify
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function fillForm(orderId) {
            document.getElementById('order_id').value = orderId;
            document.getElementById('payment_id').focus();
        }
    </script>
</body>
</html> 