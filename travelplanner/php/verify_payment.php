<?php
session_start();
require_once 'config.php';
require_once 'razorpay_config.php';
require_once '../vendor/autoload.php';

use Razorpay\Api\Api;
use Mpdf\Mpdf;

header('Content-Type: application/json');

// Enable error logging
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Accept both JSON and form POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    
    if (stripos($contentType, 'application/json') !== false) {
        $input = json_decode(file_get_contents('php://input'), true);
        $payment_id = $input['razorpay_payment_id'] ?? $input['payment_id'] ?? '';
        $order_id = $input['razorpay_order_id'] ?? $input['order_id'] ?? '';
    } else {
        $payment_id = $_POST['payment_id'] ?? $_POST['razorpay_payment_id'] ?? '';
        $order_id = $_POST['order_id'] ?? $_POST['razorpay_order_id'] ?? '';
    }

    // Log the verification attempt
    error_log("Payment verification attempt - Payment ID: $payment_id, Order ID: $order_id");

    if (empty($payment_id) || empty($order_id)) {
        echo json_encode(['status' => 'error', 'message' => 'Payment ID and Order ID are required.']);
        exit;
    }

    try {
        // Initialize Razorpay API
        $api = new Api($key_id, $key_secret);
        
        // Fetch payment details from Razorpay
        $payment = $api->payment->fetch($payment_id);
        error_log("Razorpay payment status: " . $payment->status);
        
        if ($payment->status === 'captured') {
            // Payment is captured, now update our database
            try {
                // Use the database connection from config.php
                if ($conn->connect_error) {
                    throw new Exception("Database connection failed: " . $conn->connect_error);
                }
                
                // First, try to find booking ID from payment_orders table
                $stmt = $conn->prepare("SELECT booking_id FROM payment_orders WHERE razorpay_order_id = ?");
                if (!$stmt) {
                    throw new Exception("Database prepare failed: " . $conn->error);
                }
                
                $stmt->bind_param("s", $order_id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                $orderData = null;
                if ($result->num_rows > 0) {
                    $orderData = $result->fetch_assoc();
                    error_log("Found order in payment_orders table: " . json_encode($orderData));
                } else {
                    // If not found in payment_orders, try to find by booking_id directly
                    error_log("Order not found in payment_orders table, trying direct booking lookup");
                    $stmt = $conn->prepare("SELECT id as booking_id FROM bookings WHERE booking_id = ?");
                    if ($stmt) {
                        $stmt->bind_param("s", $order_id);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        if ($result->num_rows > 0) {
                            $orderData = $result->fetch_assoc();
                            error_log("Found booking directly: " . json_encode($orderData));
                        }
                    }
                }
                
                if ($orderData) {
                    $bookingId = $orderData['booking_id'];
                    error_log("Processing booking ID: $bookingId");
                    
                    // --- Update booking payment status and payment date ---
                    $stmt = $conn->prepare("UPDATE bookings SET payment_status = 'paid', payment_date = NOW() WHERE id = ?");
                    if ($stmt) {
                        $stmt->bind_param("i", $bookingId);
                        $updateResult = $stmt->execute();
                        error_log("Booking payment status update result: " . ($updateResult ? 'success' : 'failed'));
                    }
                    // --- Update payment_orders table ---
                    $stmt = $conn->prepare("UPDATE payment_orders SET status = 'completed', razorpay_payment_id = ?, payment_date = NOW() WHERE razorpay_order_id = ?");
                    if ($stmt) {
                        $stmt->bind_param("ss", $payment_id, $order_id);
                        $updateResult = $stmt->execute();
                        error_log("Payment order update result: " . ($updateResult ? 'success' : 'failed'));
                    }
                    // --- Ensure wallet is credited and transaction logged ---
                    $stmt = $conn->prepare("SELECT * FROM bookings WHERE id = ?");
                    $stmt->bind_param("i", $bookingId);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $booking = $result->fetch_assoc();
                    if ($booking) {
                        $userId = $booking['user_id'];
                        $amount = floatval($booking['fare']);
                        if ($userId && $amount > 0) {
                            // Credit wallet
                            $conn->query("UPDATE users SET wallet_balance = wallet_balance + $amount WHERE id = $userId");
                            // Add wallet transaction
                            $stmtWallet = $conn->prepare("INSERT INTO wallet_transactions (user_id, amount, type, description) VALUES (?, ?, 'credit', ?)");
                            $desc = 'Razorpay payment credited for booking #' . $bookingId;
                            $stmtWallet->bind_param("ids", $userId, $amount, $desc);
                            $stmtWallet->execute();
                        }
                        $ticketSent = isset($booking['ticket_sent']) ? $booking['ticket_sent'] : 0;
                        
                        if (!$ticketSent) {
                            // Generate and send ticket
                            try {
                                $ticketData = generateTicket($booking, $payment_id);
                                $emailSent = sendTicketEmail($booking, $ticketData['pdf'], $payment_id);
                                
                                // Mark ticket as sent
                                $stmt = $conn->prepare("UPDATE bookings SET ticket_sent = 1 WHERE id = ?");
                                if ($stmt) {
                                    $stmt->bind_param("i", $bookingId);
                                    $stmt->execute();
                                }
                            } catch (Exception $e) {
                                error_log("Ticket generation error: " . $e->getMessage());
                                $emailSent = false;
                            }
                        } else {
                            $emailSent = true; // Already sent
                        }
                        
                        echo json_encode([
                            'status' => 'success',
                            'message' => 'Payment verified successfully' . ($emailSent ? ' and ticket sent to email' : ''),
                            'payment_id' => $payment_id,
                            'booking_id' => $bookingId,
                            'ticket_sent' => (bool)$ticketSent || $emailSent,
                            'booking_details' => $booking,
                            'email_sent' => $emailSent
                        ]);
                        
                    } else {
                        echo json_encode(['status' => 'error', 'message' => 'Booking not found in database after update']);
                    }
                    
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Order not found in database. Please contact support with Order ID: ' . $order_id]);
                }
                
            } catch (Exception $dbError) {
                error_log("Database error during payment verification: " . $dbError->getMessage());
                echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $dbError->getMessage()]);
            }
            
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Payment not captured. Status: ' . $payment->status]);
        }
        
    } catch (Exception $e) {
        error_log("Payment verification error: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Error verifying payment: ' . $e->getMessage()]);
    }
    
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
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
                    <strong>✅ Payment Confirmed</strong><br>
                    Payment ID: ' . $paymentId . '<br>
                    Amount Paid: ₹1 (Test Payment)<br>
                    Original Amount: ₹' . number_format($totalAmount) . '
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
        $subject = "🎫 Your Travel Ticket - Booking ID: " . $booking['booking_id'];
        
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
        
        // Email headers
        $headers = array();
        $headers[] = "MIME-Version: 1.0";
        $headers[] = "Content-Type: text/html; charset=UTF-8";
        $headers[] = "From: TravelPlanner <sarveshtravelplanner@gmail.com>";
        $headers[] = "Reply-To: sarveshtravelplanner@gmail.com";
        $headers[] = "X-Mailer: PHP/" . phpversion();
        
        // Create temporary PDF file
        $tempPdfFile = 'temp_ticket_' . $booking['booking_id'] . '.pdf';
        file_put_contents($tempPdfFile, $pdfData);
        
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
        
        // Clean up temporary file
        if (file_exists($tempPdfFile)) {
            unlink($tempPdfFile);
        }
        
        if ($mailSent) {
            error_log("Ticket email sent successfully to: $contactEmail for booking: " . $booking['booking_id']);
            return true;
        } else {
            error_log("Failed to send ticket email to: $contactEmail for booking: " . $booking['booking_id']);
            return false;
        }
    } catch (Exception $e) {
        error_log("Email sending error: " . $e->getMessage());
        return false;
    }
}
?> 