# OTP Verification System Setup Guide

## Overview
This guide will help you set up the complete OTP verification system for your TravelPlanner application. The system includes:

1. **Registration OTP** - Email and mobile verification
2. **Booking Confirmation OTP** - Email and mobile verification
3. **Payment Verification OTP** - Mobile OTP for secure payments
4. **Contact Form OTP** - Email verification for spam prevention
5. **Forgot Password OTP** - Email verification for password reset

## Step 1: Database Setup

### 1.1 Run Database Update Script
1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Select your `travelplanner` database
3. Go to the SQL tab
4. Copy and paste the contents of `database_update.sql`
5. Click "Go" to execute

### 1.2 Verify Database Changes
After running the script, you should have these new tables:
- `booking_otp` - For booking confirmation OTPs
- `payment_otp` - For payment verification OTPs
- `contact_otp` - For contact form OTPs
- `sms_log` - For SMS OTP logs (auto-created)

And these new columns in the `users` table:
- `first_name`, `last_name`, `mobile`
- `email_verified`, `mobile_verified`
- `email_otp`, `mobile_otp`
- `email_otp_expires`, `mobile_otp_expires`

## Step 2: Email Configuration (Free)

### 2.1 Configure PHP Mail Function
The system uses PHP's built-in `mail()` function which is free. To ensure emails work:

1. **For XAMPP**: 
   - Open `php.ini` in your XAMPP installation
   - Find the `[mail function]` section
   - Set `SMTP = localhost`
   - Set `smtp_port = 25`
   - Set `sendmail_from = your-email@domain.com`

2. **For Production**:
   - Consider using services like Gmail SMTP, SendGrid, or Mailgun
   - Update the `sendEmailOTP()` method in `php/otp_manager.php`

### 2.2 Test Email Functionality
Create a test file to verify email is working:

```php
<?php
$to = "your-email@example.com";
$subject = "Test Email";
$message = "This is a test email from TravelPlanner OTP system";
$headers = "From: noreply@travelplanner.com";

if(mail($to, $subject, $message, $headers)) {
    echo "Email sent successfully";
} else {
    echo "Email failed to send";
}
?>
```

## Step 3: SMS Configuration (Free Alternative)

### 3.1 Current Implementation
The system currently logs SMS OTPs to the database instead of sending actual SMS (to avoid costs). You can view these in the admin panel.

### 3.2 For Production SMS
To enable actual SMS sending, update the `sendSMSOTP()` method in `php/otp_manager.php`:

```php
// Example with Twilio (free trial available)
public function sendSMSOTP($mobile, $otp, $purpose = 'verification') {
    require_once 'vendor/autoload.php';
    
    $account_sid = 'your_account_sid';
    $auth_token = 'your_auth_token';
    $twilio_number = 'your_twilio_number';
    
    $client = new Twilio\Rest\Client($account_sid, $auth_token);
    
    try {
        $message = $client->messages->create(
            $mobile,
            [
                'from' => $twilio_number,
                'body' => "Your TravelPlanner OTP is: $otp. Valid for 10 minutes."
            ]
        );
        return true;
    } catch (Exception $e) {
        error_log("SMS Error: " . $e->getMessage());
        return false;
    }
}
```

## Step 4: Frontend Integration

### 4.1 Registration Form
Update your registration form to include OTP verification:

```html
<!-- Add this after successful registration -->
<div id="otp-verification" style="display: none;">
    <h3>Verify Your Account</h3>
    <div>
        <label>Email OTP:</label>
        <input type="text" id="email-otp" maxlength="6">
    </div>
    <div>
        <label>Mobile OTP:</label>
        <input type="text" id="mobile-otp" maxlength="6">
    </div>
    <button onclick="verifyOTP()">Verify OTP</button>
</div>
```

### 4.2 Booking Form
Add OTP verification to your booking process:

```html
<!-- Add this before booking confirmation -->
<div id="booking-otp" style="display: none;">
    <h3>Confirm Your Booking</h3>
    <div>
        <label>Email OTP:</label>
        <input type="text" id="booking-email-otp" maxlength="6">
    </div>
    <div>
        <label>Mobile OTP:</label>
        <input type="text" id="booking-mobile-otp" maxlength="6">
    </div>
    <button onclick="verifyBookingOTP()">Confirm Booking</button>
</div>
```

### 4.3 Payment Form
Add OTP verification for payments:

```html
<!-- Add this before payment processing -->
<div id="payment-otp" style="display: none;">
    <h3>Verify Payment</h3>
    <div>
        <label>Mobile OTP:</label>
        <input type="text" id="payment-mobile-otp" maxlength="6">
    </div>
    <button onclick="verifyPaymentOTP()">Complete Payment</button>
</div>
```

## Step 5: JavaScript Functions

### 5.1 Registration OTP Verification
```javascript
function verifyOTP() {
    const emailOTP = document.getElementById('email-otp').value;
    const mobileOTP = document.getElementById('mobile-otp').value;
    const userId = localStorage.getItem('temp_user_id');
    
    fetch('php/verify_registration_otp.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `user_id=${userId}&email_otp=${emailOTP}&mobile_otp=${mobileOTP}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            if (data.fully_verified) {
                alert('Registration completed! You can now login.');
                window.location.href = 'login.html';
            } else {
                alert('OTP verified! Please verify the remaining OTP.');
            }
        } else {
            alert(data.msg);
        }
    });
}
```

### 5.2 Booking OTP Verification
```javascript
function verifyBookingOTP() {
    const emailOTP = document.getElementById('booking-email-otp').value;
    const mobileOTP = document.getElementById('booking-mobile-otp').value;
    const bookingId = localStorage.getItem('temp_booking_id');
    
    fetch('php/verify_booking_otp.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `booking_id=${bookingId}&email_otp=${emailOTP}&mobile_otp=${mobileOTP}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            if (data.fully_verified) {
                alert('Booking confirmed successfully!');
                // Proceed to payment
            } else {
                alert('OTP verified! Please verify the remaining OTP.');
            }
        } else {
            alert(data.msg);
        }
    });
}
```

### 5.3 Payment OTP Verification
```javascript
function verifyPaymentOTP() {
    const mobileOTP = document.getElementById('payment-mobile-otp').value;
    const bookingId = localStorage.getItem('temp_booking_id');
    const amount = localStorage.getItem('temp_amount');
    
    fetch('php/verify_payment_otp.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `booking_id=${bookingId}&mobile_otp=${mobileOTP}&amount=${amount}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert('Payment successful! Your booking is confirmed.');
            // Redirect to booking confirmation page
        } else {
            alert(data.msg);
        }
    });
}
```

## Step 6: Admin Panel Access

### 6.1 View OTP Logs
1. Login as admin
2. Go to: `http://localhost/travelplanner/php/admin_otp_logs.php`
3. View all OTP logs including:
   - SMS OTP logs
   - Booking OTP verifications
   - Payment OTP verifications
   - Contact form OTP verifications

### 6.2 Add Admin Panel Link
Add this link to your admin dashboard:

```html
<a href="php/admin_otp_logs.php" class="admin-link">View OTP Logs</a>
```

## Step 7: Testing

### 7.1 Test Registration Flow
1. Register a new user
2. Check email for OTP
3. Check admin panel for SMS OTP (since it's logged)
4. Verify OTPs
5. Complete registration

### 7.2 Test Booking Flow
1. Create a booking
2. Generate booking OTP
3. Verify booking OTP
4. Generate payment OTP
5. Verify payment OTP
6. Complete booking

### 7.3 Test Contact Form
1. Fill contact form
2. Send OTP
3. Verify OTP
4. Submit form

## Step 8: Security Considerations

### 8.1 Rate Limiting
Consider implementing rate limiting to prevent OTP abuse:

```php
// Add to OTP generation methods
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM sms_log WHERE mobile = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)");
$stmt->bind_param("s", $mobile);
$stmt->execute();
$result = $stmt->get_result();
$count = $result->fetch_assoc()['count'];

if ($count >= 5) {
    echo json_encode(['status' => 'error', 'msg' => 'Too many OTP requests. Please try again later.']);
    exit;
}
```

### 8.2 OTP Expiration
OTPs expire after 10 minutes by default. You can adjust this in the `otp_manager.php` file.

### 8.3 Database Security
- Use prepared statements (already implemented)
- Regularly backup OTP logs
- Consider purging old OTP records

## Step 9: Production Deployment

### 9.1 Email Service
For production, consider using:
- Gmail SMTP
- SendGrid
- Mailgun
- Amazon SES

### 9.2 SMS Service
For production, consider using:
- Twilio
- MSG91
- TextLocal
- Nexmo

### 9.3 SSL Certificate
Ensure your website uses HTTPS for secure OTP transmission.

## Troubleshooting

### Common Issues

1. **Emails not sending**:
   - Check PHP mail configuration
   - Verify SMTP settings
   - Check server logs

2. **SMS not working**:
   - Check admin panel for SMS logs
   - Verify SMS service credentials
   - Check API limits

3. **OTP verification failing**:
   - Check database connection
   - Verify OTP expiration time
   - Check user session

4. **Database errors**:
   - Run database update script
   - Check table structure
   - Verify user permissions

## Support

If you encounter any issues:
1. Check the admin OTP logs
2. Verify database structure
3. Test email/SMS configuration
4. Review error logs

The OTP system is now fully integrated into your TravelPlanner application! 