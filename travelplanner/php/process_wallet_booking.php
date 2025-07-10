<?php
// process_wallet_booking.php - Process booking using wallet balance
session_start();
require_once 'config.php';
require_once 'session.php';
// Simple ticket generation - no mPDF required

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit();
}

$inputRaw = file_get_contents('php://input');
$input = json_decode($inputRaw, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid JSON: ' . json_last_error_msg(),
        'raw_input' => $inputRaw // For debugging, remove in production
    ]);
    exit();
}

$bookingData = $input['booking_data'] ?? null;

if (!$bookingData) {
    echo json_encode(['status' => 'error', 'message' => 'No booking data provided']);
    exit();
}

try {
    $userId = $_SESSION['user_id'];
    $totalAmount = floatval($bookingData['selected_fare'] ?? 0);
    $demoAmount = 1; // Only ₹1 needed for demo/test
    
    if ($totalAmount <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid booking amount']);
        exit();
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    // Check wallet balance for demo/test
    $stmt = $conn->prepare("SELECT wallet_balance FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($walletBalance);
    $stmt->fetch();
    $stmt->close();
    
    error_log("Wallet balance for user $userId: $walletBalance, demoAmount: $demoAmount");
    error_log("Booking data: " . json_encode($bookingData));
    
    if ($walletBalance < $demoAmount) {
        $conn->rollback();
        echo json_encode(['status' => 'error', 'message' => 'Insufficient wallet balance. Please add funds to your wallet.']);
        exit();
    }
    
    // Deduct only ₹1 for demo/test
    $updateWallet = $conn->query("UPDATE users SET wallet_balance = wallet_balance - $demoAmount WHERE id = $userId");
    if (!$updateWallet) throw new Exception('Failed to deduct wallet balance');
    
    // Add wallet transaction record
    $stmtWallet = $conn->prepare("INSERT INTO wallet_transactions (user_id, amount, type, description, payment_method) VALUES (?, ?, 'debit', ?, 'wallet')");
    $desc = 'Booking payment via wallet (Demo/Test Payment)';
    $stmtWallet->bind_param("ids", $userId, $demoAmount, $desc);
    if (!$stmtWallet->execute()) throw new Exception('Failed to insert wallet transaction');
    
    // Generate booking ID
    $bookingId = 'BK' . date('Ymd') . str_pad($userId, 4, '0', STR_PAD_LEFT) . rand(1000, 9999);
    
    // Create booking record with all relevant fields
    $stmt = $conn->prepare("INSERT INTO bookings (
        booking_id, user_id, booking_type, source, destination, date, start_date, end_date, duration, contact_mobile, contact_email, special_requirements, destination_name, num_travelers, fare, travel_style, is_international, payment_status
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'paid')");
    $bookingType = 'wallet_booking';
    $startDate = $bookingData['start_date'] ?? null;
    $endDate = $bookingData['end_date'] ?? null;
    $duration = $bookingData['duration'] ?? null;
    $specialRequirements = $bookingData['special_requirements'] ?? null;
    $destinationName = $bookingData['destination_name'] ?? $bookingData['destination'] ?? null;
    $travelStyle = $bookingData['travel_style'] ?? $bookingData['selected_mode'] ?? null;
    $isInternational = isset($bookingData['is_international']) ? (int)$bookingData['is_international'] : 0;
    $stmt->bind_param(
        "sissssssissssidsi",
        $bookingId,
        $userId,
        $bookingType,
        $bookingData['source'],
        $bookingData['destination'],
        $bookingData['date'],
        $startDate,
        $endDate,
        $duration,
        $bookingData['contact_mobile'],
        $bookingData['contact_email'],
        $specialRequirements,
        $destinationName,
        $bookingData['num_travelers'],
        $totalAmount,
        $travelStyle,
        $isInternational
    );
    if (!$stmt->execute()) throw new Exception("Error creating booking: " . $stmt->error);
    $bookingDbId = $conn->insert_id;
    
    // Insert traveler details
    if (isset($bookingData['travelers']) && is_array($bookingData['travelers'])) {
        foreach ($bookingData['travelers'] as $index => $traveler) {
            $stmtTraveler = $conn->prepare("INSERT INTO traveler_details (booking_id, traveler_number, name, age, gender) VALUES (?, ?, ?, ?, ?)");
            $travelerNumber = $index + 1;
            $stmtTraveler->bind_param("iisis", $bookingDbId, $travelerNumber, $traveler['name'], $traveler['age'], $traveler['gender']);
            if (!$stmtTraveler->execute()) throw new Exception("Error inserting traveler details: " . $stmtTraveler->error);
        }
    }
    
    // Commit transaction if all succeeded
    $conn->commit();
    
    // Update user's bookings JSON file
    require_once 'update_user_bookings.php';
    updateUserBookingsFile($userId, $conn);
    
    // Generate simple ticket HTML
    $ticketHtml = generateSimpleTicketHtml($bookingData, $bookingId, $totalAmount);
    
    // Return the response with ticket HTML
        echo json_encode([
            'status' => 'success',
            'message' => 'Booking completed successfully using wallet!',
            'booking_id' => $bookingId,
            'amount_paid' => $demoAmount,
            'ticket_html' => base64_encode($ticketHtml),
            'payment_method' => 'wallet'
        ]);
    
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['status' => 'error', 'message' => 'Booking failed: ' . $e->getMessage()]);
}

// Set timezone for correct time
if (function_exists('date_default_timezone_set')) {
    date_default_timezone_set('Asia/Kolkata');
}

function generateSimpleTicketHtml($bookingData, $bookingId, $totalAmount) {
    $source = $bookingData['source'] ?? '';
    $destination = $bookingData['destination'] ?? '';
    $date = $bookingData['date'] ?? '';
    $mode = strtoupper($bookingData['selected_mode'] ?? '');
    $contactName = $bookingData['contact_name'] ?? '';
    $contactMobile = $bookingData['contact_mobile'] ?? '';
    $contactEmail = $bookingData['contact_email'] ?? '';
    $travelers = $bookingData['travelers'] ?? [];
    
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>TravelPlanner Ticket</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
            .ticket { max-width: 800px; margin: 0 auto; background: white; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); overflow: hidden; }
            .header { background: linear-gradient(135deg, #0077cc, #2193b0); color: white; padding: 30px; text-align: center; }
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
            .footer { background: #333; color: white; padding: 20px; text-align: center; }
            .footer p { margin: 5px 0; }
            .traveler-table { width: 100%; border-collapse: collapse; margin: 15px 0; }
            .traveler-table th, .traveler-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            .traveler-table th { background: #f8f9fa; font-weight: bold; }
        </style>
    </head>
    <body>
        <div class="ticket">
            <div class="header">
                <h1>✈️ TravelPlanner</h1>
                <p>Booking Confirmation</p>
            </div>
            
            <div class="body">
                <div class="payment">
                    <h3>✅ Payment Confirmed</h3>
                    <div>Booking ID: ' . $bookingId . '</div>
                    <div>Amount Paid: ₹1 (Test Payment)</div>
                    <div>Original Amount: ₹' . number_format($totalAmount) . '</div>
                    <div>Booking Date: ' . date('d/m/Y H:i:s') . '</div>
                </div>
                
                <div class="section">
                    <h3>📋 Booking Information</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">From</div>
                            <div class="info-value">' . htmlspecialchars($source) . '</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">To</div>
                            <div class="info-value">' . htmlspecialchars($destination) . '</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Travel Date</div>
                            <div class="info-value">' . htmlspecialchars($date) . '</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Mode</div>
                            <div class="info-value">' . htmlspecialchars($mode) . '</div>
                        </div>
                    </div>
                </div>
                
                <div class="section">
                    <h3>👥 Traveler Information</h3>
                    <table class="traveler-table">
                        <tr><th>#</th><th>Name</th><th>Age</th><th>Gender</th></tr>';
    
    foreach ($travelers as $i => $traveler) {
        $html .= '<tr>
            <td>' . ($i+1) . '</td>
            <td>' . htmlspecialchars($traveler['name'] ?? '') . '</td>
            <td>' . htmlspecialchars($traveler['age'] ?? '') . '</td>
            <td>' . ucfirst($traveler['gender'] ?? '') . '</td>
        </tr>';
    }
    
    $html .= '</table>
                </div>
                
                <div class="section">
                    <h3>📞 Contact Information</h3>
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
            </div>
            
            <div class="footer">
                <p><strong>Thank you for choosing TravelPlanner!</strong></p>
                <p>Have a safe and enjoyable journey</p>
                <p>Generated on: ' . date('d M Y H:i:s') . '</p>
            </div>
        </div>
    </body>
    </html>';
    
    return $html;
}

function generateTicketHtmlFile($bookingData, $bookingId, $totalAmount) {
    if (function_exists('date_default_timezone_set')) {
        date_default_timezone_set('Asia/Kolkata');
    }
    $bookingId = $bookingId ?: ($bookingData['booking_id'] ?? 'N/A');
    $source = $bookingData['source'] ?? 'N/A';
    $destination = $bookingData['destination'] ?? 'N/A';
    $startDate = $bookingData['start_date'] ?? 'N/A';
    $endDate = $bookingData['end_date'] ?? 'N/A';
    $returnDate = $bookingData['return_date'] ?? $endDate;
    $numTravelers = $bookingData['num_travelers'] ?? (is_array($bookingData['travelers'] ?? null) ? count($bookingData['travelers']) : 'N/A');
    $travelers = $bookingData['travelers'] ?? [];
    $contactMobile = $bookingData['contact_mobile'] ?? 'N/A';
    $contactEmail = $bookingData['contact_email'] ?? 'N/A';
    $contactName = $bookingData['contact_name'] ?? $contactEmail;
    $duration = $bookingData['duration'] ?? '';
    $mode = $bookingData['mode'] ?? 'N/A';
    $legs = $bookingData['legs'] ?? null;
    $hotel = $bookingData['hotel'] ?? null;
    $itinerary = $bookingData['itinerary'] ?? null;
    $inclusions = $bookingData['inclusions'] ?? null;
    $exclusions = $bookingData['exclusions'] ?? null;
    $localInfo = getLocalInformation($destination, 'normal');
    $companyInfo = [
        'name' => 'TravelPlanner',
        'email' => 'sarveshtravelplanner@gmail.com',
        'phone' => '+91 9130123270',
        'address' => '123 Travel Street, Mumbai, Maharashtra, India',
        'website' => 'www.travelplanner.com',
        'gst' => 'GSTIN: 27AABCT1234Z1Z5'
    ];
    $html = '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>TravelPlanner - Booking Confirmation</title><style>
        body{font-family:Arial,sans-serif;margin:0;padding:0;background:#f4f8fb;}
        .main-bg{background:#f4f8fb;padding:40px 0;}
        .ticket-container{max-width:900px;margin:40px auto;background:white;border-radius:18px;box-shadow:0 8px 32px rgba(0,0,0,0.12);overflow:hidden;}
        .ticket-header{background:linear-gradient(90deg,#0077cc 0%,#00c6ff 100%);color:white;padding:36px 30px 24px 30px;display:flex;align-items:center;gap:24px;}
        .ticket-header .logo{font-size:40px;font-weight:700;letter-spacing:2px;}
        .ticket-header .brand{font-size:22px;font-weight:600;opacity:0.95;}
        .ticket-header .qr{margin-left:auto;background:white;border-radius:12px;padding:12px;box-shadow:0 2px 8px rgba(0,0,0,0.08);color:#0077cc;font-size:14px;min-width:100px;min-height:100px;display:flex;align-items:center;justify-content:center;}
        .ticket-body{padding:36px 30px;}
        .section{margin-bottom:32px;}
        .section-title{font-size:20px;color:#0077cc;font-weight:700;margin-bottom:16px;display:flex;align-items:center;gap:10px;}
        .info-grid{display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:18px;}
        .info-item{background:#f8f9fa;padding:14px 18px;border-radius:8px;border-left:4px solid #0077cc;}
        .info-label{font-weight:600;color:#333;margin-bottom:4px;}
        .info-value{color:#555;}
        .journey-table,.hotel-table,.itinerary-table,.traveler-table,.cost-table{width:100%;border-collapse:collapse;margin:12px 0;}
        .journey-table th,.hotel-table th,.itinerary-table th,.traveler-table th,.cost-table th{background:#e3f2fd;font-weight:700;padding:10px 8px;}
        .journey-table td,.hotel-table td,.itinerary-table td,.traveler-table td,.cost-table td{border:1px solid #e0e0e0;padding:8px;}
        .fare-summary{background:linear-gradient(90deg,#28a745 0%,#20c997 100%);color:white;padding:18px 24px;border-radius:10px;text-align:center;margin-bottom:24px;}
        .fare-summary h3{margin:0 0 8px 0;font-size:22px;}
        .fare-amount{font-size:22px;font-weight:700;}
        .support-section{background:#e3f2fd;padding:16px 20px;border-radius:8px;margin:18px 0;border-left:4px solid #2196f3;}
        .inclusion-list,.exclusion-list{margin:0 0 0 24px;padding:0;}
        .footer{background:#0077cc;color:white;padding:18px 0;text-align:center;font-size:15px;letter-spacing:1px;}
        .footer .support{margin-top:8px;}
        @media(max-width:700px){.ticket-header,.ticket-body{padding:18px 8px;}.info-grid{grid-template-columns:1fr;}}
    </style></head><body><div class="main-bg"><div class="ticket-container">';
    // Header
    $html .= '<div class="ticket-header"><div class="logo">✈️</div><div><div class="brand">TravelPlanner</div><div>Booking Confirmation & Travel Guide</div></div><div class="qr">QR Code</div></div>';
    $html .= '<div class="ticket-body">';
    // Payment Info
    $html .= '<div class="fare-summary"><h3>✅ Payment Confirmed</h3><div>Booking ID: <b>' . htmlspecialchars($bookingId) . '</b></div><div>Amount Paid: <b>₹' . number_format($totalAmount,2) . '</b></div><div>Booking Date: <b>' . date('d/m/Y H:i:s') . '</b></div></div>';
    // Booking Info
    $html .= '<div class="section"><div class="section-title">📋 Booking Information</div><div class="info-grid">';
    $html .= '<div class="info-item"><div class="info-label">From</div><div class="info-value">' . htmlspecialchars($source) . '</div></div>';
    $html .= '<div class="info-item"><div class="info-label">To</div><div class="info-value">' . htmlspecialchars($destination) . '</div></div>';
    $html .= '<div class="info-item"><div class="info-label">Mode</div><div class="info-value">' . htmlspecialchars($mode) . '</div></div>';
    $html .= '<div class="info-item"><div class="info-label">Start Date</div><div class="info-value">' . htmlspecialchars($startDate) . '</div></div>';
    $html .= '<div class="info-item"><div class="info-label">End Date</div><div class="info-value">' . htmlspecialchars($endDate) . '</div></div>';
    $html .= '<div class="info-item"><div class="info-label">Return Date</div><div class="info-value">' . htmlspecialchars($returnDate) . '</div></div>';
    $html .= '<div class="info-item"><div class="info-label">Duration</div><div class="info-value">' . htmlspecialchars($duration) . ' day(s)</div></div>';
    $html .= '<div class="info-item"><div class="info-label">Number of Travelers</div><div class="info-value">' . htmlspecialchars($numTravelers) . '</div></div>';
    $html .= '</div></div>';
    // Journey Legs
    if ($legs && is_array($legs) && count($legs) > 0) {
        $html .= '<div class="section"><div class="section-title">🛫 Journey Breakdown</div><table class="journey-table"><tr><th>Leg</th><th>From</th><th>To</th><th>Date</th><th>Mode</th><th>Vehicle/Flight/Train No</th><th>Seat</th></tr>';
        foreach ($legs as $i => $leg) {
            $html .= '<tr><td>' . ($i+1) . '</td><td>' . htmlspecialchars($leg['from'] ?? '') . '</td><td>' . htmlspecialchars($leg['to'] ?? '') . '</td><td>' . htmlspecialchars($leg['date'] ?? '') . '</td><td>' . htmlspecialchars(ucfirst($leg['mode'] ?? '')) . '</td><td>' . htmlspecialchars($leg['number'] ?? '') . '</td><td>' . htmlspecialchars($leg['seat'] ?? '') . '</td></tr>';
        }
        $html .= '</table></div>';
    }
    // Hotel/Accommodation
    if ($hotel) {
        $html .= '<div class="section"><div class="section-title">🏨 Accommodation Details</div><table class="hotel-table"><tr><th>Hotel Name</th><th>Address</th><th>Check-in</th><th>Check-out</th><th>Contact</th><th>Price (₹)</th></tr>';
        $html .= '<tr><td>' . htmlspecialchars($hotel['name'] ?? 'N/A') . '</td><td>' . htmlspecialchars($hotel['address'] ?? 'N/A') . '</td><td>' . htmlspecialchars($hotel['checkin'] ?? $startDate) . '</td><td>' . htmlspecialchars($hotel['checkout'] ?? $endDate) . '</td><td>' . htmlspecialchars($hotel['contact'] ?? 'N/A') . '</td><td>' . number_format($hotel['price'] ?? 0,2) . '</td></tr>';
        $html .= '</table></div>';
    }
    // Day-wise Itinerary
    if ($itinerary && is_array($itinerary)) {
        $html .= '<div class="section"><div class="section-title">🗓️ Day-wise Itinerary</div><table class="itinerary-table"><tr><th>Day</th><th>Plan</th></tr>';
        foreach ($itinerary as $day => $plan) {
            $html .= '<tr><td>' . htmlspecialchars($day) . '</td><td>' . htmlspecialchars($plan) . '</td></tr>';
        }
        $html .= '</table></div>';
    }
    // Inclusions/Exclusions
    if ($inclusions && is_array($inclusions)) {
        $html .= '<div class="section"><div class="section-title">✅ Inclusions</div><ul class="inclusion-list">';
        foreach ($inclusions as $inc) {
            $html .= '<li>' . htmlspecialchars($inc) . '</li>';
        }
        $html .= '</ul></div>';
    }
    if ($exclusions && is_array($exclusions)) {
        $html .= '<div class="section"><div class="section-title">❌ Exclusions</div><ul class="exclusion-list">';
        foreach ($exclusions as $exc) {
            $html .= '<li>' . htmlspecialchars($exc) . '</li>';
        }
        $html .= '</ul></div>';
    }
    // Traveler Details
    $html .= '<div class="section"><div class="section-title">👥 Traveler Information</div><table class="traveler-table"><tr><th>#</th><th>Name</th><th>Age</th><th>Gender</th><th>Seat</th></tr>';
    foreach ($travelers as $i => $traveler) {
        $html .= '<tr><td>' . ($i+1) . '</td><td>' . htmlspecialchars($traveler['name'] ?? 'N/A') . '</td><td>' . htmlspecialchars($traveler['age'] ?? 'N/A') . '</td><td>' . ucfirst($traveler['gender'] ?? 'N/A') . '</td><td>' . htmlspecialchars($traveler['seat'] ?? 'N/A') . '</td></tr>';
    }
    $html .= '</table></div>';
    // Contact Info
    $html .= '<div class="section"><div class="section-title">📞 Contact Information</div><div class="info-grid">';
    $html .= '<div class="info-item"><div class="info-label">Contact Name</div><div class="info-value">' . htmlspecialchars($contactName) . '</div></div>';
    $html .= '<div class="info-item"><div class="info-label">Mobile</div><div class="info-value">' . htmlspecialchars($contactMobile) . '</div></div>';
    $html .= '<div class="info-item"><div class="info-label">Email</div><div class="info-value">' . htmlspecialchars($contactEmail) . '</div></div>';
    $html .= '</div></div>';
    // Local Info
    $html .= '<div class="section"><div class="section-title">🏛️ Local Information - ' . htmlspecialchars($destination) . '</div><div class="info-grid">';
    $html .= '<div class="info-item"><div class="info-label">Best Time to Visit</div><div class="info-value">' . htmlspecialchars($localInfo['best_time'] ?? 'N/A') . '</div></div>';
    $html .= '<div class="info-item"><div class="info-label">Local Language</div><div class="info-value">' . htmlspecialchars($localInfo['language'] ?? 'N/A') . '</div></div>';
    $html .= '<div class="info-item"><div class="info-label">Currency</div><div class="info-value">' . htmlspecialchars($localInfo['currency'] ?? 'N/A') . '</div></div>';
    $html .= '<div class="info-item"><div class="info-label">Time Zone</div><div class="info-value">' . htmlspecialchars($localInfo['timezone'] ?? 'N/A') . '</div></div>';
    $html .= '</div><div style="margin-top:10px;"><b>Must-Visit Places:</b> ' . htmlspecialchars($localInfo['attractions'] ?? 'N/A') . '<br><b>Local Cuisine:</b> ' . htmlspecialchars($localInfo['cuisine'] ?? 'N/A') . '<br><b>Local Transport:</b> ' . htmlspecialchars($localInfo['local_transport'] ?? 'N/A') . '</div></div>';
    // Cost Breakdown
    $html .= '<div class="section"><div class="section-title">💰 Cost Breakdown</div><table class="cost-table">';
    $html .= '<tr><th>Item</th><th>Amount (₹)</th></tr>';
    $html .= '<tr><td>Base Package</td><td>' . number_format($bookingData['base_price'] ?? 0,2) . '</td></tr>';
    $html .= '<tr><td>Transport</td><td>' . number_format($bookingData['transport_price'] ?? 0,2) . '</td></tr>';
    if ($hotel && isset($hotel['price'])) $html .= '<tr><td>Hotel</td><td>' . number_format($hotel['price'],2) . '</td></tr>';
    $html .= '<tr><td>Total Paid</td><td>' . number_format($totalAmount,2) . '</td></tr>';
    $html .= '</table></div>';
    // Support Section
    $html .= '<div class="support-section"><b>📞 Customer Support</b><div><b>Phone:</b> ' . $companyInfo['phone'] . '</div><div><b>Email:</b> ' . $companyInfo['email'] . '</div><div><b>Address:</b> ' . $companyInfo['address'] . '</div></div>';
    // Footer
    $html .= '<div class="footer"><div>Thank you for choosing TravelPlanner!</div><div>Have a safe and enjoyable journey</div><div class="support">Generated on: ' . date('d M Y H:i:s') . '</div></div>';
    $html .= '</div></div></div></body></html>';
    return $html;
}

// HTML ticket download endpoint for normal bookings
if (isset($_GET['action']) && $_GET['action'] === 'download_html' && isset($_GET['booking_id'])) {
    $bookingId = $_GET['booking_id'];
    // You may need to fetch $bookingData, $totalAmount from your DB
    // For demonstration, assume you have a function getBookingDataById($bookingId)
    $bookingData = getBookingDataById($bookingId); // Implement this function as per your DB
    $totalAmount = $bookingData['total_amount'] ?? 0;
    $html = generateTicketHtmlFile($bookingData, $bookingId, $totalAmount);
    header('Content-Type: text/html; charset=UTF-8');
    header('Content-Disposition: attachment; filename="TravelPlanner_Ticket_' . $bookingId . '.html"');
    echo $html;
    exit();
}
?> 