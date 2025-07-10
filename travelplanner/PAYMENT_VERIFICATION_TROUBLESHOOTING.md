# Payment Verification Troubleshooting Guide

## Common Issues and Solutions

### 1. Payment Verification Fails After Entering All Details

**Symptoms:**
- Payment gateway opens and payment is completed
- User sees "Verifying payment..." message
- Verification fails after multiple attempts
- No success message appears

**Possible Causes and Solutions:**

#### A. Database Connection Issues
- **Check:** Run `test_payment_verification.php` to verify database connectivity
- **Solution:** Ensure XAMPP MySQL service is running
- **Fix:** Restart XAMPP if needed

#### B. Missing Database Tables/Columns
- **Check:** Run `setup_payment_tables.php` to ensure all required tables exist
- **Required Tables:**
  - `payment_orders` (for storing Razorpay order details)
  - `bookings` (with payment_status, payment_date, ticket_sent columns)

#### C. Razorpay Configuration Issues
- **Check:** Verify Razorpay keys in `php/razorpay_config.php`
- **Current Configuration:**
  - Key ID: `rzp_live_2JdrplZN9MSywf`
  - Key Secret: `8JHRkWgt4C286TQoNZErbmdK`
- **Solution:** Ensure keys are correct and account is active

#### D. Payment Order Not Found in Database
- **Check:** Look for the order in `payment_orders` table
- **Solution:** Use `test_simple_verification.php` to manually verify payment
- **Fix:** Check if booking was created properly in `bookings` table

### 2. Manual Verification Process

If automatic verification fails, follow these steps:

1. **Get Payment Details:**
   - After payment completion, note down:
     - Payment ID (starts with `pay_`)
     - Order ID (starts with `order_`)

2. **Use Manual Verification Tool:**
   - Open `test_simple_verification.php` in browser
   - Enter Payment ID and Order ID
   - Click "Verify Payment"

3. **Check Results:**
   - If successful: Payment status will be updated in database
   - If failed: Check error message for specific issue

### 3. Database Debugging

#### Check Recent Bookings:
```sql
SELECT * FROM bookings ORDER BY id DESC LIMIT 5;
```

#### Check Payment Orders:
```sql
SELECT * FROM payment_orders ORDER BY id DESC LIMIT 5;
```

#### Check Payment Status:
```sql
SELECT b.booking_id, b.payment_status, po.razorpay_order_id, po.status 
FROM bookings b 
LEFT JOIN payment_orders po ON b.id = po.booking_id 
ORDER BY b.id DESC LIMIT 10;
```

### 4. Common Error Messages and Solutions

#### "Order not found in database"
- **Cause:** Payment order wasn't created during booking
- **Solution:** Check if booking was created successfully
- **Fix:** Recreate booking or manually add payment order

#### "Payment not captured"
- **Cause:** Payment failed or is pending
- **Solution:** Check payment status in Razorpay dashboard
- **Fix:** Wait for payment to be captured or retry payment

#### "Database connection failed"
- **Cause:** MySQL service not running
- **Solution:** Start XAMPP MySQL service
- **Fix:** Restart XAMPP completely

#### "Error verifying payment"
- **Cause:** Razorpay API error or invalid keys
- **Solution:** Check Razorpay configuration
- **Fix:** Verify API keys and account status

### 5. Testing Process

1. **Create Test Booking:**
   - Go to booking.html
   - Fill all required details
   - Complete payment with ₹1 test amount

2. **Monitor Process:**
   - Check browser console for JavaScript errors
   - Check server error logs
   - Monitor database for new records

3. **Verify Results:**
   - Check if booking appears in database
   - Check if payment order was created
   - Verify payment status is updated

### 6. Emergency Recovery

If payment verification completely fails:

1. **Manual Database Update:**
   ```sql
   -- Find the booking
   SELECT * FROM bookings WHERE booking_id = 'YOUR_BOOKING_ID';
   
   -- Update payment status manually
   UPDATE bookings SET payment_status = 'paid', payment_date = NOW() 
   WHERE booking_id = 'YOUR_BOOKING_ID';
   
   -- Update payment order if exists
   UPDATE payment_orders SET status = 'completed', razorpay_payment_id = 'PAYMENT_ID' 
   WHERE razorpay_order_id = 'ORDER_ID';
   ```

2. **Generate Ticket Manually:**
   - Use the booking data to generate ticket
   - Send email manually if needed

### 7. Prevention Measures

1. **Regular Testing:**
   - Test payment flow weekly
   - Monitor error logs
   - Check database integrity

2. **Backup Verification:**
   - Keep manual verification tools ready
   - Document recovery procedures
   - Maintain payment records

3. **Monitoring:**
   - Set up error alerts
   - Monitor payment success rates
   - Track verification failures

### 8. Support Information

If issues persist:
1. Check XAMPP error logs
2. Verify Razorpay account status
3. Test with different payment methods
4. Contact support with specific error messages

**Important Files:**
- `booking.html` - Main booking interface
- `php/verify_payment.php` - Payment verification logic
- `php/book_main_travel.php` - Booking creation
- `test_simple_verification.php` - Manual verification tool
- `test_payment_verification.php` - Debug tool 