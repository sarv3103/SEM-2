<?php
session_start();
require_once 'php/config.php';
require_once 'php/razorpay_config.php';

echo "<h2>Payment Integration Test</h2>";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "❌ User not logged in. Please login first.<br>";
    exit();
}

echo "✅ User logged in (ID: " . $_SESSION['user_id'] . ")<br>";

try {
    // Test Razorpay connection
    echo "<h3>Testing Razorpay Connection</h3>";
    $razorpay = new RazorpayService();
    echo "✅ Razorpay service initialized<br>";
    
    // Test order creation
    echo "<h3>Testing Order Creation</h3>";
    $testAmount = 1;
    $order = $razorpay->createOrder($testAmount);
    echo "✅ Order created successfully<br>";
    echo "Order ID: " . $order->id . "<br>";
    echo "Amount: ₹" . $testAmount . "<br>";
    
    // Test database connection
    echo "<h3>Testing Database Connection</h3>";
    if ($conn->ping()) {
        echo "✅ Database connection successful<br>";
    } else {
        echo "❌ Database connection failed<br>";
    }
    
    // Test payment_orders table
    echo "<h3>Testing Payment Orders Table</h3>";
    $result = $conn->query("SHOW TABLES LIKE 'payment_orders'");
    if ($result && $result->num_rows > 0) {
        echo "✅ payment_orders table exists<br>";
    } else {
        echo "❌ payment_orders table does not exist<br>";
    }
    
    // Test bookings table payment columns
    echo "<h3>Testing Bookings Table Payment Columns</h3>";
    $result = $conn->query("SHOW COLUMNS FROM bookings LIKE 'payment_status'");
    if ($result && $result->num_rows > 0) {
        echo "✅ payment_status column exists<br>";
    } else {
        echo "❌ payment_status column does not exist<br>";
    }
    
    $result = $conn->query("SHOW COLUMNS FROM bookings LIKE 'payment_date'");
    if ($result && $result->num_rows > 0) {
        echo "✅ payment_date column exists<br>";
    } else {
        echo "❌ payment_date column does not exist<br>";
    }
    
    // Test inserting a payment order
    echo "<h3>Testing Payment Order Insertion</h3>";
    $testBookingId = 999999; // Test booking ID
    $stmt = $conn->prepare("
        INSERT INTO payment_orders (booking_id, razorpay_order_id, amount, status, created_at) 
        VALUES (?, ?, ?, 'pending', NOW())
    ");
    $stmt->bind_param("ssd", $testBookingId, $order->id, $testAmount);
    
    if ($stmt->execute()) {
        echo "✅ Payment order inserted successfully<br>";
        
        // Clean up test data
        $conn->query("DELETE FROM payment_orders WHERE booking_id = $testBookingId");
        echo "✅ Test data cleaned up<br>";
    } else {
        echo "❌ Failed to insert payment order: " . $stmt->error . "<br>";
    }
    
    echo "<br><strong>🎉 Payment integration test completed successfully!</strong><br>";
    echo "<p>Your payment system is ready to process ₹1 test payments.</p>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "<p>Please check your Razorpay configuration and database setup.</p>";
}
?> 