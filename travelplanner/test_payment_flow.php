<?php
session_start();
require_once 'php/config.php';
require_once 'php/razorpay_config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "Please login first";
    exit();
}

// Test payment data
$testAmount = 1; // ₹1 for testing

try {
    // First create a test booking record
    $userId = $_SESSION['user_id'];
    $testBookingId = 'TEST_' . time();
    
    // Insert a test booking record
    $stmt = $conn->prepare("
        INSERT INTO bookings (
            user_id, name, age, gender, type, source, destination, date, 
            num_travelers, fare, per_person, booking_id, travel_style, 
            is_international, start_date, end_date, duration, contact_mobile, 
            contact_email, special_requirements, booking_type, destination_name
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $name = 'Test User';
    $age = 25;
    $gender = 'male';
    $type = 'test_booking';
    $source = 'Test Source';
    $destination = 'Test Destination';
    $date = date('Y-m-d');
    $numTravelers = 1;
    $fare = $testAmount;
    $perPerson = $testAmount;
    $travelStyle = 'standard';
    $isInternational = 0;
    $startDate = date('Y-m-d');
    $endDate = date('Y-m-d', strtotime('+1 day'));
    $duration = 1;
    $contactMobile = '9876543210';
    $contactEmail = 'test@example.com';
    $specialRequirements = 'Test booking';
    $bookingType = 'test';
    $destinationName = 'Test Destination';
    
    $stmt->bind_param("isisssssiddssissssssss", 
        $userId, $name, $age, $gender, $type, $source, $destination, $date, 
        $numTravelers, $fare, $perPerson, $testBookingId, $travelStyle, 
        $isInternational, $startDate, $endDate, $duration, $contactMobile, 
        $contactEmail, $specialRequirements, $bookingType, $destinationName
    );
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to create test booking: " . $stmt->error);
    }
    
    $bookingDbId = $conn->insert_id;
    
    // Initialize Razorpay
    $razorpay = new RazorpayService();
    
    // Create test order
    $order = $razorpay->createOrder($testAmount);
    $orderId = $order->id; // Store order ID in variable to avoid warning
    
    // Store in database using the actual booking ID
    $stmt = $conn->prepare("
        INSERT INTO payment_orders (booking_id, razorpay_order_id, amount, status, created_at) 
        VALUES (?, ?, ?, 'pending', NOW())
    ");
    $stmt->bind_param("ssd", $bookingDbId, $orderId, $testAmount);
    $stmt->execute();
    
    echo "<h2>Payment Test</h2>";
    echo "<p>Order created successfully!</p>";
    echo "<p>Order ID: " . $orderId . "</p>";
    echo "<p>Amount: ₹" . $testAmount . "</p>";
    echo "<p>Booking ID: " . $bookingDbId . "</p>";
    
    // Create payment button
    echo "<button onclick='initiatePayment()'>Test Payment</button>";
    
    echo "<script src='https://checkout.razorpay.com/v1/checkout.js'></script>";
    echo "<script>
        function initiatePayment() {
            const options = {
                key: 'rzp_live_2JdrplZN9MSywf', // Live key
                amount: " . ($testAmount * 100) . ",
                currency: 'INR',
                name: 'TravelPlanner Test',
                description: 'Test Payment (₹1)',
                order_id: '" . $orderId . "',
                handler: function(response) {
                    alert('Payment successful! Payment ID: ' + response.razorpay_payment_id + '\\nAmount: ₹1');
                    // Verify payment here
                    verifyPayment(response);
                },
                prefill: {
                    name: 'Test User',
                    email: 'test@example.com',
                    contact: '9876543210'
                },
                theme: {
                    color: '#0077cc'
                }
            };
            
            const rzp = new Razorpay(options);
            rzp.open();
        }
        
        function verifyPayment(response) {
            fetch('php/verify_payment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    razorpay_payment_id: response.razorpay_payment_id,
                    razorpay_order_id: response.razorpay_order_id,
                    razorpay_signature: response.razorpay_signature
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert('Payment verified successfully!');
                } else {
                    alert('Payment verification failed: ' + data.message);
                }
            })
            .catch(error => {
                alert('Error verifying payment: ' + error);
            });
        }
    </script>";
    
} catch (Exception $e) {
    echo "<h2>Error</h2>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "<p>Please check your Razorpay configuration.</p>";
    
    // Debug information
    echo "<h3>Debug Information:</h3>";
    echo "<p>Error Type: " . get_class($e) . "</p>";
    echo "<p>Error Code: " . $e->getCode() . "</p>";
    echo "<p>File: " . $e->getFile() . "</p>";
    echo "<p>Line: " . $e->getLine() . "</p>";
}
?> 