<?php
session_start();
require_once 'php/razorpay_config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "Please login first";
    exit();
}

// Test payment data
$testAmount = 100; // ₹1 for testing

try {
    // Initialize Razorpay
    $razorpay = new RazorpayService();
    
    // Create test order
    $order = $razorpay->createOrder($testAmount);
    
    echo "<h2>Simple Payment Test</h2>";
    echo "<p>Order created successfully!</p>";
    echo "<p>Order ID: " . $order->id . "</p>";
    echo "<p>Amount: ₹" . $testAmount . "</p>";
    
    // Create payment button
    echo "<button onclick='initiatePayment()' style='padding: 10px 20px; background: #0077cc; color: white; border: none; border-radius: 5px; cursor: pointer;'>Test Payment</button>";
    
    echo "<script src='https://checkout.razorpay.com/v1/checkout.js'></script>";
    echo "<script>
        function initiatePayment() {
            const options = {
                key: 'rzp_live_2JdrplZN9MSywf', // Live key
                amount: " . ($testAmount * 100) . ",
                currency: 'INR',
                name: 'TravelPlanner',
                description: 'Test Payment (₹1)',
                order_id: '" . $order->id . "',
                handler: function(response) {
                    console.log('Payment successful:', response);
                    alert('Payment successful!\\nPayment ID: ' + response.razorpay_payment_id + '\\nOrder ID: ' + response.razorpay_order_id + '\\nAmount: ₹1');
                },
                prefill: {
                    name: 'Test User',
                    email: 'test@example.com',
                    contact: '9876543210'
                },
                theme: {
                    color: '#0077cc'
                },
                modal: {
                    ondismiss: function() {
                        console.log('Payment modal closed');
                    }
                }
            };
            
            console.log('Initializing Razorpay with options:', options);
            const rzp = new Razorpay(options);
            rzp.open();
        }
    </script>";
    
    echo "<div style='margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 5px;'>";
    echo "<h3>Test Instructions:</h3>";
    echo "<ul>";
    echo "<li>Click the 'Test Payment' button above</li>";
    echo "<li>Use any test card number: 4111 1111 1111 1111</li>";
    echo "<li>Any future expiry date</li>";
    echo "<li>Any 3-digit CVV</li>";
    echo "<li>Any name</li>";
    echo "</ul>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<h2>Error</h2>";
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
    echo "<p>Please check your Razorpay configuration.</p>";
    
    // Debug information
    echo "<h3>Debug Information:</h3>";
    echo "<p>PHP Version: " . phpversion() . "</p>";
    echo "<p>cURL Extension: " . (extension_loaded('curl') ? 'Loaded' : 'Not Loaded') . "</p>";
    echo "<p>OpenSSL Extension: " . (extension_loaded('openssl') ? 'Loaded' : 'Not Loaded') . "</p>";
    echo "<p>JSON Extension: " . (extension_loaded('json') ? 'Loaded' : 'Not Loaded') . "</p>";
    
    // Check if Razorpay class exists
    echo "<p>RazorpayService Class: " . (class_exists('RazorpayService') ? 'Exists' : 'Not Found') . "</p>";
}
?> 