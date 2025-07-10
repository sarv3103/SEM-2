<?php
// Enhanced ticket download system with QR codes and flight details
session_start();
require_once 'config.php';
require_once __DIR__ . '/../vendor/autoload.php';

// Set proper headers for HTML download
header('Content-Type: text/html; charset=utf-8');
header('Content-Disposition: attachment; filename="TravelPlanner_Ticket.html"');

// Get booking data
$bookingId = $_GET['booking_id'] ?? null;
$tempId = $_GET['temp_id'] ?? null;

if (!$bookingId && !$tempId) {
    echo '<!DOCTYPE html><html><head><title>Error</title></head><body><h1>No booking data provided</h1></body></html>';
    exit;
}

// Get booking data from database or temp storage
$bookingData = null;
$ticketNo = 'UNKNOWN';

if ($bookingId) {
    // Get from database
    try {
        $stmt = $conn->prepare("
            SELECT b.*, u.username, u.email 
            FROM bookings b 
            LEFT JOIN users u ON b.user_id = u.id 
            WHERE b.id = ? OR b.booking_id = ?
        ");
        $stmt->bind_param("ss", $bookingId, $bookingId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            $booking = $result->fetch_assoc();
            $bookingData = [
                'source' => $booking['source'],
                'destination' => $booking['destination'],
                'date' => $booking['date'],
                'selected_mode' => $booking['travel_style'],
                'contact_name' => $booking['contact_name'] ?? $booking['username'],
                'contact_mobile' => $booking['contact_mobile'],
                'contact_email' => $booking['contact_email'] ?? $booking['email'],
                'num_travelers' => $booking['num_travelers'],
                'fare' => $booking['fare'],
                'type' => $booking['type'] ?? 'Ticket',
                'payment_status' => $booking['payment_status'] ?? '-',
            ];
            $ticketNo = $booking['booking_id'] ?? $booking['id'];
        }
    } catch (Exception $e) {
        // Database error
    }
} elseif ($tempId) {
    // Get from temp storage
    require_once 'temp_booking_storage.php';
    $tempData = getTempBooking($tempId);
    if ($tempData) {
        $bookingData = $tempData;
        $ticketNo = $tempData['ticket_no'] ?? 'TEMP_' . time();
    }
}

if (!$bookingData) {
    echo '<!DOCTYPE html><html><head><title>Error</title></head><body><h1>Booking data not found</h1></body></html>';
    exit;
}

// Generate simple ticket HTML
$html = generateSimpleTicketHtml($bookingData, $ticketNo, $bookingData['fare'] ?? 0);
echo $html;

function generateSimpleTicketHtml($bookingData, $bookingId, $totalAmount) {
    $source = $bookingData['source'] ?? '';
    $destination = $bookingData['destination'] ?? '';
    $date = $bookingData['date'] ?? '';
    $mode = strtoupper($bookingData['selected_mode'] ?? '');
    $contactName = $bookingData['contact_name'] ?? '';
    $contactMobile = $bookingData['contact_mobile'] ?? '';
    $contactEmail = $bookingData['contact_email'] ?? '';
    $numTravelers = $bookingData['num_travelers'] ?? 1;
    $travelers = $bookingData['travelers'] ?? [];
    $type = $bookingData['type'] ?? 'Ticket';
    $paymentStatus = ucfirst($bookingData['payment_status'] ?? '-');
    
    // QR code removed - not scannable
    
    // Generate flight details
    $flightDetails = generateFlightDetails($source, $destination, $mode);
    
    // Generate payment breakdown
    $paymentBreakdown = generatePaymentBreakdown($totalAmount, $numTravelers);
    
    return '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>TravelPlanner Ticket - ' . $bookingId . '</title>
        <style>
            body { 
                font-family: Arial, sans-serif; 
                margin: 0; 
                padding: 20px; 
                background: #f5f5f5; 
                line-height: 1.6;
            }
            .ticket { 
                max-width: 900px; 
                margin: 0 auto; 
                background: white; 
                border-radius: 15px; 
                box-shadow: 0 10px 30px rgba(0,0,0,0.1); 
                overflow: hidden; 
            }
            .header { 
                background: linear-gradient(135deg, #0077cc, #2193b0); 
                color: white; 
                padding: 30px; 
                text-align: center; 
                position: relative;
            }
            .header h1 { 
                margin: 0; 
                font-size: 32px; 
                font-weight: bold; 
            }
            .header p { 
                margin: 10px 0 0 0; 
                opacity: 0.9; 
            }
            /* QR code removed - not scannable */
            .body { 
                padding: 30px; 
            }
            .section { 
                margin-bottom: 25px; 
            }
            .section h3 { 
                color: #0077cc; 
                border-bottom: 2px solid #0077cc; 
                padding-bottom: 8px; 
                margin-bottom: 15px; 
            }
            .info-grid { 
                display: grid; 
                grid-template-columns: 1fr 1fr; 
                gap: 15px; 
                margin-bottom: 20px; 
            }
            .info-item { 
                background: #f8f9fa; 
                padding: 12px; 
                border-radius: 8px; 
                border-left: 4px solid #0077cc; 
            }
            .info-label { 
                font-weight: bold; 
                color: #333; 
                margin-bottom: 5px; 
            }
            .info-value { 
                color: #666; 
            }
            .payment { 
                background: linear-gradient(135deg, #28a745, #20c997); 
                color: white; 
                padding: 20px; 
                border-radius: 10px; 
                text-align: center; 
                margin: 20px 0; 
            }
            .flight-details {
                background: linear-gradient(135deg, #ff6b35, #f7931e);
                color: white;
                padding: 20px;
                border-radius: 10px;
                margin: 20px 0;
            }
            .flight-table {
                width: 100%;
                border-collapse: collapse;
                margin: 15px 0;
                background: rgba(255,255,255,0.1);
                border-radius: 8px;
                overflow: hidden;
            }
            .flight-table th, .flight-table td {
                padding: 12px;
                text-align: left;
                border-bottom: 1px solid rgba(255,255,255,0.2);
            }
            .flight-table th {
                background: rgba(255,255,255,0.2);
                font-weight: bold;
            }
            .payment-table {
                width: 100%;
                border-collapse: collapse;
                margin: 15px 0;
            }
            .payment-table th, .payment-table td {
                border: 1px solid #ddd;
                padding: 8px;
                text-align: left;
            }
            .payment-table th {
                background: #f8f9fa;
                font-weight: bold;
            }
            .footer { 
                background: #333; 
                color: white; 
                padding: 20px; 
                text-align: center; 
            }
            .footer p { 
                margin: 5px 0; 
            }
            .traveler-table { 
                width: 100%; 
                border-collapse: collapse; 
                margin: 15px 0; 
            }
            .traveler-table th, .traveler-table td { 
                border: 1px solid #ddd; 
                padding: 8px; 
                text-align: left; 
            }
            .traveler-table th { 
                background: #f8f9fa; 
                font-weight: bold; 
            }
            .status-badge {
                background: #28a745;
                color: white;
                padding: 4px 8px;
                border-radius: 12px;
                font-size: 12px;
                font-weight: bold;
            }
            @media print { 
                body { background: white; } 
                .ticket { box-shadow: none; } 
            }
        </style>
    </head>
    <body>
        <div class="ticket">
            <div class="header">
                <h1>TravelPlanner Ticket</h1>
                <p><strong>Ticket No:</strong> ' . $bookingId . ' | <strong>Type:</strong> ' . htmlspecialchars($type) . ' | <strong>Status:</strong> ' . htmlspecialchars($paymentStatus) . '</p>
            </div>
            <div class="body">
                <div class="section">
                    <h3>Journey Details</h3>
                    <div class="info-grid">
                        <div class="info-item"><span class="info-label">From:</span> <span class="info-value">' . htmlspecialchars($source) . '</span></div>
                        <div class="info-item"><span class="info-label">To:</span> <span class="info-value">' . htmlspecialchars($destination) . '</span></div>
                        <div class="info-item"><span class="info-label">Date:</span> <span class="info-value">' . htmlspecialchars($date) . '</span></div>
                        <div class="info-item"><span class="info-label">Travel Mode:</span> <span class="info-value">' . htmlspecialchars($mode) . '</span></div>
                    </div>
                </div>
                <div class="section">
                    <h3>Contact & Travelers</h3>
                    <div class="info-grid">
                        <div class="info-item"><span class="info-label">Contact Name:</span> <span class="info-value">' . htmlspecialchars($contactName) . '</span></div>
                        <div class="info-item"><span class="info-label">Contact Email:</span> <span class="info-value">' . htmlspecialchars($contactEmail) . '</span></div>
                        <div class="info-item"><span class="info-label">Contact Mobile:</span> <span class="info-value">' . htmlspecialchars($contactMobile) . '</span></div>
                        <div class="info-item"><span class="info-label">No. of Travelers:</span> <span class="info-value">' . htmlspecialchars($numTravelers) . '</span></div>
                    </div>
                </div>
                ' . $flightDetails . '
                <div class="section">
                    <h3>Payment</h3>
                    ' . $paymentBreakdown . '
                </div>
            </div>
        </div>
    </body>
    </html>';
}

// QR code functions removed - not scannable

function generateFlightDetails($source, $destination, $mode) {
    $modes = [
        'FLIGHT' => [
            'icon' => '✈️', 
            'type' => 'Flight', 
            'operator' => 'Air India',
            'departure' => '09:30 AM',
            'arrival' => '11:45 AM',
            'duration' => '2h 15m'
        ],
        'TRAIN' => [
            'icon' => '🚂', 
            'type' => 'Train', 
            'operator' => 'Indian Railways',
            'departure' => '08:15 AM',
            'arrival' => '11:30 PM',
            'duration' => '15h 15m'
        ],
        'BUS' => [
            'icon' => '🚌', 
            'type' => 'Bus', 
            'operator' => 'State Transport',
            'departure' => '10:00 PM',
            'arrival' => '08:00 AM',
            'duration' => '10h 00m'
        ]
    ];
    
    $modeInfo = $modes[$mode] ?? $modes['FLIGHT'];
    $flightNumber = generateFlightNumber($source, $destination, $mode);
    
    return '
    <div class="flight-details">
        <h3 style="margin: 0 0 15px 0;">' . $modeInfo['type'] . ' Details</h3>
        <table class="flight-table">
            <thead>
                <tr>
                    <th>Route</th>
                    <th>' . $modeInfo['type'] . ' Number</th>
                    <th>Operator</th>
                    <th>Departure</th>
                    <th>Arrival</th>
                    <th>Duration</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>' . htmlspecialchars($source) . ' → ' . htmlspecialchars($destination) . '</strong></td>
                    <td><strong>' . $flightNumber . '</strong></td>
                    <td>' . $modeInfo['operator'] . '</td>
                    <td>' . $modeInfo['departure'] . '</td>
                    <td>' . $modeInfo['arrival'] . '</td>
                    <td>' . $modeInfo['duration'] . '</td>
                </tr>
            </tbody>
        </table>
    </div>';
}

function generateFlightNumber($source, $destination, $mode) {
    $sourceCode = strtoupper(substr($source, 0, 3));
    $destCode = strtoupper(substr($destination, 0, 3));
    
    if ($mode === 'FLIGHT') {
        return 'AI' . rand(100, 999) . $sourceCode . $destCode;
    } elseif ($mode === 'TRAIN') {
        return '12' . rand(100, 999) . $sourceCode . $destCode;
    } else {
        return 'BUS' . rand(100, 999) . $sourceCode . $destCode;
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
    $baseFare = $totalAmount / $numTravelers;
    $taxes = $baseFare * 0.18; // 18% GST
    $convenienceFee = 50;
    $totalPerPerson = $baseFare + $taxes + $convenienceFee;
    
    return '
    <table class="payment-table">
        <tr>
            <th>Description</th>
            <th>Per Person</th>
            <th>Total (' . $numTravelers . ' persons)</th>
        </tr>
        <tr>
            <td>Base Fare</td>
            <td>₹' . number_format($baseFare, 2) . '</td>
            <td>₹' . number_format($baseFare * $numTravelers, 2) . '</td>
        </tr>
        <tr>
            <td>Taxes & Fees (18% GST)</td>
            <td>₹' . number_format($taxes, 2) . '</td>
            <td>₹' . number_format($taxes * $numTravelers, 2) . '</td>
        </tr>
        <tr>
            <td>Convenience Fee</td>
            <td>₹' . number_format($convenienceFee, 2) . '</td>
            <td>₹' . number_format($convenienceFee * $numTravelers, 2) . '</td>
        </tr>
        <tr style="background: #e8f5e8; font-weight: bold;">
            <td>Total Amount</td>
            <td>₹' . number_format($totalPerPerson, 2) . '</td>
            <td>₹' . number_format($totalAmount, 2) . '</td>
        </tr>
        <tr style="background: #fff3cd;">
            <td colspan="2">Amount Paid (Test Payment)</td>
            <td>₹1.00</td>
        </tr>
    </table>';
}
?> 