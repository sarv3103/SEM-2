<?php
session_start();
require_once 'php/config.php';
require_once 'php/razorpay_config.php';

echo "<h2>Payment Verification Debug Test</h2>";

// Test 1: Check database connection
echo "<h3>1. Database Connection Test</h3>";
if ($conn->connect_error) {
    echo "❌ Database connection failed: " . $conn->connect_error . "<br>";
} else {
    echo "✅ Database connection successful<br>";
}

// Test 2: Check if payment_orders table exists
echo "<h3>2. Payment Orders Table Test</h3>";
$result = $conn->query("SHOW TABLES LIKE 'payment_orders'");
if ($result && $result->num_rows > 0) {
    echo "✅ payment_orders table exists<br>";
    
    // Check table structure
    $result = $conn->query("DESCRIBE payment_orders");
    echo "Table structure:<br>";
    while ($row = $result->fetch_assoc()) {
        echo "- {$row['Field']}: {$row['Type']}<br>";
    }
} else {
    echo "❌ payment_orders table does not exist<br>";
}

// Test 3: Check if bookings table has required columns
echo "<h3>3. Bookings Table Test</h3>";
$result = $conn->query("SHOW TABLES LIKE 'bookings'");
if ($result && $result->num_rows > 0) {
    echo "✅ bookings table exists<br>";
    
    // Check for payment_status column
    $result = $conn->query("SHOW COLUMNS FROM bookings LIKE 'payment_status'");
    if ($result && $result->num_rows > 0) {
        echo "✅ payment_status column exists<br>";
    } else {
        echo "❌ payment_status column missing<br>";
    }
    
    // Check for payment_date column
    $result = $conn->query("SHOW COLUMNS FROM bookings LIKE 'payment_date'");
    if ($result && $result->num_rows > 0) {
        echo "✅ payment_date column exists<br>";
    } else {
        echo "❌ payment_date column missing<br>";
    }
    
    // Check for ticket_sent column
    $result = $conn->query("SHOW COLUMNS FROM bookings LIKE 'ticket_sent'");
    if ($result && $result->num_rows > 0) {
        echo "✅ ticket_sent column exists<br>";
    } else {
        echo "❌ ticket_sent column missing<br>";
    }
} else {
    echo "❌ bookings table does not exist<br>";
}

// Test 4: Check Razorpay configuration
echo "<h3>4. Razorpay Configuration Test</h3>";
try {
    $razorpay = new RazorpayService();
    echo "✅ Razorpay service initialized successfully<br>";
    echo "Key ID: " . substr($key_id, 0, 10) . "...<br>";
    echo "Key Secret: " . substr($key_secret, 0, 10) . "...<br>";
} catch (Exception $e) {
    echo "❌ Razorpay service initialization failed: " . $e->getMessage() . "<br>";
}

// Test 5: Check recent bookings and payment orders
echo "<h3>5. Recent Bookings and Payment Orders</h3>";
$result = $conn->query("SELECT * FROM bookings ORDER BY id DESC LIMIT 5");
if ($result && $result->num_rows > 0) {
    echo "Recent bookings:<br>";
    while ($row = $result->fetch_assoc()) {
        $paymentStatus = isset($row['payment_status']) ? $row['payment_status'] : 'N/A';
        echo "- ID: {$row['id']}, Booking ID: {$row['booking_id']}, Payment Status: {$paymentStatus}<br>";
    }
} else {
    echo "No recent bookings found<br>";
}

$result = $conn->query("SELECT * FROM payment_orders ORDER BY id DESC LIMIT 5");
if ($result && $result->num_rows > 0) {
    echo "Recent payment orders:<br>";
    while ($row = $result->fetch_assoc()) {
        echo "- ID: {$row['id']}, Booking ID: {$row['booking_id']}, Order ID: {$row['razorpay_order_id']}, Status: {$row['status']}<br>";
    }
} else {
    echo "No recent payment orders found<br>";
}

// Test 6: Manual payment verification test
echo "<h3>6. Manual Payment Verification Test</h3>";
echo "<form method='post'>";
echo "Payment ID: <input type='text' name='test_payment_id' placeholder='Enter payment ID'><br>";
echo "Order ID: <input type='text' name='test_order_id' placeholder='Enter order ID'><br>";
echo "<input type='submit' name='test_verify' value='Test Verification'>";
echo "</form>";

if (isset($_POST['test_verify'])) {
    $payment_id = $_POST['test_payment_id'];
    $order_id = $_POST['test_order_id'];
    
    if (!empty($payment_id) && !empty($order_id)) {
        echo "<h4>Testing verification for Payment ID: $payment_id, Order ID: $order_id</h4>";
        
        try {
            // Test Razorpay API call
            $api = new Razorpay\Api\Api($key_id, $key_secret);
            $payment = $api->payment->fetch($payment_id);
            echo "✅ Razorpay payment fetch successful<br>";
            echo "Payment Status: " . $payment->status . "<br>";
            echo "Payment Amount: " . $payment->amount . " paise<br>";
            echo "Order ID: " . $payment->order_id . "<br>";
            
            // Test database lookup
            $stmt = $conn->prepare("SELECT * FROM payment_orders WHERE razorpay_order_id = ?");
            $stmt->bind_param("s", $order_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $orderData = $result->fetch_assoc();
                echo "✅ Payment order found in database<br>";
                echo "Booking ID: " . $orderData['booking_id'] . "<br>";
                echo "Status: " . $orderData['status'] . "<br>";
            } else {
                echo "❌ Payment order not found in database<br>";
            }
            
        } catch (Exception $e) {
            echo "❌ Error: " . $e->getMessage() . "<br>";
        }
    } else {
        echo "Please enter both Payment ID and Order ID<br>";
    }
}

echo "<br><strong>Debug test complete!</strong>";
?> 