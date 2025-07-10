# TextLocal SMS Setup - Quick Guide

## Step 1: Sign Up (2 minutes)
1. Go to [TextLocal](https://www.textlocal.in/)
2. Click "Sign Up" or "Register"
3. Fill in your details:
   - Email address
   - Mobile number
   - Password
4. Verify your email (check inbox)

## Step 2: Get API Key (1 minute)
1. Login to TextLocal dashboard
2. Look for "API" or "API Keys" in the left menu
3. Copy your API key (looks like: `abc123def456ghi789`)
4. Note: You get 100 free SMS credits automatically

## Step 3: Update Configuration (1 minute)
1. Open `php/sms_config.php`
2. Find this line:
   ```php
   $this->apiKey = 'your-textlocal-api-key';
   ```
3. Replace `your-textlocal-api-key` with your actual API key:
   ```php
   $this->apiKey = 'abc123def456ghi789'; // Your actual API key
   ```

## Step 4: Test SMS (1 minute)
1. Run: `http://localhost/travelplanner/check_sms_status.php`
2. Enter your mobile number in the test form
3. Click "Send Test SMS"
4. Check your phone for the SMS

## Troubleshooting

### If you don't see API section:
- Make sure you're logged in
- Look for "API" or "Developer" section
- Contact TextLocal support if needed

### If SMS fails:
- Check your SMS credits balance
- Verify API key is correct
- Make sure mobile number is 10 digits (e.g., 9876543210)

### If you don't receive SMS:
- Check your mobile network
- Make sure number is not in DND list
- Check spam/junk folder in SMS app

## Alternative: MSG91
If TextLocal doesn't work, try MSG91:
1. Go to [MSG91](https://msg91.com/)
2. Sign up and get API key
3. Update the MSG91 section in `php/sms_config.php`

## Cost
- **TextLocal**: 100 free SMS credits
- **MSG91**: Usually 100 free SMS credits
- **Both**: Very cheap after free credits (₹0.10-0.20 per SMS)

## Next Steps
Once SMS is working:
1. Test registration: `http://localhost/travelplanner/register.html`
2. Test booking: `http://localhost/travelplanner/booking.html`
3. Monitor logs: `http://localhost/travelplanner/php/admin_otp_logs.php` 