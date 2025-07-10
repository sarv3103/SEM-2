<?php
require_once 'php/config.php';
require_once 'php/razorpay_config.php';

echo "<h2>Simple Payment Verification Test</h2>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_id = $_POST['payment_id'] ?? '';
    $order_id = $_POST['order_id'] ?? '';
    
    if (!empty($payment_id) && !empty($order_id)) {
        try {
            $api = new Razorpay\Api\Api($key_id, $key_secret);
            $payment = $api->payment->fetch($payment_id);
            
            echo "<h3>Payment Details:</h3>";
            echo "Status: " . $payment->status . "<br>";
            echo "Amount: " . $payment->amount . " paise<br>";
            echo "Order ID: " . $payment->order_id . "<br>";
            
            if ($payment->status === 'captured') {
                // Update database
                $stmt = $conn->prepare("UPDATE payment_orders SET status = 'completed', razorpay_payment_id = ? WHERE razorpay_order_id = ?");
                $stmt->bind_param("ss", $payment_id, $order_id);
                $stmt->execute();
                
                $stmt = $conn->prepare("SELECT booking_id FROM payment_orders WHERE razorpay_order_id = ?");
                $stmt->bind_param("s", $order_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $orderData = $result->fetch_assoc();
                
                if ($orderData) {
                    $stmt = $conn->prepare("UPDATE bookings SET payment_status = 'paid', payment_date = NOW() WHERE id = ?");
                    $stmt->bind_param("i", $orderData['booking_id']);
                    $stmt->execute();
                    
                    echo "<div style='color: green; font-weight: bold;'>✅ Payment verified and database updated successfully!</div>";
                }
            } else {
                echo "<div style='color: red;'>❌ Payment not captured</div>";
            }
            
        } catch (Exception $e) {
            echo "<div style='color: red;'>❌ Error: " . $e->getMessage() . "</div>";
        }
    }
}

echo "<form method='post'>
    <h3>Test Payment Verification</h3>
    Payment ID: <input type='text' name='payment_id' placeholder='Enter payment ID' required><br><br>
    Order ID: <input type='text' name='order_id' placeholder='Enter order ID' required><br><br>
    <input type='submit' value='Verify Payment'>
</form>";
?> 