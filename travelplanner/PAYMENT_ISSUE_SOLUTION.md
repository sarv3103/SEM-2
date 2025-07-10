# Payment Issue Solution Guide

## Problem Description
When users pay ₹1 on Razorpay and the payment is successful, but your website shows "Payment Failed" and no ticket is generated.

## Root Causes
1. **Network Issues**: Frontend JavaScript fails to reach the verification endpoint
2. **Server Errors**: PHP verification script encounters errors
3. **Database Issues**: Payment verification succeeds but database update fails
4. **Webhook Not Configured**: Razorpay webhooks not set up for automatic processing

## Solutions Implemented

### 1. **Webhook Integration (Recommended)**
- **File**: `php/razorpay_webhook.php`
- **Purpose**: Automatically processes payments when Razorpay confirms them
- **How it works**: 
  - Razorpay sends webhook notifications to your server
  - Server automatically updates payment status and generates tickets
  - No dependency on frontend JavaScript

**Setup Steps**:
1. Run `add_webhook_columns.php` to add tracking columns
2. Configure webhook URL in Razorpay dashboard: `https://yoursite.com/php/razorpay_webhook.php`
3. Set webhook secret in `php/razorpay_config.php`
4. Enable webhook events: `payment.captured`, `payment.failed`, `order.paid`

### 2. **Admin Manual Verification System**
- **File**: `php/admin_verify_payment.php`
- **Purpose**: Allows admin to manually verify payments and update booking status
- **Features**:
  - Check payment status using Payment ID
  - Manually verify payments and update database
  - View pending/failed payments
  - Generate tickets for verified payments

**Usage**:
1. Admin logs into admin panel
2. Navigate to Payment Verification
3. Enter Payment ID and Order ID
4. Click "Verify Payment" to update status

### 3. **Enhanced Error Handling**
- **File**: `php/verify_payment.php` (updated)
- **Improvements**:
  - Better error logging
  - Graceful failure handling
  - Detailed error messages
  - Automatic retry mechanisms

### 4. **Database Tracking**
- **Columns Added**:
  - `webhook_processed` in `payment_orders` table
  - `webhook_processed` in `bookings` table
- **Purpose**: Track which payments were processed via webhook vs manual verification

## Implementation Steps

### Step 1: Update Database
```bash
# Run the database update script
php add_webhook_columns.php
```

### Step 2: Configure Webhooks
1. Log into Razorpay Dashboard
2. Go to Settings > Webhooks
3. Add webhook URL: `https://yoursite.com/php/razorpay_webhook.php`
4. Select events: `payment.captured`, `payment.failed`, `order.paid`
5. Copy webhook secret and update in `php/razorpay_config.php`

### Step 3: Test Webhook
1. Make a test payment
2. Check server logs for webhook processing
3. Verify database updates
4. Confirm ticket generation and email sending

### Step 4: Admin Access
1. Access `php/admin_verify_payment.php`
2. Use admin credentials to log in
3. Test manual payment verification

## Monitoring and Maintenance

### 1. **Check Webhook Status**
- Monitor Razorpay dashboard for webhook delivery status
- Check server error logs for webhook processing errors
- Verify database updates are happening

### 2. **Regular Verification**
- Check pending payments daily
- Verify failed payments manually
- Monitor email delivery success rates

### 3. **Backup Verification**
- If webhooks fail, use admin manual verification
- Keep track of manually verified payments
- Document any recurring issues

## Troubleshooting

### Webhook Not Working
1. Check webhook URL is accessible
2. Verify webhook secret is correct
3. Check server error logs
4. Test webhook endpoint manually

### Manual Verification Fails
1. Verify Payment ID exists in Razorpay
2. Check payment status is 'captured'
3. Ensure Order ID matches in database
4. Check database connection

### Email Not Sending
1. Verify SMTP settings
2. Check email server logs
3. Test email functionality separately
4. Ensure PDF generation works

## Best Practices

1. **Always have webhooks configured** for automatic processing
2. **Keep admin manual verification as backup**
3. **Monitor payment status regularly**
4. **Log all payment activities**
5. **Test payment flow regularly**
6. **Keep backup of payment data**

## Emergency Procedures

### If Webhooks Stop Working
1. Check Razorpay service status
2. Verify server connectivity
3. Use admin manual verification
4. Contact Razorpay support if needed

### If Database Issues Occur
1. Check database connection
2. Verify table structure
3. Run database repair scripts
4. Restore from backup if necessary

### If Email System Fails
1. Check SMTP settings
2. Verify email server status
3. Use alternative email service
4. Manually send tickets if needed

## Contact Information
- **Razorpay Support**: support@razorpay.com
- **Your Support**: sarveshtravelplanner@gmail.com
- **Emergency**: [Your emergency contact]

## Files Modified/Created
1. `php/razorpay_webhook.php` - Webhook handler
2. `php/admin_verify_payment.php` - Admin verification system
3. `php/razorpay_config.php` - Enhanced Razorpay service
4. `php/verify_payment.php` - Improved error handling
5. `add_webhook_columns.php` - Database update script

This solution ensures that even if the frontend payment verification fails, payments will be automatically processed via webhooks, and admins can manually verify payments as a backup option. 