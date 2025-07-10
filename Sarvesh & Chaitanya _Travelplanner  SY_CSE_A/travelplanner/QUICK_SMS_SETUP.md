# Quick SMS Setup with TextLocal (5 minutes)

## 🎯 **Why TextLocal?**
- ✅ **API key is clearly visible** in dashboard
- ✅ **No DLT compliance required**
- ✅ **100 free SMS credits**
- ✅ **Simple setup process**

## Step 1: Sign Up for TextLocal (2 minutes)
1. Go to [TextLocal](https://www.textlocal.in/)
2. Click "Sign Up"
3. Fill in your details:
   - Email address
   - Mobile number
   - Password
4. Verify your email (check inbox)

## Step 2: Get API Key (1 minute)
1. Login to TextLocal dashboard
2. Go to "API" section in left menu
3. Copy your API key (clearly visible)

## Step 3: Update Configuration (1 minute)
1. Open `php/sms_config.php`
2. Replace `your-textlocal-api-key` with your actual API key
3. Save the file

## Step 4: Test SMS (1 minute)
1. Run: `http://localhost/travelplanner/check_sms_status.php`
2. Enter your mobile number
3. Click "Send Test SMS"
4. Check your phone for the SMS

## Current Status
- ✅ Email OTP: Working perfectly
- ✅ Database: Ready
- ✅ System: Functional
- ⚠️ SMS OTP: Needs API key

## Alternative: Use Email-Only System
Your system works perfectly with email OTPs right now!
- Register users with email OTP
- Book packages with email confirmation
- Generate PDF tickets
- Monitor all activities

## Next Steps
1. Set up TextLocal (5 minutes)
2. Test SMS delivery
3. Use complete OTP system
4. Monitor in admin panel 