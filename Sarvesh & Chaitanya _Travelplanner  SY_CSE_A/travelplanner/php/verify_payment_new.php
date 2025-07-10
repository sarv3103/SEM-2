<?php
session_start();
require_once 'config.php';
require_once 'razorpay_config.php';
require_once '../vendor/autoload.php';

use Mpdf\Mpdf;

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['razorpay_payment_id']) || !isset($input['razorpay_order_id']) || !isset($input['razorpay_signature'])) {
    echo json_encode(['status' => 'error', 'message' => 'Payment verification parameters are required']);
    exit();
}

$paymentId = $input['razorpay_payment_id'];
$orderId = $input['razorpay_order_id'];
$signature = $input['razorpay_signature'];

try {
    $razorpay = new RazorpayService();
    $isValid = $razorpay->verifyPayment($paymentId, $orderId, $signature);
    
    if ($isValid) {
        $payment = $razorpay->getPaymentDetails($paymentId);
        
        $stmt = $conn->prepare("
            UPDATE payment_orders 
            SET status = 'completed', 
                razorpay_payment_id = ?, 
                payment_date = NOW() 
            WHERE razorpay_order_id = ?
        ");
        $stmt->bind_param("ss", $paymentId, $orderId);
        $stmt->execute();
        
        $stmt = $conn->prepare("SELECT booking_id FROM payment_orders WHERE razorpay_order_id = ?");
        $stmt->bind_param("s", $orderId);
        $stmt->execute();
        $result = $stmt->get_result();
        $orderData = $result->fetch_assoc();
        
        if ($orderData) {
            $bookingId = $orderData['booking_id'];
            
            $stmt = $conn->prepare("
                UPDATE bookings 
                SET payment_status = 'paid', 
                    payment_date = NOW() 
                WHERE id = ?
            ");
            $stmt->bind_param("i", $bookingId);
            $stmt->execute();
            
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
                $ticketData = generateTicket($booking, $paymentId);
                
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Payment verified successfully',
                    'payment_id' => $paymentId,
                    'booking_id' => $bookingId,
                    'ticket_html' => base64_encode($ticketData['html']),
                    'ticket_pdf' => base64_encode($ticketData['pdf']),
                    'booking_details' => $booking
                ]);
            } else {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Payment verified successfully',
                    'payment_id' => $paymentId,
                    'booking_id' => $bookingId
                ]);
            }
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Booking not found'
            ]);
        }
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Payment verification failed'
        ]);
    }
    
} catch (Exception $e) {
    error_log("Payment verification error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Payment verification failed: ' . $e->getMessage()
    ]);
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
?> 