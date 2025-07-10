<?php
session_start();
require_once 'config.php';
require_once 'razorpay_config.php';
require_once '../vendor/autoload.php';

use Mpdf\Mpdf;

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit();
}

$message = '';
$error = '';
$paymentDetails = null;

// Handle payment verification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'check_payment':
                $paymentId = $_POST['payment_id'] ?? '';
                if ($paymentId) {
                    try {
                        $razorpay = new RazorpayService();
                        $payment = $razorpay->getPaymentDetails($paymentId);
                        if ($payment) {
                            $paymentDetails = $payment;
                            $message = "Payment found! Status: " . $payment['status'];
                        } else {
                            $error = "Payment not found.";
                        }
                    } catch (Exception $e) {
                        $error = "Error checking payment: " . $e->getMessage();
                    }
                }
                break;
                
            case 'verify_and_process':
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
                                    payment_date = NOW(),
                                    webhook_processed = 0
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
                                        payment_date = NOW(),
                                        webhook_processed = 0
                                    WHERE id = ?
                                ");
                                $stmt->bind_param("i", $bookingId);
                                $stmt->execute();
                                
                                // Get booking details
                                $stmt = $conn->prepare("
                                    SELECT b.*, GROUP_CONCAT(td.name ORDER BY td.traveler_number) as traveler_names
                                    FROM bookings b
                                    LEFT JOIN traveler_details td ON b.id = td.booking_id
                                    WHERE b.id = ?
                                    GROUP BY b.id
                                ");
                                $stmt->bind_param("i", $bookingId);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                $booking = $result->fetch_assoc();
                                
                                if ($booking) {
                                    $ticketSent = $booking['ticket_sent'] ?? 0;
                                    if (!$ticketSent) {
                                        // Generate and send ticket
                                        $ticketData = generateTicket($booking, $paymentId);
                                        $emailSent = sendTicketEmail($booking, $ticketData['pdf'], $paymentId);
                                        // Set ticket_sent = 1
                                        $stmt = $conn->prepare("UPDATE bookings SET ticket_sent = 1 WHERE id = ?");
                                        $stmt->bind_param("i", $bookingId);
                                        $stmt->execute();
                                    } else {
                                        $emailSent = true; // Already sent
                                    }
                                    $message = "✅ Payment verified and processed successfully!<br>";
                                    $message .= "Booking ID: " . $booking['booking_id'] . "<br>";
                                    $message .= "Amount: ₹" . ($payment['amount'] / 100) . "<br>";
                                    $message .= "Email sent: " . ($emailSent ? "Yes" : "No");
                                }
                            }
                        } else {
                            $error = "Payment not captured. Status: " . ($payment['status'] ?? 'Unknown');
                        }
                    } catch (Exception $e) {
                        $error = "Error processing payment: " . $e->getMessage();
                    }
                } else {
                    $error = "Payment ID and Order ID are required.";
                }
                break;
        }
    }
}

// Get all pending/failed payments
$stmt = $conn->prepare("
    SELECT po.*, b.booking_id, b.name, b.contact_email, b.contact_mobile, b.destination, b.fare, b.start_date, b.num_travelers
    FROM payment_orders po
    JOIN bookings b ON po.booking_id = b.id
    WHERE po.status = 'pending' OR po.status = 'failed'
    ORDER BY po.created_at DESC
    LIMIT 50
");
$stmt->execute();
$pendingPayments = $stmt->get_result();

// Get recent successful payments
$stmt = $conn->prepare("
    SELECT po.*, b.booking_id, b.name, b.contact_email, b.contact_mobile, b.destination, b.fare, b.start_date, b.num_travelers
    FROM payment_orders po
    JOIN bookings b ON po.booking_id = b.id
    WHERE po.status = 'completed'
    ORDER BY po.payment_date DESC
    LIMIT 20
");
$stmt->execute();
$completedPayments = $stmt->get_result();

function generateTicket($booking, $paymentId) {
    $mpdf = new Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4',
        'margin_left' => 15,
        'margin_right' => 15,
        'margin_top' => 15,
        'margin_bottom' => 15
    ]);
    
    $html = generateTicketHTML($booking, $paymentId);
    $mpdf->WriteHTML($html);
    
    return [
        'html' => $html,
                    'pdf' => $mpdf->Output('', \Mpdf\Output\Destination::STRING_RETURN)
    ];
}

function generateTicketHTML($booking, $paymentId) {
    $bookingType = $booking['booking_type'] ?? 'Travel';
    $destinationName = $booking['destination_name'] ?? $booking['destination'];
    $startDate = $booking['start_date'] ?? $booking['date'];
    $endDate = $booking['end_date'] ?? $booking['date'];
    $numTravelers = $booking['num_travelers'];
    $travelStyle = $booking['travel_style'] ?? 'Standard';
    $contactMobile = $booking['contact_mobile'];
    $contactEmail = $booking['contact_email'];
    $totalAmount = $booking['fare'];
    $duration = $booking['duration'] ?? 1;
    
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>TravelPlanner - Booking Confirmation</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
            .ticket-container { max-width: 800px; margin: 0 auto; background: white; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); overflow: hidden; }
            .ticket-header { background: linear-gradient(135deg, #0077cc, #2193b0); color: white; padding: 30px; text-align: center; }
            .ticket-header h1 { margin: 0; font-size: 28px; font-weight: bold; }
            .ticket-header p { margin: 10px 0 0 0; opacity: 0.9; }
            .ticket-body { padding: 30px; }
            .ticket-section { margin-bottom: 25px; }
            .ticket-section h3 { color: #0077cc; border-bottom: 2px solid #0077cc; padding-bottom: 8px; margin-bottom: 15px; }
            .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px; }
            .info-item { background: #f8f9fa; padding: 12px; border-radius: 8px; border-left: 4px solid #0077cc; }
            .info-label { font-weight: bold; color: #333; margin-bottom: 5px; }
            .info-value { color: #666; }
            .fare-summary { background: linear-gradient(135deg, #28a745, #20c997); color: white; padding: 20px; border-radius: 10px; text-align: center; }
            .fare-summary h3 { margin: 0 0 10px 0; }
            .fare-amount { font-size: 24px; font-weight: bold; }
            .ticket-footer { background: #333; color: white; padding: 20px; text-align: center; }
            .ticket-footer p { margin: 5px 0; }
            .guidelines { background: #fff3cd; padding: 20px; border-radius: 10px; margin: 20px 0; }
            .guidelines h4 { color: #856404; margin-bottom: 15px; }
            .guidelines ul { color: #856404; margin: 0; padding-left: 20px; }
            .qr-placeholder { width: 100px; height: 100px; background: #e0e0e0; margin: 20px auto; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #666; font-size: 12px; }
            .payment-info { background: #e8f5e8; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #28a745; }
            .manual-notice { background: #d1ecf1; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #17a2b8; }
        </style>
    </head>
    <body>
        <div class="ticket-container">
            <div class="ticket-header">
                <h1>✈️ TravelPlanner</h1>
                <p>Official Booking Confirmation</p>
                <div class="qr-placeholder">QR Code</div>
            </div>
            
            <div class="ticket-body">
                <div class="payment-info">
                    <strong>✅ Payment Confirmed (Manually Verified)</strong><br>
                    Payment ID: ' . $paymentId . '<br>
                    Amount Paid: ₹1 (Test Payment)<br>
                    Original Amount: ₹' . number_format($totalAmount) . '<br>
                    Verified by Admin
                </div>
                
                <div class="manual-notice">
                    <strong>👨‍💼 Manual Verification</strong><br>
                    This booking was manually verified by our admin team. Your payment was successful and has been confirmed.
                </div>
                
                <div class="ticket-section">
                    <h3>📋 Booking Information</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">Booking ID</div>
                            <div class="info-value">' . $booking['booking_id'] . '</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Booking Type</div>
                            <div class="info-value">' . ucfirst($bookingType) . '</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Destination</div>
                            <div class="info-value">' . htmlspecialchars($destinationName) . '</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Travel Style</div>
                            <div class="info-value">' . ucfirst($travelStyle) . '</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Start Date</div>
                            <div class="info-value">' . $startDate . '</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">End Date</div>
                            <div class="info-value">' . $endDate . '</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Duration</div>
                            <div class="info-value">' . $duration . ' day(s)</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Number of Travelers</div>
                            <div class="info-value">' . $numTravelers . '</div>
                        </div>
                    </div>
                </div>
                
                <div class="ticket-section">
                    <h3>👥 Traveler Information</h3>
                    <div class="info-item">
                        <div class="info-label">Primary Traveler</div>
                        <div class="info-value">' . htmlspecialchars($booking['name']) . ' (Age: ' . $booking['age'] . ', Gender: ' . ucfirst($booking['gender']) . ')</div>
                    </div>
                    ' . ($booking['traveler_names'] ? '<div class="info-item"><div class="info-label">All Travelers</div><div class="info-value">' . htmlspecialchars($booking['traveler_names']) . '</div></div>' : '') . '
                </div>
                
                <div class="ticket-section">
                    <h3>📞 Contact Information</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">Mobile</div>
                            <div class="info-value">' . $contactMobile . '</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Email</div>
                            <div class="info-value">' . $contactEmail . '</div>
                        </div>
                    </div>
                </div>
                
                <div class="fare-summary">
                    <h3>💰 Fare Summary</h3>
                    <div class="fare-amount">₹' . number_format($totalAmount) . '</div>
                    <p>Per Person: ₹' . number_format($booking['per_person'] ?? $totalAmount / $numTravelers) . '</p>
                </div>
                
                <div class="guidelines">
                    <h4>📋 Important Guidelines</h4>
                    <ul>
                        <li>Please carry a valid ID proof for all travelers</li>
                        <li>Arrive at least 2 hours before departure for international flights</li>
                        <li>Arrive at least 1 hour before departure for domestic flights</li>
                        <li>Keep this ticket handy during your journey</li>
                        <li>Contact our support team for any assistance</li>
                    </ul>
                </div>
            </div>
            
            <div class="ticket-footer">
                <p><strong>TravelPlanner</strong></p>
                <p>Thank you for choosing us for your travel needs!</p>
                <p>For support: sarveshtravelplanner@gmail.com</p>
            </div>
        </div>
    </body>
    </html>';
    
    return $html;
}

function sendTicketEmail($booking, $pdfData, $paymentId) {
    try {
        $bookingType = $booking['booking_type'] ?? 'Travel';
        $destinationName = $booking['destination_name'] ?? $booking['destination'];
        $startDate = $booking['start_date'] ?? $booking['date'];
        $endDate = $booking['end_date'] ?? $booking['date'];
        $numTravelers = $booking['num_travelers'];
        $totalAmount = $booking['fare'];
        $contactName = $booking['name'];
        $contactEmail = $booking['contact_email'];
        $contactMobile = $booking['contact_mobile'];
        
        // Email subject
        $subject = "🎫 Your Travel Ticket (Manually Verified) - Booking ID: " . $booking['booking_id'];
        
        // Email body
        $message = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .header { background: linear-gradient(135deg, #0077cc, #2193b0); color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; }
                .booking-info { background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0; }
                .success-message { background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin: 15px 0; }
                .manual-notice { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 8px; margin: 15px 0; }
                .footer { background: #333; color: white; padding: 15px; text-align: center; margin-top: 20px; }
                .info-item { margin: 10px 0; }
                .label { font-weight: bold; }
            </style>
        </head>
        <body>
            <div class='header'>
                <h1>✈️ TravelPlanner</h1>
                <p>Your Travel Ticket is Ready!</p>
            </div>
            
            <div class='content'>
                <div class='success-message'>
                    <h2>✅ Payment Confirmed & Ticket Generated!</h2>
                    <p>Dear $contactName,</p>
                    <p>Your payment has been successfully verified and your travel ticket is ready. Please find your ticket attached to this email.</p>
                </div>
                
                <div class='manual-notice'>
                    <h3>👨‍💼 Manual Verification Notice</h3>
                    <p>This booking was manually verified by our admin team. Your payment was successful and has been confirmed.</p>
                </div>
                
                <div class='booking-info'>
                    <h3>📋 Booking Summary</h3>
                    <div class='info-item'>
                        <span class='label'>Booking ID:</span> " . $booking['booking_id'] . "
                    </div>
                    <div class='info-item'>
                        <span class='label'>Booking Type:</span> " . ucfirst($bookingType) . "
                    </div>
                    <div class='info-item'>
                        <span class='label'>Destination:</span> " . htmlspecialchars($destinationName) . "
                    </div>
                    <div class='info-item'>
                        <span class='label'>Travel Dates:</span> $startDate to $endDate
                    </div>
                    <div class='info-item'>
                        <span class='label'>Number of Travelers:</span> $numTravelers
                    </div>
                    <div class='info-item'>
                        <span class='label'>Total Amount:</span> ₹" . number_format($totalAmount) . "
                    </div>
                    <div class='info-item'>
                        <span class='label'>Payment ID:</span> $paymentId
                    </div>
                    <div class='info-item'>
                        <span class='label'>Payment Amount:</span> ₹1 (Test Payment)
                    </div>
                </div>
                
                <div style='margin: 20px 0;'>
                    <h3>📥 Your Ticket</h3>
                    <p>Your travel ticket is attached to this email as a PDF file. Please:</p>
                    <ul>
                        <li>Download and save the ticket to your device</li>
                        <li>Print a copy for your journey</li>
                        <li>Keep it handy during your travel</li>
                        <li>Share with all travelers in your group</li>
                    </ul>
                </div>
                
                <div style='background: #fff3cd; padding: 15px; border-radius: 8px; margin: 15px 0;'>
                    <h4>📋 Important Travel Guidelines</h4>
                    <ul>
                        <li>Please carry a valid ID proof for all travelers</li>
                        <li>Arrive at least 2 hours before departure for international flights</li>
                        <li>Arrive at least 1 hour before departure for domestic flights</li>
                        <li>Keep this ticket handy during your journey</li>
                        <li>Contact our support team for any assistance</li>
                    </ul>
                </div>
                
                <div style='margin: 20px 0;'>
                    <h3>📞 Need Help?</h3>
                    <p>If you have any questions or need assistance, please contact us:</p>
                    <ul>
                        <li><strong>Email:</strong> sarveshtravelplanner@gmail.com</li>
                        <li><strong>Mobile:</strong> $contactMobile</li>
                    </ul>
                </div>
            </div>
            
            <div class='footer'>
                <p><strong>TravelPlanner</strong></p>
                <p>Thank you for choosing us for your travel needs!</p>
                <p>Have a wonderful journey! ✈️</p>
            </div>
        </body>
        </html>";
        
        // Email headers for attachment
        $boundary = md5(time());
        $headers = array();
        $headers[] = "MIME-Version: 1.0";
        $headers[] = "From: TravelPlanner <sarveshtravelplanner@gmail.com>";
        $headers[] = "Reply-To: sarveshtravelplanner@gmail.com";
        $headers[] = "Content-Type: multipart/mixed; boundary=\"" . $boundary . "\"";
        $headers[] = "X-Mailer: PHP/" . phpversion();
        
        // Email body with attachment
        $emailBody = "--" . $boundary . "\r\n";
        $emailBody .= "Content-Type: text/html; charset=UTF-8\r\n";
        $emailBody .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
        $emailBody .= $message . "\r\n\r\n";
        
        // Add PDF attachment
        $emailBody .= "--" . $boundary . "\r\n";
        $emailBody .= "Content-Type: application/pdf; name=\"ticket_" . $booking['booking_id'] . ".pdf\"\r\n";
        $emailBody .= "Content-Transfer-Encoding: base64\r\n";
        $emailBody .= "Content-Disposition: attachment; filename=\"ticket_" . $booking['booking_id'] . ".pdf\"\r\n\r\n";
        $emailBody .= chunk_split(base64_encode($pdfData)) . "\r\n";
        $emailBody .= "--" . $boundary . "--\r\n";
        
        // Send email
        $mailSent = mail($contactEmail, $subject, $emailBody, implode("\r\n", $headers));
        
        if ($mailSent) {
            error_log("Manual ticket email sent successfully to: $contactEmail for booking: " . $booking['booking_id']);
            return true;
        } else {
            error_log("Failed to send manual ticket email to: $contactEmail for booking: " . $booking['booking_id']);
            return false;
        }
        
    } catch (Exception $e) {
        error_log("Manual email sending error: " . $e->getMessage());
        return false;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enhanced Payment Verification</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .status-pending { color: #ffc107; }
        .status-failed { color: #dc3545; }
        .status-completed { color: #28a745; }
        .card { margin-bottom: 20px; }
        .table-responsive { margin-top: 20px; }
        .payment-details { background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
                    <div class="container-fluid">
                        <a class="navbar-brand" href="#">
                            <i class="fas fa-plane"></i> TravelPlanner Admin
                        </a>
                        <div class="navbar-nav ms-auto">
                            <a class="nav-link" href="admin_dashboard.php">Dashboard</a>
                            <a class="nav-link active" href="enhanced_payment_verification.php">Payment Verification</a>
                            <a class="nav-link" href="admin_logout.php">Logout</a>
                        </div>
                    </div>
                </nav>
            </div>
        </div>

        <div class="container mt-4">
            <h1 class="mb-4">
                <i class="fas fa-credit-card"></i> Enhanced Payment Verification
            </h1>

            <?php if ($message): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i> <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Payment Check and Verification -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-search"></i> Check Payment Status</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="action" value="check_payment">
                                <div class="mb-3">
                                    <label for="check_payment_id" class="form-label">Payment ID</label>
                                    <input type="text" class="form-control" id="check_payment_id" name="payment_id" required>
                                </div>
                                <button type="submit" class="btn btn-info">
                                    <i class="fas fa-search"></i> Check Status
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-check-circle"></i> Verify & Process Payment</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="action" value="verify_and_process">
                                <div class="mb-3">
                                    <label for="verify_payment_id" class="form-label">Payment ID</label>
                                    <input type="text" class="form-control" id="verify_payment_id" name="payment_id" required>
                                </div>
                                <div class="mb-3">
                                    <label for="verify_order_id" class="form-label">Order ID</label>
                                    <input type="text" class="form-control" id="verify_order_id" name="order_id" required>
                                </div>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-check"></i> Verify & Process
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Details Display -->
            <?php if ($paymentDetails): ?>
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-info-circle"></i> Payment Details</h5>
                </div>
                <div class="card-body">
                    <div class="payment-details">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Payment ID:</strong> <?php echo htmlspecialchars($paymentDetails['id']); ?></p>
                                <p><strong>Status:</strong> 
                                    <span class="badge <?php echo $paymentDetails['status'] === 'captured' ? 'bg-success' : 'bg-warning'; ?>">
                                        <?php echo ucfirst($paymentDetails['status']); ?>
                                    </span>
                                </p>
                                <p><strong>Amount:</strong> ₹<?php echo number_format($paymentDetails['amount'] / 100, 2); ?></p>
                                <p><strong>Currency:</strong> <?php echo strtoupper($paymentDetails['currency']); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Method:</strong> <?php echo ucfirst($paymentDetails['method']); ?></p>
                                <p><strong>Created:</strong> <?php echo date('Y-m-d H:i:s', $paymentDetails['created_at']); ?></p>
                                <p><strong>Order ID:</strong> <?php echo htmlspecialchars($paymentDetails['order_id']); ?></p>
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($paymentDetails['email'] ?? 'N/A'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Pending/Failed Payments -->
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-clock"></i> Pending/Failed Payments</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Booking ID</th>
                                    <th>Customer</th>
                                    <th>Destination</th>
                                    <th>Amount</th>
                                    <th>Order ID</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($payment = $pendingPayments->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($payment['booking_id']); ?></td>
                                    <td>
                                        <?php echo htmlspecialchars($payment['name']); ?><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($payment['contact_email']); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($payment['destination']); ?></td>
                                    <td>₹<?php echo number_format($payment['fare']); ?></td>
                                    <td><?php echo htmlspecialchars($payment['razorpay_order_id']); ?></td>
                                    <td>
                                        <span class="badge <?php echo $payment['status'] === 'pending' ? 'bg-warning' : 'bg-danger'; ?>">
                                            <?php echo ucfirst($payment['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('Y-m-d H:i', strtotime($payment['created_at'])); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-info" onclick="fillForm('<?php echo $payment['razorpay_order_id']; ?>')">
                                            <i class="fas fa-edit"></i> Process
                                        </button>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Recent Completed Payments -->
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-check-circle"></i> Recent Completed Payments</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Booking ID</th>
                                    <th>Customer</th>
                                    <th>Destination</th>
                                    <th>Amount</th>
                                    <th>Payment ID</th>
                                    <th>Payment Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($payment = $completedPayments->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($payment['booking_id']); ?></td>
                                    <td>
                                        <?php echo htmlspecialchars($payment['name']); ?><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($payment['contact_email']); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($payment['destination']); ?></td>
                                    <td>₹<?php echo number_format($payment['fare']); ?></td>
                                    <td><?php echo htmlspecialchars($payment['razorpay_payment_id'] ?? 'N/A'); ?></td>
                                    <td><?php echo $payment['payment_date'] ? date('Y-m-d H:i', strtotime($payment['payment_date'])) : 'N/A'; ?></td>
                                    <td>
                                        <span class="badge bg-success">Completed</span>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function fillForm(orderId) {
            document.getElementById('verify_order_id').value = orderId;
            document.getElementById('verify_payment_id').focus();
        }
    </script>
</body>
</html> 