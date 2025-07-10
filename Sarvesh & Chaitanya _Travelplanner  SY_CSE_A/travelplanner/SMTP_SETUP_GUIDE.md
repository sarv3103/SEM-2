# Gmail SMTP Setup Guide for TravelPlanner Admin Dashboard

This guide will help you set up Gmail SMTP authentication for the admin dashboard's email reply functionality.

## Prerequisites

1. A Gmail account (sarveshtravelplanner@gmail.com)
2. 2-Step Verification enabled on your Google Account
3. Access to your Google Account settings

## Step 1: Enable 2-Step Verification

1. Go to your Google Account settings: https://myaccount.google.com/
2. Click on **Security** in the left sidebar
3. Find **2-Step Verification** and click on it
4. Follow the prompts to enable 2-Step Verification if not already enabled

## Step 2: Generate an App Password

1. In the **Security** section, scroll down to find **App passwords**
2. Click on **App passwords**
3. You may need to sign in again for security
4. Under **Select app**, choose **Mail**
5. Under **Select device**, choose **Other (Custom name)**
6. Enter **TravelPlanner** as the name
7. Click **Generate**
8. **Copy the 16-character password** (it will look like: `abcd efgh ijkl mnop`)
9. **Important**: Save this password securely - you won't be able to see it again!

## Step 3: Update Configuration Files

### Option A: Using the Centralized Config (Recommended)

1. Open `php/smtp_config.php`
2. Find this line:
   ```php
   define('SMTP_PASSWORD', 'YOUR_APP_PASSWORD');
   ```
3. Replace `'YOUR_APP_PASSWORD'` with your actual App Password (including quotes):
   ```php
   define('SMTP_PASSWORD', 'abcd efgh ijkl mnop');
   ```

### Option B: Using the Test File

1. Open `test_smtp_config.php`
2. Update these variables:
   ```php
   $mailPassword = 'YOUR_APP_PASSWORD'; // Replace with your App Password
   $testEmail = 'your-test-email@gmail.com'; // Replace with your test email
   ```

## Step 4: Test the Configuration

1. Open your browser and go to: `http://localhost/travelplanner/test_smtp_config.php`
2. The page will show detailed SMTP connection information
3. If successful, you'll see "✅ Test Email Sent Successfully!"
4. Check your test email for the confirmation message

## Step 5: Use the Admin Dashboard

1. Once the test is successful, you can use the admin dashboard
2. Go to the **Contact Messages** tab
3. Click **Reply** on any message
4. Compose your reply and click **Send Reply**
5. The email will be sent using your Gmail SMTP configuration

## Troubleshooting

### Common Issues

**"Authentication failed" error:**
- Make sure you're using the App Password, not your regular Gmail password
- Ensure 2-Step Verification is enabled
- Verify the App Password was generated for "Mail" application

**"Connection timeout" error:**
- Check your internet connection
- Ensure port 587 is not blocked by your firewall
- Try using port 465 with SSL instead of TLS

**"SMTP credentials not configured" error:**
- Make sure you updated the password in `php/smtp_config.php`
- Check that the password is enclosed in quotes
- Verify there are no extra spaces or characters

### Debug Mode

To enable detailed debugging:

1. Open `php/admin_reply_message.php`
2. Find this line:
   ```php
   $mail->SMTPDebug = 0;
   ```
3. Change it to:
   ```php
   $mail->SMTPDebug = 2;
   ```
4. Check your server's error log for detailed SMTP communication

### Alternative SMTP Providers

If Gmail doesn't work, you can use other providers:

**Outlook/Hotmail:**
```php
define('SMTP_HOST', 'smtp-mail.outlook.com');
define('SMTP_PORT', 587);
```

**Yahoo:**
```php
define('SMTP_HOST', 'smtp.mail.yahoo.com');
define('SMTP_PORT', 587);
```

## Security Notes

1. **Never commit your App Password to version control**
2. Consider using environment variables for production
3. Delete `test_smtp_config.php` after successful testing
4. Regularly rotate your App Passwords
5. Monitor your Gmail account for any suspicious activity

## File Structure

```
travelplanner/
├── php/
│   ├── smtp_config.php          # Centralized SMTP configuration
│   └── admin_reply_message.php  # Email sending functionality
├── test_smtp_config.php         # SMTP testing tool (delete after use)
└── SMTP_SETUP_GUIDE.md          # This guide
```

## Support

If you continue to have issues:

1. Check the error messages in your browser's developer console
2. Review your server's error logs
3. Verify your Gmail account settings
4. Test with a different email provider if needed

## Next Steps

After successful setup:

1. Delete `test_smtp_config.php` for security
2. Test the admin dashboard reply functionality
3. Consider setting up email templates for different types of replies
4. Monitor email delivery and bounce rates 