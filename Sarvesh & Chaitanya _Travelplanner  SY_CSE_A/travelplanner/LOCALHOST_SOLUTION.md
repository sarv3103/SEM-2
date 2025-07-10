# Localhost Payment Solution Guide

## Problem
Razorpay webhooks require HTTPS URLs, but you're using localhost. Here are the solutions:

## Solution 1: Use ngrok (Recommended for Development)

### Step 1: Install ngrok
```bash
# Already installed via winget
ngrok --version
```

### Step 2: Start ngrok tunnel
```bash
# Run the batch file
start_ngrok.bat

# Or manually:
ngrok http 80
```

### Step 3: Configure Razorpay webhook
1. Copy the HTTPS URL from ngrok (e.g., `https://abc123.ngrok.io`)
2. Go to Razorpay Dashboard > Settings > Webhooks
3. Add webhook URL: `https://abc123.ngrok.io/php/razorpay_webhook.php`
4. Select events: `payment.captured`, `payment.failed`, `order.paid`
5. Copy webhook secret and update in `php/razorpay_config.php`

### Step 4: Test webhook
1. Make a test payment
2. Check ngrok dashboard for webhook requests
3. Verify database updates

## Solution 2: Enhanced Manual Verification (No Webhooks)

### Features:
- Check payment status using Payment ID
- Manually verify and process payments
- Generate tickets and send emails
- View all pending/failed payments
- Complete payment history

### Usage:
1. Access `php/enhanced_payment_verification.php`
2. Enter Payment ID to check status
3. Enter Payment ID + Order ID to verify and process
4. System automatically generates ticket and sends email

### Workflow:
1. User pays ₹1 on Razorpay
2. Payment shows successful on Razorpay
3. Admin checks pending payments
4. Admin enters Payment ID and Order ID
5. System verifies with Razorpay
6. Updates database and generates ticket
7. Sends email to user

## Solution 3: Frontend Retry Mechanism

### Enhanced JavaScript:
```javascript
// Add retry mechanism to payment verification
function verifyPayment(paymentId, orderId, signature, retryCount = 0) {
    fetch('php/verify_payment.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            razorpay_payment_id: paymentId,
            razorpay_order_id: orderId,
            razorpay_signature: signature
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            // Show success message and ticket
            showTicket(data);
        } else {
            // Retry up to 3 times
            if (retryCount < 3) {
                setTimeout(() => {
                    verifyPayment(paymentId, orderId, signature, retryCount + 1);
                }, 2000);
            } else {
                // Show manual verification message
                showManualVerificationMessage(paymentId, orderId);
            }
        }
    })
    .catch(error => {
        console.error('Payment verification failed:', error);
        if (retryCount < 3) {
            setTimeout(() => {
                verifyPayment(paymentId, orderId, signature, retryCount + 1);
            }, 2000);
        } else {
            showManualVerificationMessage(paymentId, orderId);
        }
    });
}

function showManualVerificationMessage(paymentId, orderId) {
    const message = `
        <div class="alert alert-warning">
            <h4>Payment Processing</h4>
            <p>Your payment was successful on Razorpay. We're processing your booking.</p>
            <p><strong>Payment ID:</strong> ${paymentId}</p>
            <p><strong>Order ID:</strong> ${orderId}</p>
            <p>You will receive your ticket via email shortly. If you don't receive it within 10 minutes, please contact support.</p>
        </div>
    `;
    document.getElementById('payment-result').innerHTML = message;
}
```

## Solution 4: Database Monitoring Script

### Create monitoring script:
```php
<?php
// check_pending_payments.php
require_once 'config.php';

$stmt = $conn->prepare("
    SELECT po.*, b.booking_id, b.name, b.contact_email, b.destination, b.fare
    FROM payment_orders po
    JOIN bookings b ON po.booking_id = b.id
    WHERE po.status = 'pending' OR po.status = 'failed'
    ORDER BY po.created_at DESC
");

$stmt->execute();
$pendingPayments = $stmt->get_result();

echo "<h2>Pending Payments</h2>";
while ($payment = $pendingPayments->fetch_assoc()) {
    echo "Booking: " . $payment['booking_id'] . " - " . $payment['name'] . " - ₹" . $payment['fare'] . "<br>";
}
?>
```

## Recommended Approach for Localhost:

### 1. **Primary: Enhanced Manual Verification**
- Use `php/enhanced_payment_verification.php`
- Most reliable for localhost development
- Full control over payment processing
- Immediate ticket generation

### 2. **Secondary: ngrok for Testing**
- Use when you want to test webhooks
- Good for production-like testing
- Requires ngrok to be running

### 3. **Backup: Frontend Retry**
- Add retry mechanism to frontend
- Graceful handling of network issues
- User-friendly error messages

## Setup Instructions:

### 1. Run Database Update:
```bash
php add_webhook_columns.php
```

### 2. Access Admin Panel:
- Go to `php/enhanced_payment_verification.php`
- Login with admin credentials
- Start processing payments

### 3. Test Payment Flow:
1. Make test payment on frontend
2. Check pending payments in admin panel
3. Verify payment manually
4. Confirm ticket generation and email

## Benefits of This Approach:

✅ **Works on localhost** - No HTTPS required  
✅ **Reliable processing** - Manual verification ensures no lost payments  
✅ **Immediate feedback** - Admin can see all pending payments  
✅ **Ticket generation** - Automatic PDF generation and email  
✅ **Easy monitoring** - Clear view of payment status  
✅ **Production ready** - Can be used in production as backup  

## Files to Use:
- `php/enhanced_payment_verification.php` - Main verification system
- `php/admin_verify_payment.php` - Simple verification
- `start_ngrok.bat` - For webhook testing
- `add_webhook_columns.php` - Database setup

This solution ensures that **every payment gets processed** even on localhost, with multiple fallback mechanisms for reliability. 