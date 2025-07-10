# MSG91 Setup Guide (500+ Free SMS Credits)

## 🎯 **Why MSG91?**
- **500+ free SMS credits** (vs 100 from TextLocal)
- **Better delivery rates**
- **Indian company** - better support
- **More generous free tier**

## Step 1: Sign Up for MSG91 (2 minutes)
1. Go to [MSG91](https://msg91.com/)
2. Click "Sign Up" or "Get Started"
3. Fill in your details:
   - Email address
   - Mobile number
   - Company name (use "TravelPlanner")
   - Password
4. Verify your email (check inbox)

## Step 2: Get Your Auth Key (1 minute)
1. Login to MSG91 dashboard
2. Go to "API" section in the left menu
3. Copy your **Auth Key** (looks like: `abc123def456ghi789`)
4. Note: You get 500+ free SMS credits automatically

## Step 3: Create SMS Flow (2 minutes)
1. In MSG91 dashboard, go to "Flow" section
2. Click "Create New Flow"
3. Choose "Transactional SMS"
4. Set flow name: "TravelPlanner OTP"
5. Create message template:
   ```
   Your TravelPlanner OTP is: {{#var#}}. Valid for 10 minutes.
   ```
6. Save and copy the **Flow ID** (looks like: `1234567890abcdef`)

## Step 4: Update Configuration (1 minute)
1. Open `php/sms_config.php`
2. Update these lines:
   ```php
   $this->apiKey = 'your-actual-auth-key'; // Replace with your Auth Key
   $this->sender = 'TRAVEL'; // Your sender ID (6 characters max)
   $this->service = 'msg91'; // Keep this as 'msg91'
   ```
3. Also update the flow_id in the sendViaMSG91 function:
   ```php
   'flow_id' => 'your-actual-flow-id', // Replace with your Flow ID
   ```

## Step 5: Test SMS (1 minute)
1. Run: `http://localhost/travelplanner/check_sms_status.php`
2. Enter your mobile number
3. Click "Send Test SMS"
4. Check your phone for the SMS

## Troubleshooting

### If you don't see API section:
- Make sure you're logged in
- Look for "API" or "Developer" section
- Contact MSG91 support if needed

### If SMS fails:
- Check your SMS credits balance
- Verify Auth Key is correct
- Make sure Flow ID is correct
- Check mobile number format (10 digits)

### If you don't receive SMS:
- Check your mobile network
- Make sure number is not in DND list
- Check spam/junk folder in SMS app
- Wait 1-2 minutes (sometimes delayed)

## Cost Comparison

| Service | Free Credits | Cost After Free |
|---------|-------------|-----------------|
| **MSG91** | **500+ SMS** | ₹0.10-0.15 per SMS |
| TextLocal | 100 SMS | ₹0.10-0.20 per SMS |
| Twilio | $15-20 worth | $0.0075 per SMS |

## Alternative Setup (If MSG91 doesn't work)

### Option 1: TextLocal (100 free SMS)
1. Go to [TextLocal](https://www.textlocal.in/)
2. Sign up and get API key
3. Change `$this->service = 'textlocal';` in config

### Option 2: Twilio (International)
1. Go to [Twilio](https://www.twilio.com/)
2. Sign up and get $15-20 free credits
3. Use Twilio API (more complex setup)

## Next Steps
Once SMS is working:
1. Test registration: `http://localhost/travelplanner/register.html`
2. Test booking: `http://localhost/travelplanner/booking.html`
3. Monitor logs: `http://localhost/travelplanner/php/admin_otp_logs.php`

## Pro Tips
- **Sender ID**: Use "TRAVEL" (6 characters max)
- **Mobile format**: Use 10 digits (e.g., 9876543210)
- **Flow template**: Keep it simple and clear
- **Testing**: Always test with your own number first

## Support
- MSG91 Support: Available in dashboard
- Documentation: https://msg91.com/api-documentation
- Community: MSG91 developer forums 