# TravelPlanner OTP Configuration Guide

## Step 1: Fix Database (Required)
1. Go to `http://localhost/phpmyadmin`
2. Select `travelplanner` database
3. Click "SQL" tab
4. Run this SQL:
```sql
DROP TABLE IF EXISTS sms_log;
CREATE TABLE sms_log (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    mobile VARCHAR(15) NOT NULL,
    message TEXT NOT NULL,
    otp VARCHAR(6) NOT NULL,
    success TINYINT(1) DEFAULT 0,
    api_response TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

## Step 2: Configure Gmail Email (FREE)

### 2.1 Enable 2-Step Verification
1. Go to [Google Account Settings](https://myaccount.google.com/)
2. Click "Security" → "2-Step Verification"
3. Enable 2-Step Verification

### 2.2 Create App Password
1. In Security settings, click "App passwords"
2. Select "Mail" and "Other (Custom name)"
3. Enter "TravelPlanner" as the name
4. Copy the 16-character password

### 2.3 Update Email Configuration
Edit `php/email_config.php`:
```php
$this->mailer->Username = 'your-actual-gmail@gmail.com'; // Your Gmail
$this->mailer->Password = 'your-16-char-app-password'; // App password from step 2.2
$this->mailer->setFrom('your-actual-gmail@gmail.com', 'TravelPlanner');
```

## Step 3: Configure SMS Service (FREE)

### Option A: TextLocal (Recommended)
1. Sign up at [TextLocal](https://www.textlocal.in/)
2. Get free credits (usually 100 SMS)
3. Go to API section and copy your API key
4. Edit `php/sms_config.php`:
```php
$this->apiKey = 'your-actual-textlocal-api-key';
$this->sender = 'TXTLCL'; // or your custom sender ID
```

### Option B: MSG91
1. Sign up at [MSG91](https://msg91.com/)
2. Get free credits
3. Create a flow and get flow ID
4. Edit `php/sms_config.php` in the MSG91 section:
```php
'flow_id' => 'your-actual-flow-id',
'Authkey: your-actual-msg91-authkey'
```

## Step 4: Test Configuration

### 4.1 Run Database Test
```bash
http://localhost/travelplanner/test_email_sms.php
```

### 4.2 Expected Results
- ✅ PHPMailer is installed successfully
- ✅ EmailService initialized successfully
- ✅ Email service test completed (result: Success)
- ✅ SMSService initialized successfully
- ✅ SMS service test completed (result: Success)
- ✅ OTP Manager initialized successfully

## Step 5: Test Real OTP Delivery

### 5.1 Test Registration
1. Go to `http://localhost/travelplanner/register.html`
2. Fill in registration form
3. Check email and SMS for OTP
4. Verify OTP to complete registration

### 5.2 Test Booking
1. Go to booking page
2. Complete booking form
3. Check email and SMS for booking confirmation OTP

## Troubleshooting

### Email Issues
- **"Invalid credentials"**: Check Gmail app password
- **"SMTP connection failed"**: Check internet connection
- **"Authentication failed"**: Enable 2-Step Verification first

### SMS Issues
- **"Invalid API key"**: Check TextLocal/MSG91 API key
- **"Insufficient balance"**: Add credits to your SMS account
- **"Invalid mobile number"**: Ensure mobile has country code (+91 for India)

### Database Issues
- **"Table doesn't exist"**: Run the SQL from Step 1
- **"Column doesn't exist"**: Run the complete database update

## Admin Panel
- View OTP logs: `http://localhost/travelplanner/php/admin_otp_logs.php`
- Monitor email/SMS delivery status
- Check for failed deliveries

## Security Notes
- Never commit real credentials to git
- Use environment variables in production
- Regularly rotate API keys
- Monitor OTP usage for abuse

## Free Service Limits
- **Gmail**: 500 emails/day (free)
- **TextLocal**: 100 SMS free credits
- **MSG91**: Varies, check their website

## Next Steps
1. Configure real credentials
2. Test with your actual email/mobile
3. Deploy to production
4. Set up monitoring and alerts 