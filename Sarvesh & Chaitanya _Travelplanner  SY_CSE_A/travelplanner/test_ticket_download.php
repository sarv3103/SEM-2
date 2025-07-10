<?php
// Test ticket download functionality
header('Content-Type: text/html; charset=UTF-8');

// Sample booking data
$bookingData = [
    'booking_id' => 'TP' . time(),
    'source' => 'Mumbai',
    'destination' => 'Delhi',
    'date' => '2024-12-25',
    'mode' => 'flight',
    'contact_mobile' => '+91 9876543210',
    'contact_email' => 'test@example.com',
    'fare' => 8500,
    'travelers' => [
        ['name' => 'John Doe', 'age' => 30, 'gender' => 'Male'],
        ['name' => 'Jane Doe', 'age' => 28, 'gender' => 'Female']
    ]
];

// Generate ticket HTML
$bookingId = $bookingData['booking_id'];
$source = $bookingData['source'];
$destination = $bookingData['destination'];
$date = $bookingData['date'];
$mode = $bookingData['mode'];
$contactMobile = $bookingData['contact_mobile'];
$contactEmail = $bookingData['contact_email'];
$totalAmount = $bookingData['fare'];
$travelers = $bookingData['travelers'];

// Generate flight details
$flightDetails = generateFlightDetails($source, $destination, $mode);

// Generate payment breakdown
$paymentBreakdown = generatePaymentBreakdown($totalAmount, count($travelers));

// Generate traveler table
$travelerTable = '';
foreach ($travelers as $index => $traveler) {
    $seat = chr(65 + rand(0, 25)) . (rand(1, 30));
    $travelerTable .= "
        <tr>
            <td>" . ($index + 1) . "</td>
            <td>{$traveler['name']}</td>
            <td>{$traveler['age']}</td>
            <td>{$traveler['gender']}</td>
            <td>{$seat}</td>
        </tr>";
}

$ticketHTML = '
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
                        <h1>TravelPlanner</h1>
                        <p>Official Travel Ticket</p>
                        <div style="margin-top: 15px;">
                            <span class="status-badge">CONFIRMED</span>
                        </div>
                    </div>
        
        <div class="body">
                                    <div class="section">
                            <h3>Booking Information</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Booking ID</div>
                        <div class="info-value">' . $bookingId . '</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Travel Date</div>
                        <div class="info-value">' . date('d M Y', strtotime($date)) . '</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Contact Mobile</div>
                        <div class="info-value">' . $contactMobile . '</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Contact Email</div>
                        <div class="info-value">' . $contactEmail . '</div>
                    </div>
                </div>
            </div>
            
                                    <div class="section">
                            <h3>Travel Details</h3>
                ' . $flightDetails . '
            </div>
            
                                    <div class="section">
                            <h3>Passenger Details</h3>
                <table class="traveler-table">
                    <thead>
                        <tr>
                            <th>Sr. No.</th>
                            <th>Passenger Name</th>
                            <th>Age</th>
                            <th>Gender</th>
                            <th>Seat</th>
                        </tr>
                    </thead>
                    <tbody>
                        ' . $travelerTable . '
                    </tbody>
                </table>
            </div>
            
                                    <div class="section">
                            <h3>Payment Details</h3>
                ' . $paymentBreakdown . '
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
        </div>
        
        <div class="footer">
            <p><strong>Thank you for choosing TravelPlanner!</strong></p>
            <p>Have a safe and enjoyable journey</p>
            <p>Generated on: ' . date('d M Y H:i:s') . '</p>
        </div>
    </div>
</body>
</html>';

echo $ticketHTML;

function generateFlightDetails($source, $destination, $mode) {
    $modes = [
        'flight' => [
            'icon' => '✈️', 
            'type' => 'Flight', 
            'operator' => 'Air India',
            'number' => 'AI-' . rand(100, 999),
            'departure' => '09:30 AM',
            'arrival' => '11:45 AM',
            'duration' => '2h 15m'
        ],
        'train' => [
            'icon' => '🚂', 
            'type' => 'Train', 
            'operator' => 'Indian Railways',
            'number' => rand(12000, 12999),
            'departure' => '08:15 AM',
            'arrival' => '11:30 PM',
            'duration' => '15h 15m'
        ],
        'bus' => [
            'icon' => '🚌', 
            'type' => 'Bus', 
            'operator' => 'State Transport',
            'number' => 'ST-' . rand(1000, 9999),
            'departure' => '10:00 PM',
            'arrival' => '08:00 AM',
            'duration' => '10h 00m'
        ]
    ];
    
    $modeInfo = $modes[$mode] ?? $modes['flight'];
    
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
                        <td><strong>' . $source . ' → ' . $destination . '</strong></td>
                        <td><strong>' . $modeInfo['number'] . '</strong></td>
                        <td>' . $modeInfo['operator'] . '</td>
                        <td>' . $modeInfo['departure'] . '</td>
                        <td>' . $modeInfo['arrival'] . '</td>
                        <td>' . $modeInfo['duration'] . '</td>
                    </tr>
                </tbody>
            </table>
        </div>';
}

function generatePaymentBreakdown($totalAmount, $travelerCount) {
    $baseFare = round($totalAmount * 0.8);
    $taxes = round($totalAmount * 0.15);
    $convenienceFee = round($totalAmount * 0.05);
    
    return '
        <table class="payment-table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Amount (₹)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Base Fare (' . $travelerCount . ' passenger' . ($travelerCount > 1 ? 's' : '') . ')</td>
                    <td>' . $baseFare . '</td>
                </tr>
                <tr>
                    <td>Taxes & Surcharges</td>
                    <td>' . $taxes . '</td>
                </tr>
                <tr>
                    <td>Convenience Fee</td>
                    <td>' . $convenienceFee . '</td>
                </tr>
                <tr style="background: #e8f5e8; font-weight: bold;">
                    <td>Total Amount</td>
                    <td>₹' . $totalAmount . '</td>
                </tr>
            </tbody>
        </table>
        <div class="payment">
            <h3 style="margin: 0 0 10px 0;">Payment Status: PAID</h3>
            <p style="margin: 5px 0;">Amount: ₹' . $totalAmount . '</p>
            <p style="margin: 5px 0; font-size: 14px;">Payment completed successfully</p>
        </div>';
}
?> 