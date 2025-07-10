<?php
// php/book_plan.php - Dedicated booking system for plan-generated bookings
session_start();
require 'config.php';
require_once 'razorpay_config.php';

// Only handle POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'msg' => 'Invalid request method']);
    exit();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'msg' => 'Please login to book a plan']);
    exit();
}

try {
    // Get booking type
    $bookingType = $_POST['booking_type'] ?? '';
    
    if ($bookingType === 'plan') {
        handleGenericPlanBooking();
    } else {
        // Handle specific plan types
        $planType = $_POST['plan_type'] ?? '';
        
        if (empty($planType)) {
            echo json_encode(['status' => 'error', 'msg' => 'Plan type not specified']);
            exit();
        }
        
        // Handle different plan types
        switch ($planType) {
            case 'destination-only':
                handleDestinationOnlyBooking();
                break;
            case 'complete-journey':
                handleCompleteJourneyBooking();
                break;
            case 'enhanced_travel_plan':
                handleEnhancedTravelBooking();
                break;
            default:
                echo json_encode(['status' => 'error', 'msg' => 'Invalid plan type']);
                exit();
        }
    }
    
} catch (Exception $e) {
    error_log("Plan booking error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'msg' => 'Booking failed: ' . $e->getMessage()]);
}

// Handle generic plan booking from booking_plan.html
function handleGenericPlanBooking() {
    global $conn;
    
    // WALLET DEDUCTION LOGIC START
    $userId = $_SESSION['user_id'];
    $planDataJson = $_POST['plan_data'] ?? '';
    $planData = json_decode(urldecode($planDataJson), true);
    $totalCost = floatval($planData['totalCost'] ?? $planData['totalAmount'] ?? 0);
    if ($totalCost <= 0) $totalCost = 5000;
    $userRes = $conn->query("SELECT wallet_balance FROM users WHERE id = $userId");
    $userRow = $userRes ? $userRes->fetch_assoc() : null;
    if (!$userRow || $userRow['wallet_balance'] < $totalCost) {
        echo json_encode(['status' => 'error', 'msg' => 'Insufficient wallet balance. Please add funds to your wallet.']);
        exit();
    }
    $conn->query("UPDATE users SET wallet_balance = wallet_balance - $totalCost WHERE id = $userId");
    $stmtWallet = $conn->prepare("INSERT INTO wallet_transactions (user_id, amount, type, description) VALUES (?, ?, 'debit', ?)");
    $desc = 'Booking deduction for plan booking';
    $stmtWallet->bind_param("ids", $userId, $totalCost, $desc);
    $stmtWallet->execute();
    // WALLET DEDUCTION LOGIC END
    // --- Sync wallet table for admin dashboard ---
    $stmtWalletSync = $conn->prepare("INSERT INTO wallet (user_id, balance, last_updated) VALUES (?, (SELECT wallet_balance FROM users WHERE id = ?), NOW()) ON DUPLICATE KEY UPDATE balance = (SELECT wallet_balance FROM users WHERE id = ?), last_updated = NOW()");
    $stmtWalletSync->bind_param("iii", $userId, $userId, $userId);
    $stmtWalletSync->execute();
    // --- End wallet table sync ---
    
    // Get form data
    $contactName = $_POST['contact_name'] ?? '';
    $contactMobile = $_POST['contact_mobile'] ?? '';
    $contactEmail = $_POST['contact_email'] ?? '';
    $numTravelers = intval($_POST['num_travelers'] ?? 1);
    $travelType = $_POST['travel_type'] ?? '';
    $specialRequirements = $_POST['special_requirements'] ?? '';
    
    // Validate required fields
    if (empty($contactName) || empty($contactMobile) || empty($contactEmail) || empty($travelType)) {
        echo json_encode(['status' => 'error', 'msg' => 'Please fill in all required fields']);
        exit();
    }
    
    // Validate mobile number
    if (!preg_match('/^[0-9]{10}$/', $contactMobile)) {
        echo json_encode(['status' => 'error', 'msg' => 'Please enter a valid 10-digit mobile number']);
        exit();
    }
    
    // Validate email
    if (!filter_var($contactEmail, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['status' => 'error', 'msg' => 'Please enter a valid email address']);
        exit();
    }
    
    // Collect traveler information
    $travelers = [];
    for ($i = 1; $i <= $numTravelers; $i++) {
        $travelerName = $_POST["traveler_{$i}_name"] ?? '';
        $travelerAge = $_POST["traveler_{$i}_age"] ?? '';
        $travelerGender = $_POST["traveler_{$i}_gender"] ?? '';
        $travelerMobile = $_POST["traveler_{$i}_mobile"] ?? '';
        
        if (empty($travelerName) || empty($travelerAge) || empty($travelerGender) || empty($travelerMobile)) {
            echo json_encode(['status' => 'error', 'msg' => "Please fill in all details for traveler $i"]);
            exit();
        }
        
        $travelers[] = [
            'name' => $travelerName,
            'age' => $travelerAge,
            'gender' => $travelerGender,
            'mobile' => $travelerMobile
        ];
        
        // Add international-specific fields
        if ($travelType === 'international') {
            $passport = $_POST["traveler_{$i}_passport"] ?? '';
            $passportExpiry = $_POST["traveler_{$i}_passport_expiry"] ?? '';
            
            if (empty($passport) || empty($passportExpiry)) {
                echo json_encode(['status' => 'error', 'msg' => "Passport details required for traveler $i"]);
                exit();
            }
            
            $travelers[count($travelers) - 1]['passport'] = $passport;
            $travelers[count($travelers) - 1]['passport_expiry'] = $passportExpiry;
        } else {
            // Domestic travel - ID details
            $idType = $_POST["traveler_{$i}_id_type"] ?? '';
            $idNumber = $_POST["traveler_{$i}_id_number"] ?? '';
            
            if (empty($idType) || empty($idNumber)) {
                echo json_encode(['status' => 'error', 'msg' => "ID details required for traveler $i"]);
                exit();
            }
            
            $travelers[count($travelers) - 1]['id_type'] = $idType;
            $travelers[count($travelers) - 1]['id_number'] = $idNumber;
        }
    }
    
    // Calculate costs from plan data
    $perPerson = floatval($planData['perPerson'] ?? 0);
    
    if ($perPerson <= 0) {
        $perPerson = $totalCost / $numTravelers;
    }
    
    // Generate booking ID
    $bookingId = 'PLAN_' . strtoupper(bin2hex(random_bytes(4))) . date('Ymd');
    
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
    $type = 'custom_plan';
    $source = $planData['fromCity'] ?? 'Direct to destination';
    $destination = $planData['destination'] ?? $planData['toCity'] ?? 'Custom Destination';
    $date = date('Y-m-d');
    $travelStyle = $planData['travelStyle'] ?? 'standard';
    $isInternational = ($travelType === 'international') ? 1 : 0;
    $startDate = $planData['startDate'] ?? date('Y-m-d');
    $endDate = $planData['endDate'] ?? date('Y-m-d', strtotime('+1 day'));
    $duration = $planData['duration'] ?? 1;
    
    $stmt->bind_param("isisssssiddssissssssss", 
        $userId, 
        $mainTraveler['name'], 
        $mainTraveler['age'], 
        $mainTraveler['gender'], 
        $type, 
        $source, 
        $destination, 
        $date, 
        $numTravelers, 
        $totalCost, 
        $perPerson, 
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
        $destination
    );
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to store booking: " . $stmt->error);
    }
    
    $dbBookingId = $conn->insert_id;
    
    // Update user's bookings JSON file
    require_once 'update_user_bookings.php';
    updateUserBookingsFile($userId, $conn);
    
    // Store traveler details in a separate table or as JSON
    // For now, we'll store additional traveler info in the booking record
    
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
        $stmt->bind_param("ssd", $dbBookingId, $order->id, $totalCost);
        $stmt->execute();
        
        // Return success with payment details
        echo json_encode([
            'status' => 'success',
            'msg' => 'Booking created successfully! Please complete payment.',
            'booking_id' => $dbBookingId,
            'plan_name' => $planData['planType'] ?? 'Custom Plan',
            'num_travelers' => $numTravelers,
            'total_amount' => $totalCost,
            'payment_order_id' => $order->id,
            'payment_amount' => $testAmount,
            'key_id' => 'rzp_live_2JdrplZN9MSywf'
        ]);
        
    } catch (Exception $e) {
        // If payment fails, still return booking success but with payment error
        echo json_encode([
            'status' => 'success',
            'msg' => 'Booking created successfully! Payment initialization failed.',
            'booking_id' => $dbBookingId,
            'plan_name' => $planData['planType'] ?? 'Custom Plan',
            'num_travelers' => $numTravelers,
            'total_amount' => $totalCost,
            'payment_error' => $e->getMessage()
        ]);
    }
}

// Handle destination-only plan booking
function handleDestinationOnlyBooking() {
    global $conn;
    
    // WALLET DEDUCTION LOGIC START
    $userId = $_SESSION['user_id'];
    $travelers = intval($_POST['travelers'] ?? 1);
    $totalCost = 5000 * $travelers;
    $userRes = $conn->query("SELECT wallet_balance FROM users WHERE id = $userId");
    $userRow = $userRes ? $userRes->fetch_assoc() : null;
    if (!$userRow || $userRow['wallet_balance'] < $totalCost) {
        echo json_encode(['status' => 'error', 'msg' => 'Insufficient wallet balance. Please add funds to your wallet.']);
        exit();
    }
    $conn->query("UPDATE users SET wallet_balance = wallet_balance - $totalCost WHERE id = $userId");
    $stmtWallet = $conn->prepare("INSERT INTO wallet_transactions (user_id, amount, type, description) VALUES (?, ?, 'debit', ?)");
    $desc = 'Booking deduction for destination-only plan';
    $stmtWallet->bind_param("ids", $userId, $totalCost, $desc);
    $stmtWallet->execute();
    // WALLET DEDUCTION LOGIC END
    // --- Sync wallet table for admin dashboard ---
    $stmtWalletSync = $conn->prepare("INSERT INTO wallet (user_id, balance, last_updated) VALUES (?, (SELECT wallet_balance FROM users WHERE id = ?), NOW()) ON DUPLICATE KEY UPDATE balance = (SELECT wallet_balance FROM users WHERE id = ?), last_updated = NOW()");
    $stmtWalletSync->bind_param("iii", $userId, $userId, $userId);
    $stmtWalletSync->execute();
    // --- End wallet table sync ---
    
    // Get plan data
    $destination = $_POST['destination'] ?? '';
    $startDate = $_POST['start_date'] ?? '';
    $endDate = $_POST['end_date'] ?? '';
    $travelStyle = $_POST['travel_style'] ?? 'standard';
    $currency = $_POST['currency'] ?? 'INR';
    $contactMobile = $_POST['contact_mobile'] ?? '';
    $contactEmail = $_POST['contact_email'] ?? '';
    $travelDate = $_POST['travel_date'] ?? '';
    
    // Validate required fields
    if (empty($destination) || empty($startDate) || empty($endDate) || empty($contactMobile) || empty($contactEmail)) {
        echo json_encode(['status' => 'error', 'msg' => 'Please fill in all required fields']);
        exit();
    }
    
    // Collect traveler information
    $travelerData = [];
    for ($i = 1; $i <= $travelers; $i++) {
        if (!isset($_POST["traveler_name_$i"], $_POST["traveler_age_$i"], $_POST["traveler_gender_$i"])) {
            echo json_encode(['status' => 'error', 'msg' => "Missing traveler info for traveler $i"]);
            exit();
        }
        
        $traveler = [
            'name' => $_POST["traveler_name_$i"],
            'age' => $_POST["traveler_age_$i"],
            'gender' => $_POST["traveler_gender_$i"],
            'mobile' => $contactMobile,
            'email' => $contactEmail
        ];
        
        $travelerData[] = $traveler;
    }
    
    // Calculate estimated costs (you can enhance this based on your destination data)
    $estimatedCostPerPerson = 5000; // Base cost, can be enhanced
    $totalCost = $estimatedCostPerPerson * $travelers;
    
    // Store booking in database
    $stmt = $conn->prepare("INSERT INTO bookings (user_id, name, age, gender, type, source, destination, date, num_travelers, fare, per_person) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $mainTraveler = $travelerData[0];
    $type = 'destination_only_plan';
    $source = 'Direct to destination';
    $date = $travelDate;
    $isInternational = false; // Destination-only plans are typically domestic
    
    $stmt->bind_param("isisssssidd", 
        $userId, 
        $mainTraveler['name'], 
        $mainTraveler['age'], 
        $mainTraveler['gender'], 
        $type, 
        $source, 
        $destination, 
        $date, 
        $travelers, 
        $totalCost, 
        $estimatedCostPerPerson
    );
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to store booking: " . $stmt->error);
    }
    
    // Get the inserted booking ID
    $bookingId = $conn->insert_id;
    
    // Update user's bookings JSON file
    require_once 'update_user_bookings.php';
    updateUserBookingsFile($userId, $conn);
    
    // Return success response
    echo json_encode([
        'status' => 'success',
        'msg' => "Destination plan booking confirmed! Booking ID: $bookingId",
        'booking_id' => $bookingId,
        'total_cost' => $totalCost,
        'plan_type' => 'destination_only'
    ]);
}

// Handle complete journey plan booking
function handleCompleteJourneyBooking() {
    global $conn;
    
    // WALLET DEDUCTION LOGIC START
    $userId = $_SESSION['user_id'];
    $totalCostForAll = floatval($_POST['total_cost_for_all'] ?? 0);
    if ($totalCostForAll <= 0) $totalCostForAll = 5000;
    $userRes = $conn->query("SELECT wallet_balance FROM users WHERE id = $userId");
    $userRow = $userRes ? $userRes->fetch_assoc() : null;
    if (!$userRow || $userRow['wallet_balance'] < $totalCostForAll) {
        echo json_encode(['status' => 'error', 'msg' => 'Insufficient wallet balance. Please add funds to your wallet.']);
        exit();
    }
    $conn->query("UPDATE users SET wallet_balance = wallet_balance - $totalCostForAll WHERE id = $userId");
    $stmtWallet = $conn->prepare("INSERT INTO wallet_transactions (user_id, amount, type, description) VALUES (?, ?, 'debit', ?)");
    $desc = 'Booking deduction for complete journey plan';
    $stmtWallet->bind_param("ids", $userId, $totalCostForAll, $desc);
    $stmtWallet->execute();
    // WALLET DEDUCTION LOGIC END
    // --- Sync wallet table for admin dashboard ---
    $stmtWalletSync = $conn->prepare("INSERT INTO wallet (user_id, balance, last_updated) VALUES (?, (SELECT wallet_balance FROM users WHERE id = ?), NOW()) ON DUPLICATE KEY UPDATE balance = (SELECT wallet_balance FROM users WHERE id = ?), last_updated = NOW()");
    $stmtWalletSync->bind_param("iii", $userId, $userId, $userId);
    $stmtWalletSync->execute();
    // --- End wallet table sync ---
    
    // Get plan data
    $fromCity = $_POST['from_city'] ?? '';
    $toCity = $_POST['to_city'] ?? '';
    $startDate = $_POST['start_date'] ?? '';
    $endDate = $_POST['end_date'] ?? '';
    $travelers = intval($_POST['travelers'] ?? 1);
    $travelStyle = $_POST['travel_style'] ?? 'standard';
    $isInternational = $_POST['is_international'] ?? false;
    $totalCostPerPerson = floatval($_POST['total_cost_per_person'] ?? 0);
    $sourceToDestCost = floatval($_POST['source_to_dest_cost'] ?? 0);
    $sourceToDestMode = $_POST['source_to_dest_mode'] ?? '';
    $currency = $_POST['currency'] ?? 'INR';
    $contactMobile = $_POST['contact_mobile'] ?? '';
    $contactEmail = $_POST['contact_email'] ?? '';
    $travelDate = $_POST['travel_date'] ?? '';
    
    // Validate required fields
    if (empty($toCity) || empty($startDate) || empty($endDate) || empty($contactMobile) || empty($contactEmail)) {
        echo json_encode(['status' => 'error', 'msg' => 'Please fill in all required fields']);
        exit();
    }
    
    // Collect traveler information
    $travelerData = [];
    for ($i = 1; $i <= $travelers; $i++) {
        if (!isset($_POST["traveler_name_$i"], $_POST["traveler_age_$i"], $_POST["traveler_gender_$i"])) {
            echo json_encode(['status' => 'error', 'msg' => "Missing traveler info for traveler $i"]);
            exit();
        }
        
        $traveler = [
            'name' => $_POST["traveler_name_$i"],
            'age' => $_POST["traveler_age_$i"],
            'gender' => $_POST["traveler_gender_$i"],
            'mobile' => $contactMobile,
            'email' => $contactEmail
        ];
        
        // Add international-specific fields
        if ($isInternational) {
            if (!isset($_POST["traveler_passport_$i"], $_POST["traveler_nationality_$i"])) {
                echo json_encode(['status' => 'error', 'msg' => 'Passport and nationality required for international travel']);
                exit();
            }
            $traveler['passport'] = $_POST["traveler_passport_$i"];
            $traveler['nationality'] = $_POST["traveler_nationality_$i"];
        }
        
        $travelerData[] = $traveler;
    }
    
    // Check if date is in the past
    $selectedDate = new DateTime($travelDate);
    $today = new DateTime();
    $today->setTime(0, 0, 0);
    
    if ($selectedDate < $today) {
        echo json_encode(['status' => 'error', 'msg' => 'Cannot book for past dates. Please select today\'s date or a future date.']);
        exit();
    }
    
    // Generate booking ID
    $bookingId = 'CJ' . strtoupper(bin2hex(random_bytes(4))) . date('Ymd');
    
    // Store booking in database
    $stmt = $conn->prepare("INSERT INTO bookings (user_id, name, age, gender, type, source, destination, date, num_travelers, fare, per_person) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $mainTraveler = $travelerData[0];
    $type = 'complete_journey_plan';
    $source = $fromCity ?: 'Direct to destination';
    $date = $travelDate;
    
    $stmt->bind_param("isisssssidd", 
        $userId, 
        $mainTraveler['name'], 
        $mainTraveler['age'], 
        $mainTraveler['gender'], 
        $type, 
        $source, 
        $toCity, 
        $date, 
        $travelers, 
        $totalCostForAll, 
        $totalCostPerPerson
    );
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to store booking: " . $stmt->error);
    }
    
    // Get the inserted booking ID
    $bookingId = $conn->insert_id;
    
    // Update user's bookings JSON file
    require_once 'update_user_bookings.php';
    updateUserBookingsFile($userId, $conn);
    
    // Return success response
    echo json_encode([
        'status' => 'success',
        'msg' => "Complete journey plan booking confirmed! Booking ID: $bookingId",
        'booking_id' => $bookingId,
        'total_cost' => $totalCostForAll,
        'plan_type' => 'complete_journey'
    ]);
}

// Handle enhanced travel plan booking (existing logic)
function handleEnhancedTravelBooking() {
    global $conn;
    
    // WALLET DEDUCTION LOGIC START
    $userId = $_SESSION['user_id'];
    $totalCostForAll = floatval($_POST['total_cost_for_all'] ?? 0);
    if ($totalCostForAll <= 0) $totalCostForAll = 5000;
    $userRes = $conn->query("SELECT wallet_balance FROM users WHERE id = $userId");
    $userRow = $userRes ? $userRes->fetch_assoc() : null;
    if (!$userRow || $userRow['wallet_balance'] < $totalCostForAll) {
        echo json_encode(['status' => 'error', 'msg' => 'Insufficient wallet balance. Please add funds to your wallet.']);
        exit();
    }
    $conn->query("UPDATE users SET wallet_balance = wallet_balance - $totalCostForAll WHERE id = $userId");
    $stmtWallet = $conn->prepare("INSERT INTO wallet_transactions (user_id, amount, type, description) VALUES (?, ?, 'debit', ?)");
    $desc = 'Booking deduction for enhanced travel plan';
    $stmtWallet->bind_param("ids", $userId, $totalCostForAll, $desc);
    $stmtWallet->execute();
    // WALLET DEDUCTION LOGIC END
    // --- Sync wallet table for admin dashboard ---
    $stmtWalletSync = $conn->prepare("INSERT INTO wallet (user_id, balance, last_updated) VALUES (?, (SELECT wallet_balance FROM users WHERE id = ?), NOW()) ON DUPLICATE KEY UPDATE balance = (SELECT wallet_balance FROM users WHERE id = ?), last_updated = NOW()");
    $stmtWalletSync->bind_param("iii", $userId, $userId, $userId);
    $stmtWalletSync->execute();
    // --- End wallet table sync ---
    
    // Get plan data
    $fromCity = $_POST['from_city'] ?? '';
    $toCity = $_POST['to_city'] ?? '';
    $startDate = $_POST['start_date'] ?? '';
    $endDate = $_POST['end_date'] ?? '';
    $travelers = intval($_POST['travelers'] ?? 1);
    $travelStyle = $_POST['travel_style'] ?? 'standard';
    $isInternational = $_POST['is_international'] ?? false;
    $totalCostPerPerson = floatval($_POST['total_cost_per_person'] ?? 0);
    $sourceToDestCost = floatval($_POST['source_to_dest_cost'] ?? 0);
    $sourceToDestMode = $_POST['source_to_dest_mode'] ?? '';
    $currency = $_POST['currency'] ?? 'INR';
    $contactMobile = $_POST['contact_mobile'] ?? '';
    $contactEmail = $_POST['contact_email'] ?? '';
    $travelDate = $_POST['travel_date'] ?? '';
    
    // Validate required fields
    if (empty($toCity) || empty($startDate) || empty($endDate) || empty($contactMobile) || empty($contactEmail)) {
        echo json_encode(['status' => 'error', 'msg' => 'Please fill in all required fields']);
        exit();
    }
    
    // Collect traveler information
    $travelerData = [];
    for ($i = 1; $i <= $travelers; $i++) {
        if (!isset($_POST["traveler_name_$i"], $_POST["traveler_age_$i"], $_POST["traveler_gender_$i"])) {
            echo json_encode(['status' => 'error', 'msg' => "Missing traveler info for traveler $i"]);
            exit();
        }
        
        $traveler = [
            'name' => $_POST["traveler_name_$i"],
            'age' => $_POST["traveler_age_$i"],
            'gender' => $_POST["traveler_gender_$i"],
            'mobile' => $contactMobile,
            'email' => $contactEmail
        ];
        
        // Add international-specific fields
        if ($isInternational) {
            if (!isset($_POST["traveler_passport_$i"], $_POST["traveler_nationality_$i"])) {
                echo json_encode(['status' => 'error', 'msg' => 'Passport and nationality required for international travel']);
                exit();
            }
            $traveler['passport'] = $_POST["traveler_passport_$i"];
            $traveler['nationality'] = $_POST["traveler_nationality_$i"];
        }
        
        $travelerData[] = $traveler;
    }
    
    // Check if date is in the past
    $selectedDate = new DateTime($travelDate);
    $today = new DateTime();
    $today->setTime(0, 0, 0);
    
    if ($selectedDate < $today) {
        echo json_encode(['status' => 'error', 'msg' => 'Cannot book for past dates. Please select today\'s date or a future date.']);
        exit();
    }
    
    // Store booking in database
    $stmt = $conn->prepare("INSERT INTO bookings (user_id, name, age, gender, type, source, destination, date, num_travelers, fare, per_person) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $mainTraveler = $travelerData[0];
    $type = 'enhanced_travel_plan';
    $source = $fromCity ?: 'Direct to destination';
    $date = $travelDate;
    
    $stmt->bind_param("isisssssidd", 
        $userId, 
        $mainTraveler['name'], 
        $mainTraveler['age'], 
        $mainTraveler['gender'], 
        $type, 
        $source, 
        $toCity, 
        $date, 
        $travelers, 
        $totalCostForAll, 
        $totalCostPerPerson
    );
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to store booking: " . $stmt->error);
    }
    
    // Get the inserted booking ID
    $bookingId = $conn->insert_id;
    
    // Update user's bookings JSON file
    require_once 'update_user_bookings.php';
    updateUserBookingsFile($userId, $conn);
    
    // Return success response
    echo json_encode([
        'status' => 'success',
        'msg' => "Enhanced travel plan booking confirmed! Booking ID: $bookingId",
        'booking_id' => $bookingId,
        'total_cost' => $totalCostForAll,
        'plan_type' => 'enhanced_travel_plan'
    ]);
}
?> 