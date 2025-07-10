<?php
session_start(); // FIRST LINE, before any output

// Enable error reporting for debugging
ini_set('display_errors', 0); // Disable display errors to prevent HTML output
ini_set('log_errors', 1);
error_reporting(E_ALL);

require_once 'config.php';
require_once 'razorpay_config.php';
header('Content-Type: application/json');

try {
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
    
    // Validate required fields
    $required_fields = ['source', 'destination', 'date', 'num_travelers', 'selected_mode', 'selected_fare', 'contact_name', 'contact_mobile', 'contact_email', 'travelers'];
    foreach ($required_fields as $field) {
        if (empty($input[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }
    
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('User not logged in');
    }
    
    $user_id = $_SESSION['user_id'];
    
    // Create database connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // WALLET DEDUCTION LOGIC START
    $fare = floatval($input['selected_fare']);
    $userRes = $conn->query("SELECT wallet_balance FROM users WHERE id = $user_id");
    $userRow = $userRes ? $userRes->fetch_assoc() : null;
    if (!$userRow || $userRow['wallet_balance'] < $fare) {
        echo json_encode(['status' => 'error', 'message' => 'Insufficient wallet balance. Please add funds to your wallet.']);
        exit();
    }
    // Deduct from wallet
    $conn->query("UPDATE users SET wallet_balance = wallet_balance - $fare WHERE id = $user_id");
    // Add wallet transaction
    $stmtWallet = $conn->prepare("INSERT INTO wallet_transactions (user_id, amount, type, description) VALUES (?, ?, 'debit', ?)");
    $desc = 'Booking deduction for travel booking';
    $stmtWallet->bind_param("ids", $user_id, $fare, $desc);
    $stmtWallet->execute();
    // WALLET DEDUCTION LOGIC END
    // --- Sync wallet table for admin dashboard ---
    $stmtWalletSync = $conn->prepare("INSERT INTO wallet (user_id, balance, last_updated) VALUES (?, (SELECT wallet_balance FROM users WHERE id = ?), NOW()) ON DUPLICATE KEY UPDATE balance = (SELECT wallet_balance FROM users WHERE id = ?), last_updated = NOW()");
    $stmtWalletSync->bind_param("iii", $user_id, $user_id, $user_id);
    $stmtWalletSync->execute();
    // --- End wallet table sync ---
    
    // Generate booking ID
    $booking_id = 'TRAVEL' . date('Ymd') . rand(1000, 9999);
    
    // Insert main booking record
    $stmt = $conn->prepare("INSERT INTO bookings (booking_id, user_id, booking_type, source, destination, date, num_travelers, type, fare, contact_mobile, contact_email) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $booking_type = 'destination';
    $stmt->bind_param("sisssisssss", 
        $booking_id,
        $user_id,
        $booking_type,
        $input['source'],
        $input['destination'],
        $input['date'],
        $input['num_travelers'],
        $input['selected_mode'],
        $input['selected_fare'],
        $input['contact_mobile'],
        $input['contact_email']
    );
    
    if (!$stmt->execute()) {
        throw new Exception("Error creating booking: " . $stmt->error);
    }
    
    $booking_db_id = $conn->insert_id;
    
    // Update user's bookings JSON file
    require_once 'update_user_bookings.php';
    updateUserBookingsFile($user_id, $conn);
    
    // Insert traveler details
    if (!empty($input['travelers'])) {
        $traveler_stmt = $conn->prepare("INSERT INTO traveler_details (booking_id, traveler_number, name, age, gender) VALUES (?, ?, ?, ?, ?)");
        
        foreach ($input['travelers'] as $index => $traveler) {
            $traveler_number = $index + 1;
            $traveler_stmt->bind_param("iisss", 
                $booking_db_id,
                $traveler_number,
                $traveler['name'],
                $traveler['age'],
                $traveler['gender']
            );
            
            if (!$traveler_stmt->execute()) {
                throw new Exception("Error saving traveler details: " . $traveler_stmt->error);
            }
        }
    }
    
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
        $stmt->bind_param("ssd", $booking_db_id, $order->id, $input['selected_fare']);
        $stmt->execute();
        
        // Generate PDF ticket
        $ticket_data = [
            'booking_id' => $booking_id,
            'source' => $input['source'],
            'destination' => $input['destination'],
            'date' => $input['date'],
            'mode' => $input['selected_mode'],
            'travelers' => $input['travelers'],
            'contact_name' => $input['contact_name'],
            'contact_email' => $input['contact_email'],
            'contact_mobile' => $input['contact_mobile'],
            'total_amount' => $input['selected_fare'],
            'booking_type' => 'Travel'
        ];
        
        // Save ticket data for PDF generation
        $ticket_file = "user_bookings/travel_" . $booking_id . ".json";
        file_put_contents($ticket_file, json_encode($ticket_data));
        
        // Send confirmation email
        $to = $input['contact_email'];
        $subject = "Travel Booking Confirmation - $booking_id";
        $message = "
        Dear {$input['contact_name']},
        
        Your travel booking has been confirmed!
        
        Booking Details:
        - Booking ID: $booking_id
        - From: {$input['source']}
        - To: {$input['destination']}
        - Date: {$input['date']}
        - Mode: {$input['selected_mode']}
        - Travelers: {$input['num_travelers']}
        - Total Amount: ₹{$input['selected_fare']}
        
        Your ticket will be sent separately after payment confirmation.
        
        Thank you for choosing TravelPlanner!
        
        Best regards,
        TravelPlanner Team
        ";
        
        $headers = "From: sarveshtravelplanner@gmail.com\r\n";
        $headers .= "Reply-To: sarveshtravelplanner@gmail.com\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        
        mail($to, $subject, $message, $headers);
        
        echo json_encode([
            'status' => 'success',
            'booking_id' => $booking_db_id,
            'message' => 'Booking created successfully. Please complete payment.',
            'payment_order_id' => $order->id,
            'payment_amount' => $testAmount,
            'key_id' => 'rzp_live_2JdrplZN9MSywf'
        ]);
        
    } catch (Exception $e) {
        // If payment fails, still return booking success but with payment error
        echo json_encode([
            'status' => 'success',
            'booking_id' => $booking_db_id,
            'message' => 'Booking created successfully. Payment initialization failed.',
            'payment_error' => $e->getMessage()
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?> 