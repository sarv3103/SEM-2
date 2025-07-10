# Razorpay Integration Setup Guide

## Prerequisites
1. Razorpay account (sign up at https://razorpay.com)
2. PHP Razorpay SDK installed via Composer
3. Valid Razorpay API keys

## Step 1: Install Razorpay PHP SDK

Run this command in your project directory:
```bash
composer require razorpay/razorpay
```

## Step 2: Get Your Razorpay API Keys

1. Log in to your Razorpay Dashboard
2. Go to Settings → API Keys
3. Generate a new key pair
4. Copy your Key ID and Key Secret

## Step 3: Update Configuration

Edit `php/razorpay_config.php` and replace the placeholder values:

```php
$keyId = 'rzp_test_YOUR_ACTUAL_KEY_ID'; // Your actual Key ID
$keySecret = 'YOUR_ACTUAL_KEY_SECRET'; // Your actual Key Secret
```

## Step 4: Create Database Table

Run the SQL script to create the payment orders table:

```sql
-- Execute this in your MySQL database
USE travelplanner;

CREATE TABLE IF NOT EXISTS payment_orders (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    booking_id INT(11) NOT NULL,
    razorpay_order_id VARCHAR(100) NOT NULL,
    razorpay_payment_id VARCHAR(100) NULL,
    amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    payment_date TIMESTAMP NULL,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    INDEX idx_razorpay_order_id (razorpay_order_id),
    INDEX idx_booking_id (booking_id),
    INDEX idx_status (status)
);
```

## Step 5: Test the Integration

1. Make sure your web server is running
2. Navigate to the booking page
3. Complete the booking process
4. Test with Razorpay test cards:
   - Card Number: 4111 1111 1111 1111
   - Expiry: Any future date
   - CVV: Any 3 digits
   - Name: Any name

## Step 6: Production Setup

When going live:

1. Switch to live API keys in `razorpay_config.php`
2. Update the key ID in `php/process_payment.php`
3. Test thoroughly with small amounts
4. Ensure SSL is enabled on your domain

## Troubleshooting

### Common Issues:

1. **"Invalid API Key" Error**
   - Check your Key ID and Secret are correct
   - Ensure you're using test keys for testing

2. **"Order creation failed"**
   - Verify your Razorpay account is active
   - Check if you have sufficient balance

3. **"Payment verification failed"**
   - Ensure the signature verification is working
   - Check server time synchronization

4. **"Razorpay not defined"**
   - Make sure the Razorpay script is loaded
   - Check for JavaScript errors in browser console

### Debug Mode:

Add this to your PHP files for debugging:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

## Security Notes

1. Never expose your Key Secret in client-side code
2. Always verify payment signatures on the server
3. Use HTTPS in production
4. Keep your API keys secure

## Support

For Razorpay-specific issues:
- Razorpay Documentation: https://razorpay.com/docs/
- Razorpay Support: support@razorpay.com

For integration issues:
- Check the error logs in your PHP files
- Verify database connections
- Test with the provided test cards 