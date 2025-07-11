<?php
require_once 'config.php';
require_once 'razorpay_config.php';
require_once '../vendor/autoload.php';

use Mpdf\Mpdf;

// Set content type to JSON
header('Content-Type: application/json');

// Get the webhook payload
$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_RAZORPAY_SIGNATURE'] ?? '';

try {
    // Initialize Razorpay
    $razorpay = new RazorpayService();
    
    // Verify webhook signature
    $isValid = $razorpay->verifyWebhookSignature($payload, $signature);
    
    if (!$isValid) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid webhook signature']);
        exit();
    }
    
    // Decode the payload
    $data = json_decode($payload, true);
    
    if (!$data) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON payload']);
        exit();
    }
    
    // Handle different webhook events
    $event = $data['event'] ?? '';
    $paymentId = $data['payload']['payment']['entity']['id'] ?? '';
    $orderId = $data['payload']['payment']['entity']['order_id'] ?? '';
    $status = $data['payload']['payment']['entity']['status'] ?? '';
    
    error_log("Webhook received: Event=$event, PaymentID=$paymentId, OrderID=$orderId, Status=$status");
    
    switch ($event) {
        case 'payment.captured':
            // Payment was successful
            if ($status === 'captured') {
                handleSuccessfulPayment($paymentId, $orderId);
            }
            break;
            
        case 'payment.failed':
            // Payment failed
            handleFailedPayment($paymentId, $orderId);
            break;
            
        case 'order.paid':
            // Order is paid
            handleOrderPaid($orderId);
            break;
            
        default:
            error_log("Unhandled webhook event: $event");
            break;
    }
    
    // Return success response
    echo json_encode(['status' => 'success']);
    
} catch (Exception $e) {
    error_log("Webhook error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}

function handleSuccessfulPayment($paymentId, $orderId) {
    global $conn;
    
    try {
        // Update payment order status
        $stmt = $conn->prepare("
            UPDATE payment_orders 
            SET status = 'completed', 
                razorpay_payment_id = ?, 
                payment_date = NOW(),
                webhook_processed = 1
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
                    webhook_processed = 1
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
                // Generate and send ticket
                $ticketData = generateTicket($booking, $paymentId);
                $emailSent = sendTicketEmail($booking, $ticketData['pdf'], $paymentId);
                
                error_log("Webhook: Payment processed successfully for booking $bookingId. Email sent: " . ($emailSent ? 'Yes' : 'No'));
            }
        }
        
    } catch (Exception $e) {
        error_log("Error handling successful payment: " . $e->getMessage());
    }
}

function handleFailedPayment($paymentId, $orderId) {
    global $conn;
    
    try {
        // Update payment order status
        $stmt = $conn->prepare("
            UPDATE payment_orders 
            SET status = 'failed', 
                razorpay_payment_id = ?,
                webhook_processed = 1
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
                SET payment_status = 'failed',
                    webhook_processed = 1
                WHERE id = ?
            ");
            $stmt->bind_param("i", $bookingId);
            $stmt->execute();
            
            error_log("Webhook: Payment failed for booking $bookingId");
        }
        
    } catch (Exception $e) {
        error_log("Error handling failed payment: " . $e->getMessage());
    }
}

function handleOrderPaid($orderId) {
    global $conn;
    
    try {
        // Get payment details from Razorpay
        $razorpay = new RazorpayService();
        $payment = $razorpay->getPaymentByOrderId($orderId);
        
        if ($payment && $payment['status'] === 'captured') {
            handleSuccessfulPayment($payment['id'], $orderId);
        }
        
    } catch (Exception $e) {
        error_log("Error handling order paid: " . $e->getMessage());
    }
}

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
            .webhook-notice { background: #d1ecf1; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #17a2b8; }
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
                    <strong>✅ Payment Confirmed (Auto-processed)</strong><br>
                    Payment ID: ' . $paymentId . '<br>
                    Amount Paid: ₹1 (Test Payment)<br>
                    Original Amount: ₹' . number_format($totalAmount) . '<br>
                    Processed via Webhook
                </div>
                
                <div class="webhook-notice">
                    <strong>🔄 Automatic Processing</strong><br>
                    This booking was automatically processed via our payment system. Your payment was successful on Razorpay and has been automatically confirmed.
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
        $subject = "🎫 Your Travel Ticket (Auto-processed) - Booking ID: " . $booking['booking_id'];
        
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
                .webhook-notice { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 8px; margin: 15px 0; }
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
                    <p>Your payment has been successfully processed and your travel ticket is ready. Please find your ticket attached to this email.</p>
                </div>
                
                <div class='webhook-notice'>
                    <h3>🔄 Automatic Processing Notice</h3>
                    <p>This booking was automatically processed via our payment system. Your payment was successful on Razorpay and has been automatically confirmed.</p>
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
            error_log("Webhook ticket email sent successfully to: $contactEmail for booking: " . $booking['booking_id']);
            return true;
        } else {
            error_log("Failed to send webhook ticket email to: $contactEmail for booking: " . $booking['booking_id']);
            return false;
        }
        
    } catch (Exception $e) {
        error_log("Webhook email sending error: " . $e->getMessage());
        return false;
    }
}
?>
