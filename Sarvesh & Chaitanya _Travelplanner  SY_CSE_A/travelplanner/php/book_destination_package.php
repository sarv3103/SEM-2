<?php
session_start();
require_once 'config.php';
require_once 'razorpay_config.php';
require_once '../vendor/autoload.php';

use Mpdf\Mpdf;

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Please login to make a booking']);
    exit();
}

// Get JSON input
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

if (!$input) {
    echo json_encode(['status' => 'error', 'message' => 'No input data received']);
    exit();
}

try {
    // Validate required fields
    $requiredFields = ['booking_type', 'destination_name', 'start_date', 'end_date', 'num_travelers', 'travel_style', 'contact_mobile', 'contact_email', 'travelers', 'transport'];
    
    foreach ($requiredFields as $field) {
        if (!isset($input[$field]) || empty($input[$field])) {
            echo json_encode(['status' => 'error', 'message' => "Missing required field: $field"]);
            exit();
        }
    }
    
    // Extract data
    $userId = $_SESSION['user_id'];
    $bookingType = $input['booking_type'];
    $destinationName = $input['destination_name'];
    $startDate = $input['start_date'];
    $endDate = $input['end_date'];
    $numTravelers = intval($input['num_travelers']);
    $travelStyle = $input['travel_style'];
    $contactMobile = $input['contact_mobile'];
    $contactEmail = $input['contact_email'];
    $sourceCity = $input['source_city'] ?? '';
    $specialRequirements = $input['special_requirements'] ?? '';
    $travelers = $input['travelers']; // Array of travelers
    $transport = $input['transport'];
    
    // Validate email
    if (!filter_var($contactEmail, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid email address']);
        exit();
    }
    
    // Validate mobile number (10 digits, starts with 6-9)
    if (!preg_match('/^[6-9][0-9]{9}$/', $contactMobile)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid mobile number. Must be 10 digits starting with 6-9']);
        exit();
    }
    
    // Validate travelers array
    if (!is_array($travelers) || count($travelers) !== $numTravelers) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid number of travelers']);
        exit();
    }
    
    // Validate each traveler
    foreach ($travelers as $index => $traveler) {
        if (!isset($traveler['name']) || empty(trim($traveler['name']))) {
            echo json_encode(['status' => 'error', 'message' => "Traveler " . ($index + 1) . " name is required"]);
            exit();
        }
        
        if (!preg_match('/^[a-zA-Z\s]{2,50}$/', trim($traveler['name']))) {
            echo json_encode(['status' => 'error', 'message' => "Invalid traveler " . ($index + 1) . " name. Only letters and spaces allowed (2-50 characters)"]);
            exit();
        }
        
        if (!isset($traveler['age']) || !is_numeric($traveler['age']) || $traveler['age'] < 1 || $traveler['age'] > 120) {
            echo json_encode(['status' => 'error', 'message' => "Invalid traveler " . ($index + 1) . " age. Must be between 1 and 120"]);
            exit();
        }
        
        if (!isset($traveler['gender']) || !in_array($traveler['gender'], ['male', 'female', 'other'])) {
            echo json_encode(['status' => 'error', 'message' => "Invalid traveler " . ($index + 1) . " gender selection"]);
            exit();
        }
        
        // Validate passport for international travel
        if (isset($input['is_international']) && $input['is_international']) {
            if (!isset($traveler['passport']) || empty(trim($traveler['passport']))) {
                echo json_encode(['status' => 'error', 'message' => "Traveler " . ($index + 1) . " passport number is required for international travel"]);
                exit();
            }
            
            // Validate passport format (basic validation)
            if (!preg_match('/^[A-Z0-9]{6,12}$/', strtoupper(trim($traveler['passport'])))) {
                echo json_encode(['status' => 'error', 'message' => "Invalid traveler " . ($index + 1) . " passport number format"]);
                exit();
            }
            
            if (!isset($traveler['nationality']) || empty(trim($traveler['nationality']))) {
                echo json_encode(['status' => 'error', 'message' => "Traveler " . ($index + 1) . " nationality is required for international travel"]);
                exit();
            }
        }
    }
    
    // Validate dates
    $startDateTime = new DateTime($startDate);
    $endDateTime = new DateTime($endDate);
    $today = new DateTime();
    $today->setTime(0, 0, 0);
    
    if ($startDateTime < $today) {
        echo json_encode(['status' => 'error', 'message' => 'Start date cannot be in the past']);
        exit();
    }
    
    if ($endDateTime < $startDateTime) {
        echo json_encode(['status' => 'error', 'message' => 'End date must be after start date']);
        exit();
    }
    
    // Calculate duration
    $duration = $startDateTime->diff($endDateTime)->days + 1;
    
    // Get destination/package information
    $destinationInfo = null;
    $packageInfo = null;
    $isInternational = false;
    
    if ($bookingType === 'destination') {
        // Get destination info from database
        $stmt = $conn->prepare("SELECT * FROM destinations WHERE name = ?");
        $stmt->bind_param("s", $destinationName);
        $stmt->execute();
        $result = $stmt->get_result();
        $destinationInfo = $result->fetch_assoc();
        
        if (!$destinationInfo) {
            echo json_encode(['status' => 'error', 'message' => 'Destination not found']);
            exit();
        }
        
        // Check if international
        $isInternational = strtolower($destinationInfo['location']) === 'international';
        
    } else if ($bookingType === 'package') {
        // Get package info from database
        $stmt = $conn->prepare("SELECT * FROM packages WHERE name = ?");
        $stmt->bind_param("s", $destinationName);
        $stmt->execute();
        $result = $stmt->get_result();
        $packageInfo = $result->fetch_assoc();
        
        if (!$packageInfo) {
            echo json_encode(['status' => 'error', 'message' => 'Package not found']);
            exit();
        }
        
        $isInternational = $packageInfo['type'] === 'international';
    }
    
    // Calculate pricing
    $basePrice = 15000; // Default price
    $transportPrice = $transport['price'] ?? 0;
    
    if ($bookingType === 'destination' && $destinationInfo) {
        // Extract price from price_range field
        if ($destinationInfo['price_range']) {
            preg_match('/\d+/', $destinationInfo['price_range'], $matches);
            if ($matches) {
                $basePrice = intval($matches[0]);
            }
        }
    } else if ($bookingType === 'package' && $packageInfo) {
        $basePrice = floatval($packageInfo['price_per_person']);
    }
    
    // Apply travel style multiplier
    $styleMultiplier = 1.0;
    switch ($travelStyle) {
        case 'budget':
            $styleMultiplier = 0.8;
            break;
        case 'standard':
            $styleMultiplier = 1.0;
            break;
        case 'luxury':
            $styleMultiplier = 1.5;
            break;
    }
    
    $adjustedBasePrice = $basePrice * $styleMultiplier;
    $totalPerPerson = $adjustedBasePrice + $transportPrice;
    $totalAmount = $totalPerPerson * $numTravelers;
    
    // WALLET DEDUCTION LOGIC START
    $userRes = $conn->query("SELECT wallet_balance FROM users WHERE id = $userId");
    $userRow = $userRes ? $userRes->fetch_assoc() : null;
    if (!$userRow || $userRow['wallet_balance'] < $totalAmount) {
        echo json_encode(['status' => 'error', 'message' => 'Insufficient wallet balance. Please add funds to your wallet.']);
        exit();
    }
    $conn->query("UPDATE users SET wallet_balance = wallet_balance - $totalAmount WHERE id = $userId");
    $stmtWallet = $conn->prepare("INSERT INTO wallet_transactions (user_id, amount, type, description) VALUES (?, ?, 'debit', ?)");
    $desc = 'Booking deduction for destination/package booking';
    $stmtWallet->bind_param("ids", $userId, $totalAmount, $desc);
    $stmtWallet->execute();
    // WALLET DEDUCTION LOGIC END
    // --- Sync wallet table for admin dashboard ---
    $stmtWalletSync = $conn->prepare("INSERT INTO wallet (user_id, balance, last_updated) VALUES (?, (SELECT wallet_balance FROM users WHERE id = ?), NOW()) ON DUPLICATE KEY UPDATE balance = (SELECT wallet_balance FROM users WHERE id = ?), last_updated = NOW()");
    $stmtWalletSync->bind_param("iii", $userId, $userId, $userId);
    $stmtWalletSync->execute();
    // --- End wallet table sync ---
    
    // --- Insert payment_orders entry for wallet-based booking ---
    $stmtPayment = $conn->prepare("INSERT INTO payment_orders (booking_id, user_id, amount, status, payment_method, payment_date, created_at) VALUES (?, ?, ?, 'completed', 'wallet', NOW(), NOW())");
    $stmtPayment->bind_param("iid", $dbBookingId, $userId, $totalAmount);
    $stmtPayment->execute();
    // --- End payment_orders entry ---
    
    // Generate booking ID
    $bookingId = 'BK' . strtoupper(bin2hex(random_bytes(6)));
    
    // Store booking in database
    $stmt = $conn->prepare("
        INSERT INTO bookings (
            user_id, name, age, gender, type, source, destination, date, 
            num_travelers, fare, per_person, booking_id, travel_style, 
            is_international, start_date, end_date, duration, contact_mobile, 
            contact_email, special_requirements, booking_type, destination_name
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $mainTraveler = $travelers[0];
    $type = $bookingType === 'destination' ? 'destination_booking' : 'package_booking';
    $source = $sourceCity ?: 'Direct to destination';
    $date = $startDate;
    
    $stmt->bind_param("isisssssiddssissssssss", 
        $userId, 
        $mainTraveler['name'], 
        $mainTraveler['age'], 
        $mainTraveler['gender'], 
        $type, 
        $source, 
        $destinationName, 
        $date, 
        $numTravelers, 
        $totalAmount, 
        $totalPerPerson, 
        $bookingId, 
        $travelStyle, 
        $isInternational,
        $startDate,
        $endDate,
        $duration,
        $contactMobile,
        $contactEmail,
        $specialRequirements,
        $bookingType,
        $destinationName
    );
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to store booking: " . $stmt->error);
    }
    
    $dbBookingId = $conn->insert_id;
    
    // Update user's bookings JSON file
    require_once 'update_user_bookings.php';
    updateUserBookingsFile($userId, $conn);
    
    // Store traveler details for all travelers
    $stmt = $conn->prepare("
        INSERT INTO traveler_details (
            booking_id, traveler_number, name, age, gender, 
            passport_number, nationality
        ) VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    
    foreach ($travelers as $index => $traveler) {
        $travelerNumber = $index + 1;
        $passportNumber = $isInternational ? ($traveler['passport'] ?? '') : '';
        $nationality = $isInternational ? ($traveler['nationality'] ?? 'Indian') : 'Indian';
        
        $stmt->bind_param("iisiss", 
            $dbBookingId, 
            $travelerNumber, 
            $traveler['name'], 
            $traveler['age'], 
            $traveler['gender'],
            $passportNumber,
            $nationality
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to store traveler " . ($index + 1) . " details: " . $stmt->error);
        }
    }
    
    // Generate PDF ticket
    $ticketData = generateDestinationPackageTicket($bookingId, $input, $destinationInfo, $packageInfo, $travelers, $transport, $totalAmount);
    
    // Initialize payment
    try {
        $razorpay = new RazorpayService();
        
        // Create payment order with ₹1 for testing
        $testAmount = 1;
        $order = $razorpay->createOrder($testAmount);
        
        // Store payment order
        $stmt = $conn->prepare("
            INSERT INTO payment_orders (booking_id, razorpay_order_id, amount, status, created_at) 
            VALUES (?, ?, ?, 'pending', NOW())
        ");
        $stmt->bind_param("ssd", $dbBookingId, $order->id, $totalAmount);
        $stmt->execute();
    
    // Send confirmation email
        sendBookingConfirmationEmail($contactEmail, $bookingId, $destinationName, $totalAmount, $startDate, $endDate, $numTravelers, $input, $travelers, $transport, $destinationInfo, $destinationInfo);
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Booking confirmed successfully! Please complete payment.',
            'booking_id' => $bookingId,
            'total_amount' => $totalAmount,
            'pdf_data' => base64_encode($ticketData['html']),
            'payment_order_id' => $order->id,
            'payment_amount' => $testAmount,
            'key_id' => 'rzp_live_2JdrplZN9MSywf'
        ]);
        
    } catch (Exception $e) {
        // If payment fails, still return booking success but with payment error
        sendBookingConfirmationEmail($contactEmail, $bookingId, $destinationName, $totalAmount, $startDate, $endDate, $numTravelers, $input, $travelers, $transport, $destinationInfo, $destinationInfo);
    
    echo json_encode([
        'status' => 'success',
            'message' => 'Booking confirmed successfully! Payment initialization failed.',
        'booking_id' => $bookingId,
        'total_amount' => $totalAmount,
            'pdf_data' => base64_encode($ticketData['html']),
            'payment_error' => $e->getMessage()
    ]);
    }
    
} catch (Exception $e) {
    error_log("Booking error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Booking error: ' . $e->getMessage()
    ]);
}

function generateDestinationPackageTicket($bookingId, $bookingData, $destinationInfo, $packageInfo, $travelers, $transport, $totalAmount) {
    $mpdf = getMpdfInstance();
    
    $html = generateComprehensiveTicketHTML($bookingId, $bookingData, $destinationInfo, $packageInfo, $travelers, $transport, $totalAmount);
    $mpdf->WriteHTML($html);
    
    return [
        'html' => $html,
                    'pdf' => $mpdf->Output('', \Mpdf\Output\Destination::STRING_RETURN)
    ];
}

function generateComprehensiveTicketHTML($bookingId, $bookingData, $destinationInfo, $packageInfo, $travelers, $transport, $totalAmount) {
    if (function_exists('date_default_timezone_set')) {
        date_default_timezone_set('Asia/Kolkata');
    }
    // Ensure Booking ID is always shown
    $bookingId = $bookingId ?: ($bookingData['booking_id'] ?? 'N/A');
    $bookingType = $bookingData['booking_type'] ?? 'N/A';
    $destinationName = $bookingData['destination_name'] ?? 'N/A';
    $startDate = $bookingData['start_date'] ?? 'N/A';
    $endDate = $bookingData['end_date'] ?? 'N/A';
    $returnDate = $bookingData['return_date'] ?? $endDate;
    $numTravelers = $bookingData['num_travelers'] ?? (is_array($travelers) ? count($travelers) : 'N/A');
    $travelStyle = $bookingData['travel_style'] ?? 'N/A';
    $contactMobile = $bookingData['contact_mobile'] ?? 'N/A';
    $contactEmail = $bookingData['contact_email'] ?? 'N/A';
    $contactName = $bookingData['contact_name'] ?? $contactEmail;
    $sourceCity = $bookingData['source_city'] ?? 'N/A';
    $duration = $bookingData['duration'] ?? '';
    $isInternational = $bookingData['is_international'] ?? false;
    $hotel = $destinationInfo['hotel'] ?? $packageInfo['hotel'] ?? $bookingData['hotel'] ?? null;
    $itinerary = $packageInfo['itinerary'] ?? $destinationInfo['itinerary'] ?? $bookingData['itinerary'] ?? null;
    $inclusions = $packageInfo['inclusions'] ?? $destinationInfo['inclusions'] ?? $bookingData['inclusions'] ?? null;
    $exclusions = $packageInfo['exclusions'] ?? $destinationInfo['exclusions'] ?? $bookingData['exclusions'] ?? null;
    $legs = $bookingData['legs'] ?? null;
    $localInfo = getLocalInformation($destinationName, $bookingType);
    $transportDetails = getTransportDetails($transport, $sourceCity, $destinationName, $isInternational);
    $companyInfo = [
        'name' => 'TravelPlanner',
        'email' => 'sarveshtravelplanner@gmail.com',
        'phone' => '+91-9876543210',
        'address' => '123 Travel Street, Mumbai, Maharashtra, India',
        'website' => 'www.travelplanner.com',
        'gst' => 'GSTIN: 27AABCT1234Z1Z5'
    ];
    $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>TravelPlanner - Booking Confirmation</title><style>
        body{font-family:DejaVu Sans,Arial,sans-serif;margin:0;padding:0;background:#f4f8fb;}
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
    $html .= '<div class="ticket-header"><div class="logo">✈️</div><div><div class="brand">TravelPlanner</div><div>Comprehensive Booking Confirmation & Travel Guide</div></div><div class="qr">QR Code</div></div>';
    $html .= '<div class="ticket-body">';
    // Payment Info
    $html .= '<div class="fare-summary"><h3>✅ Payment Confirmed</h3><div>Booking ID: <b>' . htmlspecialchars($bookingId) . '</b></div><div>Amount Paid: <b>₹' . number_format($totalAmount,2) . '</b> (Test Payment)</div><div>Original Amount: <b>₹' . number_format($bookingData['original_price'] ?? $totalAmount,2) . '</b></div><div>Booking Date: <b>' . date('d/m/Y H:i:s') . '</b></div></div>';
    // Booking Info
    $html .= '<div class="section"><div class="section-title">📋 Booking Information</div><div class="info-grid">';
    $html .= '<div class="info-item"><div class="info-label">From</div><div class="info-value">' . htmlspecialchars($sourceCity) . '</div></div>';
    $html .= '<div class="info-item"><div class="info-label">To</div><div class="info-value">' . htmlspecialchars($destinationName) . '</div></div>';
    $html .= '<div class="info-item"><div class="info-label">Travel Style</div><div class="info-value">' . ucfirst($travelStyle) . '</div></div>';
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
    } else {
        // Fallback: show main transport info
        $html .= '<div class="section"><div class="section-title">🛫 Journey Details</div><div class="info-grid">';
        $html .= '<div class="info-item"><div class="info-label">From</div><div class="info-value">' . htmlspecialchars($sourceCity) . '</div></div>';
        $html .= '<div class="info-item"><div class="info-label">To</div><div class="info-value">' . htmlspecialchars($destinationName) . '</div></div>';
        $html .= '<div class="info-item"><div class="info-label">Transport Mode</div><div class="info-value">' . ucfirst($transport['mode'] ?? 'N/A') . '</div></div>';
        $html .= '<div class="info-item"><div class="info-label">Transport Cost</div><div class="info-value">₹' . number_format($transport['price'] ?? 0,2) . '</div></div>';
        $html .= '</div><div style="margin-top:10px;"><b>Journey Details:</b> ' . htmlspecialchars($transportDetails['description'] ?? '') . '<br><b>Return Journey:</b> ' . htmlspecialchars($transportDetails['return_info'] ?? '') . '</div></div>';
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
    $html .= '<div class="section"><div class="section-title">👥 Traveler Information</div><table class="traveler-table"><tr><th>#</th><th>Name</th><th>Age</th><th>Gender</th>';
    if ($isInternational) $html .= '<th>Passport</th><th>Nationality</th>';
    $html .= '<th>Seat</th></tr>';
    foreach ($travelers as $i => $traveler) {
        $html .= '<tr><td>' . ($i+1) . '</td><td>' . htmlspecialchars($traveler['name'] ?? 'N/A') . '</td><td>' . htmlspecialchars($traveler['age'] ?? 'N/A') . '</td><td>' . ucfirst($traveler['gender'] ?? 'N/A') . '</td>';
        if ($isInternational) {
            $html .= '<td>' . htmlspecialchars($traveler['passport'] ?? 'N/A') . '</td><td>' . htmlspecialchars($traveler['nationality'] ?? 'N/A') . '</td>';
        }
        $html .= '<td>' . htmlspecialchars($traveler['seat'] ?? 'N/A') . '</td></tr>';
    }
    $html .= '</table></div>';
    // Contact Info
    $html .= '<div class="section"><div class="section-title">📞 Contact Information</div><div class="info-grid">';
    $html .= '<div class="info-item"><div class="info-label">Contact Name</div><div class="info-value">' . htmlspecialchars($contactName) . '</div></div>';
    $html .= '<div class="info-item"><div class="info-label">Mobile</div><div class="info-value">' . htmlspecialchars($contactMobile) . '</div></div>';
    $html .= '<div class="info-item"><div class="info-label">Email</div><div class="info-value">' . htmlspecialchars($contactEmail) . '</div></div>';
    $html .= '</div></div>';
    // Local Info
    $html .= '<div class="section"><div class="section-title">🏛️ Local Information - ' . htmlspecialchars($destinationName) . '</div><div class="info-grid">';
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

function getLocalInformation($destinationName, $bookingType) {
    // Local information database - you can expand this
    $localInfo = [
        'Mumbai' => [
            'best_time' => 'October to March',
            'language' => 'Marathi, Hindi, English',
            'currency' => 'Indian Rupee (₹)',
            'timezone' => 'IST (UTC+5:30)',
            'attractions' => 'Gateway of India, Marine Drive, Juhu Beach, Elephanta Caves, Bandra-Worli Sea Link',
            'cuisine' => 'Vada Pav, Pav Bhaji, Bhelpuri, Seafood, Street Food',
            'local_transport' => 'Local Trains, BEST Buses, Auto Rickshaws, Taxis, Metro'
        ],
        'Goa' => [
            'best_time' => 'November to March',
            'language' => 'Konkani, English, Hindi',
            'currency' => 'Indian Rupee (₹)',
            'timezone' => 'IST (UTC+5:30)',
            'attractions' => 'Calangute Beach, Fort Aguada, Basilica of Bom Jesus, Dudhsagar Falls, Anjuna Beach',
            'cuisine' => 'Goan Fish Curry, Vindaloo, Bebinca, Feni, Seafood',
            'local_transport' => 'Bikes, Taxis, Buses, Ferries, Auto Rickshaws'
        ],
        'Delhi' => [
            'best_time' => 'October to March',
            'language' => 'Hindi, English, Punjabi',
            'currency' => 'Indian Rupee (₹)',
            'timezone' => 'IST (UTC+5:30)',
            'attractions' => 'Red Fort, Qutub Minar, India Gate, Humayun\'s Tomb, Lotus Temple',
            'cuisine' => 'Butter Chicken, Chaat, Parathas, Biryani, Street Food',
            'local_transport' => 'Metro, DTC Buses, Auto Rickshaws, Taxis, Cycle Rickshaws'
        ],
        'Kerala' => [
            'best_time' => 'September to March',
            'language' => 'Malayalam, English, Hindi',
            'currency' => 'Indian Rupee (₹)',
            'timezone' => 'IST (UTC+5:30)',
            'attractions' => 'Backwaters, Munnar, Alleppey, Fort Kochi, Thekkady',
            'cuisine' => 'Kerala Fish Curry, Appam, Puttu, Malabar Biryani, Coconut Dishes',
            'local_transport' => 'Houseboats, Buses, Taxis, Auto Rickshaws, Ferries'
        ],
        'Rajasthan' => [
            'best_time' => 'October to March',
            'language' => 'Rajasthani, Hindi, English',
            'currency' => 'Indian Rupee (₹)',
            'timezone' => 'IST (UTC+5:30)',
            'attractions' => 'Jaipur Palace, Udaipur Lake Palace, Jaisalmer Fort, Pushkar, Ranthambore',
            'cuisine' => 'Dal Baati Churma, Laal Maas, Ghewar, Pyaaz Kachori, Rajasthani Thali',
            'local_transport' => 'Buses, Taxis, Auto Rickshaws, Camel Rides, Heritage Trains'
        ]
    ];
    
    // Default information for unknown destinations
    $defaultInfo = [
        'best_time' => 'Check local weather conditions',
        'language' => 'Local language and English',
        'currency' => 'Local currency',
        'timezone' => 'Local timezone',
        'attractions' => 'Local attractions and landmarks',
        'cuisine' => 'Local cuisine and specialties',
        'local_transport' => 'Local transport options available'
    ];
    
    return $localInfo[$destinationName] ?? $defaultInfo;
}

function getTransportDetails($transport, $sourceCity, $destinationName, $isInternational) {
    $mode = $transport['mode'] ?? 'flight';
    $price = $transport['price'] ?? 0;
    
    $transportDetails = [
        'flight' => [
            'description' => 'Direct flight from ' . $sourceCity . ' to ' . $destinationName . '. Check-in 2 hours before departure for international flights, 1 hour for domestic flights.',
            'return_info' => 'Return flight details will be provided 24 hours before departure. Please check your email for updates.'
        ],
        'train' => [
            'description' => 'Comfortable train journey from ' . $sourceCity . ' to ' . $destinationName . '. Arrive at station 1 hour before departure.',
            'return_info' => 'Return train tickets will be confirmed and sent via email 48 hours before journey.'
        ],
        'bus' => [
            'description' => 'Luxury bus service from ' . $sourceCity . ' to ' . $destinationName . '. Boarding point details will be sent via SMS.',
            'return_info' => 'Return bus details will be provided 24 hours before departure.'
        ]
    ];
    
    return $transportDetails[$mode] ?? $transportDetails['flight'];
}

function sendBookingConfirmationEmail($email, $bookingId, $destinationName, $totalAmount, $startDate, $endDate, $numTravelers, $bookingData, $travelers, $transport, $localInfo, $transportDetails) {
    try {
        $sourceCity = $bookingData['source_city'] ?? 'Mumbai';
        $travelStyle = $bookingData['travel_style'];
        $contactMobile = $bookingData['contact_mobile'];
        $duration = $bookingData['duration'];
        $isInternational = $bookingData['is_international'] ?? false;
        $bookingType = $bookingData['booking_type'];
        
        // Company information
        $companyInfo = [
            'name' => 'TravelPlanner',
            'email' => 'sarveshtravelplanner@gmail.com',
            'phone' => '+91-9876543210',
            'address' => '123 Travel Street, Mumbai, Maharashtra, India',
            'website' => 'www.travelplanner.com',
            'gst' => 'GSTIN: 27AABCT1234Z1Z5'
        ];
        
        // Email subject
        $subject = "🎫 Your TravelPlanner Booking Confirmation - " . $destinationName . " (ID: " . $bookingId . ")";
        
        // Email body
    $message = "
    <html>
    <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .header { background: linear-gradient(135deg, #0077cc, #2193b0); color: white; padding: 30px; text-align: center; }
                .content { padding: 30px; }
                .booking-info { background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0; }
                .success-message { background: #d4edda; color: #155724; padding: 20px; border-radius: 10px; margin: 20px 0; }
                .footer { background: #333; color: white; padding: 20px; text-align: center; margin-top: 30px; }
                .info-item { margin: 15px 0; }
                .label { font-weight: bold; color: #0077cc; }
                .section { margin: 30px 0; }
                .section h3 { color: #0077cc; border-bottom: 2px solid #0077cc; padding-bottom: 10px; }
                .transport-box { background: #fff8e1; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #ff9800; }
                .local-box { background: #f3e5f5; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #9c27b0; }
                .company-box { background: #e3f2fd; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #2196f3; }
                .traveler-table { width: 100%; border-collapse: collapse; margin: 15px 0; }
                .traveler-table th, .traveler-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                .traveler-table th { background: #f8f9fa; font-weight: bold; }
                .guidelines { background: #fff3cd; padding: 20px; border-radius: 10px; margin: 20px 0; }
                .guidelines h4 { color: #856404; margin-bottom: 15px; }
                .guidelines ul { color: #856404; margin: 0; padding-left: 20px; }
            </style>
    </head>
    <body>
            <div class='header'>
                <h1>✈️ TravelPlanner</h1>
                <p>Your Comprehensive Travel Booking Confirmation</p>
                <p>Complete Travel Guide & Itinerary</p>
            </div>
            
            <div class='content'>
                <div class='success-message'>
                    <h2>✅ Payment Confirmed & Booking Successful!</h2>
        <p>Dear Traveler,</p>
                    <p>Your payment has been successfully processed and your travel booking is confirmed. Please find your comprehensive travel guide and ticket attached to this email.</p>
                </div>
                
                <div class='section'>
                    <h3>📋 Booking Summary</h3>
                    <div class='booking-info'>
                        <div class='info-item'>
                            <span class='label'>Booking ID:</span> " . $bookingId . "
                        </div>
                        <div class='info-item'>
                            <span class='label'>Booking Type:</span> " . ucfirst($bookingType) . "
                        </div>
                        <div class='info-item'>
                            <span class='label'>Destination:</span> " . htmlspecialchars($destinationName) . "
                        </div>
                        <div class='info-item'>
                            <span class='label'>Source City:</span> " . htmlspecialchars($sourceCity) . "
                        </div>
                        <div class='info-item'>
                            <span class='label'>Travel Dates:</span> $startDate to $endDate
                        </div>
                        <div class='info-item'>
                            <span class='label'>Duration:</span> $duration day(s)
                        </div>
                        <div class='info-item'>
                            <span class='label'>Number of Travelers:</span> $numTravelers
                        </div>
                        <div class='info-item'>
                            <span class='label'>Travel Style:</span> " . ucfirst($travelStyle) . "
                        </div>
                        <div class='info-item'>
                            <span class='label'>Total Amount:</span> ₹" . number_format($totalAmount) . "
                        </div>
                        <div class='info-item'>
                            <span class='label'>Payment Amount:</span> ₹1 (Test Payment)
                        </div>
                    </div>
                </div>
                
                <div class='section'>
                    <h3>👥 Traveler Information</h3>
                    <table class='traveler-table'>
                        <thead>
                            <tr>
                                <th>Sr. No.</th>
                                <th>Name</th>
                                <th>Age</th>
                                <th>Gender</th>
                                " . ($isInternational ? '<th>Passport</th><th>Nationality</th>' : '') . "
                            </tr>
                        </thead>
                        <tbody>";
        
        foreach ($travelers as $index => $traveler) {
            $message .= "
                            <tr>
                                <td>" . ($index + 1) . "</td>
                                <td>" . htmlspecialchars($traveler['name']) . "</td>
                                <td>" . $traveler['age'] . "</td>
                                <td>" . ucfirst($traveler['gender']) . "</td>";
            if ($isInternational) {
                $message .= "
                                <td>" . htmlspecialchars($traveler['passport']) . "</td>
                                <td>" . htmlspecialchars($traveler['nationality']) . "</td>";
            }
            $message .= "
                            </tr>";
        }
        
        $message .= "
                        </tbody>
                    </table>
                </div>
                
                <div class='section'>
                    <h3>🚗 Transport Information</h3>
                    <div class='transport-box'>
                        <div class='info-item'>
                            <span class='label'>From:</span> " . htmlspecialchars($sourceCity) . "
                        </div>
                        <div class='info-item'>
                            <span class='label'>To:</span> " . htmlspecialchars($destinationName) . "
                        </div>
                        <div class='info-item'>
                            <span class='label'>Transport Mode:</span> " . ucfirst($transport['mode']) . "
                        </div>
                        <div class='info-item'>
                            <span class='label'>Transport Cost:</span> ₹" . number_format($transport['price']) . "
                        </div>
                        <div class='info-item'>
                            <span class='label'>Journey Details:</span> " . $transportDetails['description'] . "
                        </div>
                        <div class='info-item'>
                            <span class='label'>Return Journey:</span> " . $transportDetails['return_info'] . "
                        </div>
                    </div>
                </div>
                
                <div class='section'>
                    <h3>🏛️ Local Information - " . htmlspecialchars($destinationName) . "</h3>
                    <div class='local-box'>
                        <div class='info-item'>
                            <span class='label'>Best Time to Visit:</span> " . $localInfo['best_time'] . "
                        </div>
                        <div class='info-item'>
                            <span class='label'>Local Language:</span> " . $localInfo['language'] . "
                        </div>
                        <div class='info-item'>
                            <span class='label'>Currency:</span> " . $localInfo['currency'] . "
                        </div>
                        <div class='info-item'>
                            <span class='label'>Time Zone:</span> " . $localInfo['timezone'] . "
                        </div>
                        <div class='info-item'>
                            <span class='label'>Must-Visit Places:</span> " . $localInfo['attractions'] . "
                        </div>
                        <div class='info-item'>
                            <span class='label'>Local Cuisine:</span> " . $localInfo['cuisine'] . "
                        </div>
                        <div class='info-item'>
                            <span class='label'>Local Transport:</span> " . $localInfo['local_transport'] . "
                        </div>
                    </div>
                </div>
                
                <div class='section'>
                    <h3>🏢 TravelPlanner Company Information</h3>
                    <div class='company-box'>
                        <div class='info-item'>
                            <span class='label'>Company Name:</span> " . $companyInfo['name'] . "
                        </div>
                        <div class='info-item'>
                            <span class='label'>Email:</span> " . $companyInfo['email'] . "
                        </div>
                        <div class='info-item'>
                            <span class='label'>Phone:</span> " . $companyInfo['phone'] . "
                        </div>
                        <div class='info-item'>
                            <span class='label'>Website:</span> " . $companyInfo['website'] . "
                        </div>
                        <div class='info-item'>
                            <span class='label'>Address:</span> " . $companyInfo['address'] . "
                        </div>
                        <div class='info-item'>
                            <span class='label'>GST Number:</span> " . $companyInfo['gst'] . "
                        </div>
                    </div>
                </div>
                
                <div class='section'>
                    <h3>📥 Your Travel Documents</h3>
                    <p>Your comprehensive travel ticket and guide are attached to this email as a PDF file. Please:</p>
                    <ul>
                        <li>Download and save the ticket to your device</li>
                        <li>Print a copy for your journey</li>
                        <li>Keep it handy during your travel</li>
                        <li>Share with all travelers in your group</li>
                        <li>Review the local information and guidelines</li>
                    </ul>
                </div>
                
                <div class='guidelines'>
                    <h4>📋 Important Travel Guidelines</h4>
                    <ul>
                        <li>Please carry a valid ID proof for all travelers</li>
                        <li>Arrive at least 2 hours before departure for international flights</li>
                        <li>Arrive at least 1 hour before departure for domestic flights</li>
                        <li>Keep this ticket handy during your journey</li>
                        <li>Contact our support team for any assistance</li>
                        <li>Check local weather and pack accordingly</li>
                        <li>Carry necessary medications and travel insurance</li>
                        <li>Keep emergency contact numbers handy</li>
                        <li>Follow local customs and traditions</li>
                        <li>Stay hydrated and take care of your health</li>
                    </ul>
                </div>
                
                <div class='section'>
                    <h3>📞 Need Help?</h3>
                    <p>If you have any questions or need assistance, please contact us:</p>
                    <ul>
                        <li><strong>Email:</strong> " . $companyInfo['email'] . "</li>
                        <li><strong>Phone:</strong> " . $companyInfo['phone'] . "</li>
                        <li><strong>Website:</strong> " . $companyInfo['website'] . "</li>
        </ul>
                    <p><strong>Emergency Contact:</strong> Available 24/7 for urgent travel assistance</p>
                </div>
            </div>
            
            <div class='footer'>
                <p><strong>TravelPlanner</strong></p>
                <p>Thank you for choosing us for your travel needs!</p>
                <p>Have a wonderful journey! ✈️</p>
                <p>For support: " . $companyInfo['email'] . "</p>
            </div>
    </body>
    </html>";
    
        // Email headers for attachment
        $boundary = md5(time());
        $headers = array();
        $headers[] = "MIME-Version: 1.0";
        $headers[] = "From: TravelPlanner <" . $companyInfo['email'] . ">";
        $headers[] = "Reply-To: " . $companyInfo['email'];
        $headers[] = "Content-Type: multipart/mixed; boundary=\"" . $boundary . "\"";
        $headers[] = "X-Mailer: PHP/" . phpversion();
        
        // Email body with attachment
        $emailBody = "--" . $boundary . "\r\n";
        $emailBody .= "Content-Type: text/html; charset=UTF-8\r\n";
        $emailBody .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
        $emailBody .= $message . "\r\n\r\n";
        
        // Add PDF attachment
        $emailBody .= "--" . $boundary . "\r\n";
        $emailBody .= "Content-Type: application/pdf; name=\"travel_guide_" . $bookingId . ".pdf\"\r\n";
        $emailBody .= "Content-Transfer-Encoding: base64\r\n";
        $emailBody .= "Content-Disposition: attachment; filename=\"travel_guide_" . $bookingId . ".pdf\"\r\n\r\n";
        
        // Generate PDF content
        $ticketData = generateDestinationPackageTicket($bookingId, $bookingData, null, null, $travelers, $transport, $totalAmount);
        $emailBody .= chunk_split(base64_encode($ticketData['pdf'])) . "\r\n";
        $emailBody .= "--" . $boundary . "--\r\n";
        
        // Send email
        $mailSent = mail($email, $subject, $emailBody, implode("\r\n", $headers));
        
        if ($mailSent) {
            error_log("Comprehensive travel email sent successfully to: $email for booking: " . $bookingId);
            return true;
        } else {
            error_log("Failed to send comprehensive travel email to: $email for booking: " . $bookingId);
            return false;
        }
        
    } catch (Exception $e) {
        error_log("Email sending error: " . $e->getMessage());
        return false;
    }
}

// Ensure mPDF uses UTF-8 and DejaVu Sans for PDF generation
function getMpdfInstance() {
    require_once __DIR__ . '/../vendor/autoload.php';
    $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4',
        'default_font' => 'dejavusans',
        'margin_left' => 0,
        'margin_right' => 0,
        'margin_top' => 0,
        'margin_bottom' => 0,
    ]);
    $mpdf->SetTitle('TravelPlanner - Booking Confirmation');
    $mpdf->SetAuthor('TravelPlanner');
    $mpdf->SetDisplayMode('fullpage');
    return $mpdf;
}

function generateComprehensiveTicketHTMLFile($bookingId, $bookingData, $destinationInfo, $packageInfo, $travelers, $transport, $totalAmount) {
    if (function_exists('date_default_timezone_set')) {
        date_default_timezone_set('Asia/Kolkata');
    }
    $bookingId = $bookingId ?: ($bookingData['booking_id'] ?? 'N/A');
    $bookingType = $bookingData['booking_type'] ?? 'N/A';
    $destinationName = $bookingData['destination_name'] ?? 'N/A';
    $startDate = $bookingData['start_date'] ?? 'N/A';
    $endDate = $bookingData['end_date'] ?? 'N/A';
    $returnDate = $bookingData['return_date'] ?? $endDate;
    $numTravelers = $bookingData['num_travelers'] ?? (is_array($travelers) ? count($travelers) : 'N/A');
    $travelStyle = $bookingData['travel_style'] ?? 'N/A';
    $contactMobile = $bookingData['contact_mobile'] ?? 'N/A';
    $contactEmail = $bookingData['contact_email'] ?? 'N/A';
    $contactName = $bookingData['contact_name'] ?? $contactEmail;
    $sourceCity = $bookingData['source_city'] ?? 'N/A';
    $duration = $bookingData['duration'] ?? '';
    $isInternational = $bookingData['is_international'] ?? false;
    $hotel = $destinationInfo['hotel'] ?? $packageInfo['hotel'] ?? $bookingData['hotel'] ?? null;
    $itinerary = $packageInfo['itinerary'] ?? $destinationInfo['itinerary'] ?? $bookingData['itinerary'] ?? null;
    $inclusions = $packageInfo['inclusions'] ?? $destinationInfo['inclusions'] ?? $bookingData['inclusions'] ?? null;
    $exclusions = $packageInfo['exclusions'] ?? $destinationInfo['exclusions'] ?? $bookingData['exclusions'] ?? null;
    $legs = $bookingData['legs'] ?? null;
    $localInfo = getLocalInformation($destinationName, $bookingType);
    $transportDetails = getTransportDetails($transport, $sourceCity, $destinationName, $isInternational);
    $companyInfo = [
        'name' => 'TravelPlanner',
        'email' => 'sarveshtravelplanner@gmail.com',
        'phone' => '+91-9876543210',
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
    $html .= '<div class="ticket-header"><div class="logo">✈️</div><div><div class="brand">TravelPlanner</div><div>Comprehensive Booking Confirmation & Travel Guide</div></div><div class="qr">QR Code</div></div>';
    $html .= '<div class="ticket-body">';
    // Payment Info
    $html .= '<div class="fare-summary"><h3>✅ Payment Confirmed</h3><div>Booking ID: <b>' . htmlspecialchars($bookingId) . '</b></div><div>Amount Paid: <b>₹' . number_format($totalAmount,2) . '</b> (Test Payment)</div><div>Original Amount: <b>₹' . number_format($bookingData['original_price'] ?? $totalAmount,2) . '</b></div><div>Booking Date: <b>' . date('d/m/Y H:i:s') . '</b></div></div>';
    // Booking Info
    $html .= '<div class="section"><div class="section-title">📋 Booking Information</div><div class="info-grid">';
    $html .= '<div class="info-item"><div class="info-label">From</div><div class="info-value">' . htmlspecialchars($sourceCity) . '</div></div>';
    $html .= '<div class="info-item"><div class="info-label">To</div><div class="info-value">' . htmlspecialchars($destinationName) . '</div></div>';
    $html .= '<div class="info-item"><div class="info-label">Travel Style</div><div class="info-value">' . ucfirst($travelStyle) . '</div></div>';
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
    } else {
        // Fallback: show main transport info
        $html .= '<div class="section"><div class="section-title">🛫 Journey Details</div><div class="info-grid">';
        $html .= '<div class="info-item"><div class="info-label">From</div><div class="info-value">' . htmlspecialchars($sourceCity) . '</div></div>';
        $html .= '<div class="info-item"><div class="info-label">To</div><div class="info-value">' . htmlspecialchars($destinationName) . '</div></div>';
        $html .= '<div class="info-item"><div class="info-label">Transport Mode</div><div class="info-value">' . ucfirst($transport['mode'] ?? 'N/A') . '</div></div>';
        $html .= '<div class="info-item"><div class="info-label">Transport Cost</div><div class="info-value">₹' . number_format($transport['price'] ?? 0,2) . '</div></div>';
        $html .= '</div><div style="margin-top:10px;"><b>Journey Details:</b> ' . htmlspecialchars($transportDetails['description'] ?? '') . '<br><b>Return Journey:</b> ' . htmlspecialchars($transportDetails['return_info'] ?? '') . '</div></div>';
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
    $html .= '<div class="section"><div class="section-title">👥 Traveler Information</div><table class="traveler-table"><tr><th>#</th><th>Name</th><th>Age</th><th>Gender</th>';
    if ($isInternational) $html .= '<th>Passport</th><th>Nationality</th>';
    $html .= '<th>Seat</th></tr>';
    foreach ($travelers as $i => $traveler) {
        $html .= '<tr><td>' . ($i+1) . '</td><td>' . htmlspecialchars($traveler['name'] ?? 'N/A') . '</td><td>' . htmlspecialchars($traveler['age'] ?? 'N/A') . '</td><td>' . ucfirst($traveler['gender'] ?? 'N/A') . '</td>';
        if ($isInternational) {
            $html .= '<td>' . htmlspecialchars($traveler['passport'] ?? 'N/A') . '</td><td>' . htmlspecialchars($traveler['nationality'] ?? 'N/A') . '</td>';
        }
        $html .= '<td>' . htmlspecialchars($traveler['seat'] ?? 'N/A') . '</td></tr>';
    }
    $html .= '</table></div>';
    // Contact Info
    $html .= '<div class="section"><div class="section-title">📞 Contact Information</div><div class="info-grid">';
    $html .= '<div class="info-item"><div class="info-label">Contact Name</div><div class="info-value">' . htmlspecialchars($contactName) . '</div></div>';
    $html .= '<div class="info-item"><div class="info-label">Mobile</div><div class="info-value">' . htmlspecialchars($contactMobile) . '</div></div>';
    $html .= '<div class="info-item"><div class="info-label">Email</div><div class="info-value">' . htmlspecialchars($contactEmail) . '</div></div>';
    $html .= '</div></div>';
    // Local Info
    $html .= '<div class="section"><div class="section-title">🏛️ Local Information - ' . htmlspecialchars($destinationName) . '</div><div class="info-grid">';
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

// HTML ticket download endpoint for package bookings
if (isset($_GET['action']) && $_GET['action'] === 'download_html' && isset($_GET['booking_id'])) {
    // Fetch booking data as per your logic (example shown)
    $bookingId = $_GET['booking_id'];
    // You may need to fetch $bookingData, $destinationInfo, $packageInfo, $travelers, $transport, $totalAmount from your DB
    // For demonstration, assume you have a function getBookingDataById($bookingId)
    $bookingData = getBookingDataById($bookingId); // Implement this function as per your DB
    $destinationInfo = $bookingData['destination_info'] ?? [];
    $packageInfo = $bookingData['package_info'] ?? [];
    $travelers = $bookingData['travelers'] ?? [];
    $transport = $bookingData['transport'] ?? [];
    $totalAmount = $bookingData['total_amount'] ?? 0;
    $html = generateComprehensiveTicketHTMLFile($bookingId, $bookingData, $destinationInfo, $packageInfo, $travelers, $transport, $totalAmount);
    header('Content-Type: text/html; charset=UTF-8');
    header('Content-Disposition: attachment; filename="TravelPlanner_Ticket_' . $bookingId . '.html"');
    echo $html;
    exit();
}
?> 