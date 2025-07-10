<?php
require_once 'email_config.php';

header('Content-Type: application/json');

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    echo json_encode(['status' => 'error', 'message' => 'No data received']);
    exit;
}

$bookingData = $input['bookingData'] ?? [];
$bookingId = $input['bookingId'] ?? '';
$totalAmount = $input['totalAmount'] ?? 0;
$emailTo = $input['emailTo'] ?? '';

if (!empty($bookingData['ticket_html'])) {
    $ticketHtml = $bookingData['ticket_html'];
} else {
    // Always generate the full ticket HTML from booking data
    $ticketHtml = generateTicketHtml($bookingData, $bookingId, $totalAmount);
}

if (empty($emailTo) || !filter_var($emailTo, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => 'No valid email address provided.']);
    exit;
}

// Add sendTicket method to EmailService if not present
if (!method_exists('EmailService', 'sendTicket')) {
    class EmailServiceWithTicket extends EmailService {
        public function __construct() {
            parent::__construct();
        }
        public function sendTicket($to, $html, $subject = 'Your TravelPlanner Ticket') {
            try {
                $this->mailer->clearAddresses();
                $this->mailer->addAddress($to);
                $this->mailer->isHTML(true);
                $this->mailer->Subject = $subject;
                $this->mailer->Body = $html;
                $this->mailer->AltBody = 'Your ticket is attached.';
                $this->mailer->send();
                return true;
            } catch (Exception $e) {
                error_log("Email Error: " . $e->getMessage());
                return false;
            }
        }
    }
    $emailService = new EmailServiceWithTicket();
} else {
    $emailService = new EmailService();
}

$success = $emailService->sendTicket($emailTo, $ticketHtml);
if ($success) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to send email.']);
}

function generateTicketHtml($bookingData, $bookingId, $totalAmount) {
    $source = $bookingData['source'] ?? '';
    $destination = $bookingData['destination'] ?? '';
    $date = $bookingData['date'] ?? '';
    $mode = strtoupper($bookingData['selected_mode'] ?? '');
    $contactName = $bookingData['contact_name'] ?? '';
    $contactMobile = $bookingData['contact_mobile'] ?? '';
    $contactEmail = $bookingData['contact_email'] ?? '';
    $travelers = $bookingData['travelers'] ?? [];
    
    // Generate flight details
    $flightDetails = generateFlightDetails($source, $destination, $mode);
    
    // Generate payment breakdown
    $paymentBreakdown = generatePaymentBreakdown($totalAmount, count($travelers));
    
    // Generate traveler table
    $travelerRows = '';
    foreach ($travelers as $i => $traveler) {
        $seatNumber = generateSeatNumber($mode, $i + 1);
        $travelerRows .= "<tr><td>" . ($i + 1) . "</td><td>" . htmlspecialchars($traveler['name']) . "</td><td>" . htmlspecialchars($traveler['age']) . "</td><td>" . htmlspecialchars($traveler['gender']) . "</td><td>" . $seatNumber . "</td></tr>";
    }
    
    return '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>TravelPlanner Ticket - ' . $bookingId . '</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; line-height: 1.6; }
            .ticket { max-width: 900px; margin: 0 auto; background: white; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); overflow: hidden; }
            .header { background: linear-gradient(135deg, #0077cc, #2193b0); color: white; padding: 30px; text-align: center; position: relative; }
            .header h1 { margin: 0; font-size: 32px; font-weight: bold; }
            .header p { margin: 10px 0 0 0; opacity: 0.9; }
            .body { padding: 30px; }
            .section { margin-bottom: 25px; }
            .section h3 { color: #0077cc; border-bottom: 2px solid #0077cc; padding-bottom: 8px; margin-bottom: 15px; }
            .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px; }
            .info-item { background: #f8f9fa; padding: 12px; border-radius: 8px; border-left: 4px solid #0077cc; }
            .info-label { font-weight: bold; color: #333; margin-bottom: 5px; }
            .info-value { color: #666; }
            .payment { background: linear-gradient(135deg, #28a745, #20c997); color: white; padding: 20px; border-radius: 10px; text-align: center; margin: 20px 0; }
            .flight-details { background: linear-gradient(135deg, #ff6b35, #f7931e); color: white; padding: 20px; border-radius: 10px; margin: 20px 0; }
            .flight-table { width: 100%; border-collapse: collapse; margin: 15px 0; background: rgba(255,255,255,0.1); border-radius: 8px; overflow: hidden; }
            .flight-table th, .flight-table td { padding: 12px; text-align: left; border-bottom: 1px solid rgba(255,255,255,0.2); }
            .flight-table th { background: rgba(255,255,255,0.2); font-weight: bold; }
            .payment-table { width: 100%; border-collapse: collapse; margin: 15px 0; }
            .payment-table th, .payment-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            .payment-table th { background: #f8f9fa; font-weight: bold; }
            .footer { background: #333; color: white; padding: 20px; text-align: center; }
            .footer p { margin: 5px 0; }
            .traveler-table { width: 100%; border-collapse: collapse; margin: 15px 0; }
            .traveler-table th, .traveler-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            .traveler-table th { background: #f8f9fa; font-weight: bold; }
            .status-badge { background: #28a745; color: white; padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: bold; }
        </style>
    </head>
    <body>
        <div class="ticket">
            <div class="header">
                <h1>TravelPlanner</h1>
                <p>Official Travel Ticket</p>
                <div style="margin-top: 15px;">
                    <span class="status-badge">CONFIRMED</span>
                </div>
            </div>
            
            <div class="body">
                <div class="payment">
                    <h3>Payment Confirmed</h3>
                    <div><strong>Booking ID:</strong> ' . $bookingId . '</div>
                    <div><strong>Amount Paid:</strong> &#8377;1 (Test Payment)</div>
                    <div><strong>Original Amount:</strong> &#8377;' . number_format($totalAmount) . '</div>
                    <div><strong>Booking Date:</strong> ' . date('d/m/Y H:i:s') . '</div>
                </div>
                
                <div class="flight-details">
                    <h3>Flight Details</h3>
                    ' . $flightDetails . '
                </div>
                
                <div class="section">
                    <h3>Payment Breakdown</h3>
                    ' . $paymentBreakdown . '
                </div>
                
                <div class="section">
                    <h3>Traveler Information</h3>
                    <table class="traveler-table">
                        <tr><th>#</th><th>Name</th><th>Age</th><th>Gender</th><th>Seat</th></tr>
                        ' . $travelerRows . '
                    </table>
                </div>
                
                <div class="section">
                    <h3>Contact Information</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">Contact Name</div>
                            <div class="info-value">' . htmlspecialchars($contactName) . '</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Mobile</div>
                            <div class="info-value">' . htmlspecialchars($contactMobile) . '</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Email</div>
                            <div class="info-value">' . htmlspecialchars($contactEmail) . '</div>
                        </div>
                    </div>
                </div>
                
                <div class="section">
                    <h3>Important Information</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">Check-in Time</div>
                            <div class="info-value">2 hours before departure</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Boarding Time</div>
                            <div class="info-value">30 minutes before departure</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Baggage Allowance</div>
                            <div class="info-value">15kg check-in + 7kg cabin</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Document Required</div>
                            <div class="info-value">Valid ID Proof</div>
                        </div>
                    </div>
                </div>
                
                <div class="section">
                    <h3>TravelPlanner Customer Support</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">24/7 Helpline</div>
                            <div class="info-value">+91 9130123270</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">WhatsApp Support</div>
                            <div class="info-value">+91 9130123270</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Email Support</div>
                            <div class="info-value">sarveshtravelplanner@gmail.com</div>
                        </div>
                    </div>
                </div>
                
                <div class="section">
                    <h3>TravelPlanner Wishes & Advice</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">Wishing you a wonderful journey!</div>
                            <div class="info-value">Thank you for booking with TravelPlanner. We hope you have a safe, comfortable, and memorable trip. If you need any help, our team is just a call or email away.</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Travel Tips</div>
                            <div class="info-value">Arrive early, keep your ID handy, and double-check your travel documents. For any assistance, contact our 24/7 helpline.</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="footer">
                <p><strong>Thank you for choosing TravelPlanner!</strong></p>
                <p>Have a safe and enjoyable journey</p>
                <p>Generated on: ' . date('d/m/Y H:i:s') . '</p>
            </div>
        </div>
    </body>
    </html>';
}

function generateFlightDetails($source, $destination, $mode) {
    $departureTime = '06:30';
    $arrivalTime = '08:45';
    $duration = '2h 15m';
    
    if ($mode === 'TRAIN') {
        $departureTime = '08:00';
        $arrivalTime = '16:30';
        $duration = '8h 30m';
    } elseif ($mode === 'BUS') {
        $departureTime = '20:00';
        $arrivalTime = '08:00';
        $duration = '12h 00m';
    }
    
    $flightNumber = generateFlightNumber($source, $destination, $mode);
    
    return '
    <table class="flight-table">
        <tr>
            <th>Flight/Train/Bus No</th>
            <th>From</th>
            <th>To</th>
            <th>Departure</th>
            <th>Arrival</th>
            <th>Duration</th>
        </tr>
        <tr>
            <td>' . $flightNumber . '</td>
            <td>' . htmlspecialchars($source) . '</td>
            <td>' . htmlspecialchars($destination) . '</td>
            <td>' . $departureTime . '</td>
            <td>' . $arrivalTime . '</td>
            <td>' . $duration . '</td>
        </tr>
    </table>';
}

function generateFlightNumber($source, $destination, $mode) {
    $sourceCode = strtoupper(substr($source, 0, 3));
    $destCode = strtoupper(substr($destination, 0, 3));
    
    if ($mode === 'FLIGHT') {
        return 'AI' . (rand(100, 999)) . $sourceCode . $destCode;
    } elseif ($mode === 'TRAIN') {
        return '12' . (rand(100, 999)) . $sourceCode . $destCode;
    } else {
        return 'BUS' . (rand(100, 999)) . $sourceCode . $destCode;
    }
}

function generateSeatNumber($mode, $travelerNumber) {
    if ($mode === 'FLIGHT') {
        $rows = ['A', 'B', 'C', 'D', 'E', 'F'];
        $row = $rows[($travelerNumber - 1) % 6];
        $seat = rand(1, 30);
        return $seat . $row;
    } elseif ($mode === 'TRAIN') {
        $coach = ['A1', 'A2', 'A3', 'B1', 'B2', 'B3'];
        $coachType = $coach[($travelerNumber - 1) % 6];
        $berth = rand(1, 72);
        return $coachType . '-' . $berth;
    } else {
        return 'Seat ' . $travelerNumber;
    }
}

function generatePaymentBreakdown($totalAmount, $numTravelers) {
    if ($numTravelers == 0) return '<p>No travelers specified.</p>';
    $farePerTraveler = $totalAmount / $numTravelers;
    $taxes = $farePerTraveler * 0.18;
    $convenienceFee = 50;
    $totalPerPerson = $farePerTraveler + $taxes + $convenienceFee;
    
    return '
    <table class="payment-table">
        <tr>
            <th>Description</th>
            <th>Per Person</th>
            <th>Total (' . $numTravelers . ' persons)</th>
        </tr>
        <tr>
            <td>Base Fare</td>
            <td>&#8377;' . number_format($farePerTraveler, 2) . '</td>
            <td>&#8377;' . number_format($farePerTraveler * $numTravelers, 2) . '</td>
        </tr>
        <tr>
            <td>Taxes & Fees (18% GST)</td>
            <td>&#8377;' . number_format($taxes, 2) . '</td>
            <td>&#8377;' . number_format($taxes * $numTravelers, 2) . '</td>
        </tr>
        <tr>
            <td>Convenience Fee</td>
            <td>&#8377;' . number_format($convenienceFee, 2) . '</td>
            <td>&#8377;' . number_format($convenienceFee * $numTravelers, 2) . '</td>
        </tr>
        <tr style="background: #e8f5e8; font-weight: bold;">
            <td>Total Amount</td>
            <td>&#8377;' . number_format($totalPerPerson, 2) . '</td>
            <td>&#8377;' . number_format($totalAmount, 2) . '</td>
        </tr>
        <tr style="background: #fff3cd;">
            <td colspan="2">Amount Paid (Test Payment)</td>
            <td>&#8377;1.00</td>
        </tr>
    </table>';
}

// Generate the ticket HTML
$ticketHtml = generateTicketHtml($bookingData, $bookingId, $totalAmount);

// Email configuration
$to = $emailTo;
$subject = "TravelPlanner Ticket - Booking ID: $bookingId";
$message = "
Dear " . htmlspecialchars($bookingData['contact_name'] ?? 'Traveler') . ",

Your travel ticket is attached to this email.

Booking Details:
- Booking ID: $bookingId
- From: " . htmlspecialchars($bookingData['source'] ?? '') . "
- To: " . htmlspecialchars($bookingData['destination'] ?? '') . "
- Date: " . htmlspecialchars($bookingData['date'] ?? '') . "
- Total Amount: &#8377;" . number_format($totalAmount) . "

Please find your complete ticket details in the HTML attachment.

Thank you for choosing TravelPlanner!

Best regards,
TravelPlanner Team
";

$headers = "From: sarveshtravelplanner@gmail.com\r\n";
$headers .= "Reply-To: sarveshtravelplanner@gmail.com\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html; charset=UTF-8\r\n";

// Legacy mail() call removed. All ticket emails now use PHPMailer/EmailService only.
?> 