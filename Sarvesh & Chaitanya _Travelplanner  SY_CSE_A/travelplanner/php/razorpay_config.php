<?php
// Razorpay Configuration
require_once __DIR__ . '/../vendor/autoload.php';

use Razorpay\Api\Api;

class RazorpayService {
    private $api;
    private $webhookSecret = 'your_webhook_secret_here'; // Replace with your actual webhook secret
    
    public function __construct() {
        // TEST MODE - Use these for testing (commented out for live)
        // $keyId = 'rzp_test_1DP5mmOlF5G5ag'; // Standard test key
        // $keySecret = 'thisisatestkey'; // Standard test secret
        
        // LIVE MODE - Use these for real payments
        $keyId = 'rzp_live_2JdrplZN9MSywf'; // Live Key ID
        $keySecret = '8JHRkWgt4C286TQoNZErbmdK'; // Live Key Secret
        
        $this->api = new Api($keyId, $keySecret);
    }
    
    public function createOrder($amount, $currency = 'INR') {
        try {
            $orderData = [
                'receipt' => 'rcptid_' . time(),
                'amount' => $amount * 100, // Razorpay expects amount in paise
                'currency' => $currency
            ];
            
            $order = $this->api->order->create($orderData);
            return $order;
        } catch (Exception $e) {
            error_log("Razorpay order creation error: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function verifyPayment($paymentId, $orderId, $signature) {
        try {
            $attributes = [
                'razorpay_payment_id' => $paymentId,
                'razorpay_order_id' => $orderId,
                'razorpay_signature' => $signature
            ];
            
            $this->api->utility->verifyPaymentSignature($attributes);
            return true;
        } catch (Exception $e) {
            error_log("Razorpay payment verification error: " . $e->getMessage());
            return false;
        }
    }
    
    public function getPaymentDetails($paymentId) {
        try {
            $payment = $this->api->payment->fetch($paymentId);
            return $payment;
        } catch (Exception $e) {
            error_log("Razorpay payment fetch error: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function verifyWebhookSignature($payload, $signature) {
        try {
            $this->api->utility->verifyWebhookSignature($payload, $signature, $this->webhookSecret);
            return true;
        } catch (Exception $e) {
            error_log("Webhook signature verification error: " . $e->getMessage());
            return false;
        }
    }
    
    public function getPaymentByOrderId($orderId) {
        try {
            $payments = $this->api->payment->all(['order_id' => $orderId]);
            if ($payments->count > 0) {
                return $payments->items[0];
            }
            return null;
        } catch (Exception $e) {
            error_log("Razorpay payment fetch by order error: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function getOrderDetails($orderId) {
        try {
            $order = $this->api->order->fetch($orderId);
            return $order;
        } catch (Exception $e) {
            error_log("Razorpay order fetch error: " . $e->getMessage());
            throw $e;
        }
    }
}

// Global variables for direct access (used by verify_payment.php)
// $key_id = 'rzp_test_1DP5mmOlF5G5ag'; // Test Key ID
// $key_secret = 'thisisatestkey'; // Test Key Secret
$key_id = 'rzp_live_2JdrplZN9MSywf'; // Live Key ID
$key_secret = '8JHRkWgt4C286TQoNZErbmdK'; // Live Key Secret
?> 