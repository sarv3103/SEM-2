<?php
require_once 'vendor/autoload.php';
require_once 'php/razorpay_config.php';

use Razorpay\Api\Api;

echo "<h2>Razorpay Connection Test</h2>";

try {
    // Test 1: Check if we can create an API instance
    echo "<h3>Test 1: Creating API Instance</h3>";
    $api = new Api('rzp_live_2JdrplZN9MSywf', '8JHRkWgt4C286TQoNZErbmdK');
    echo "✅ API instance created successfully<br>";
    
    // Test 2: Check if we can create an order
    echo "<h3>Test 2: Creating Test Order</h3>";
    $orderData = [
        'receipt' => 'test_' . time(),
        'amount' => 100, // ₹1 in paise
        'currency' => 'INR'
    ];
    
    $order = $api->order->create($orderData);
    echo "✅ Order created successfully<br>";
    echo "Order ID: " . $order->id . "<br>";
    echo "Amount: ₹1<br>";
    
    // Test 3: Check if we can fetch the order
    echo "<h3>Test 3: Fetching Order</h3>";
    $fetchedOrder = $api->order->fetch($order->id);
    echo "✅ Order fetched successfully<br>";
    echo "Fetched Order ID: " . $fetchedOrder->id . "<br>";
    
    echo "<h3>✅ All Tests Passed!</h3>";
    echo "<p>Razorpay integration is working correctly.</p>";
    
    // Create a test payment button
    echo "<h3>Test Payment</h3>";
    echo "<button onclick='testPayment()' style='padding: 10px 20px; background: #0077cc; color: white; border: none; border-radius: 5px; cursor: pointer;'>Test Payment</button>";
    
    echo "<script src='https://checkout.razorpay.com/v1/checkout.js'></script>";
    echo "<script>
        function testPayment() {
            const options = {
                key: 'rzp_live_2JdrplZN9MSywf',
                amount: 100,
                currency: 'INR',
                name: 'TravelPlanner Test',
                description: 'Connection Test (₹1)',
                order_id: '" . $order->id . "',
                handler: function(response) {
                    alert('Payment successful!\\nPayment ID: ' + response.razorpay_payment_id + '\\nAmount: ₹1');
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
    </script>";
    
} catch (Exception $e) {
    echo "<h3>❌ Test Failed</h3>";
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
    
    echo "<h3>Debug Information:</h3>";
    echo "<p>Error Type: " . get_class($e) . "</p>";
    echo "<p>Error Code: " . $e->getCode() . "</p>";
    echo "<p>File: " . $e->getFile() . "</p>";
    echo "<p>Line: " . $e->getLine() . "</p>";
    
    echo "<h3>Possible Solutions:</h3>";
    echo "<ul>";
    echo "<li>Check your internet connection</li>";
    echo "<li>Verify Razorpay service is accessible</li>";
    echo "<li>Try using your own test keys from Razorpay Dashboard</li>";
    echo "<li>Check if your server can make outbound HTTPS requests</li>";
    echo "</ul>";
    
    echo "<p><a href='get_razorpay_keys.php'>Get Your Own Test Keys</a></p>";
}
?> 