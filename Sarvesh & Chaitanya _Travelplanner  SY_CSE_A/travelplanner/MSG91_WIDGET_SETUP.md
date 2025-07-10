# MSG91 OTP Widget Setup Guide

## ✅ Recommended Widget Configuration

### Widget Settings:
- **Widget Name**: `TRVLPL` (6 characters max)
- **Widget Type**: `Verification`
- **Contact Point**: `Mobile Primary Channel`
- **Channel**: `SMS`
- **OTP Length**: `6` (recommended for balance of security and convenience)
- **Resend Configuration**:
  - Resend Channels: `SMS`
  - Resend Count: `2`
  - Resend After: `30` seconds
- **OTP Expiration**: `15` minutes (minimum required)
- **Widget Integration**: `Web Application`
- **Invisible OTP**: `Enabled` (for better UX)
- **Captcha Validation**: `Enabled` (for security)
- **IFrame**: `Disabled` (for direct integration)
- **Default Country**: `India (+91)`

## 🔧 Integration Steps

### Step 1: Update Widget Configuration
1. Go to MSG91 Dashboard → OTP Widgets
2. Update your widget with the settings above
3. Save and get your Widget ID

### Step 2: Get Integration Code
After saving, MSG91 will provide you with:
- Widget ID
- Integration JavaScript code
- API endpoints

### Step 3: Integrate with Your System
The widget will replace your current OTP system with a more user-friendly interface.

## 📱 Current Mobile Number
Your test mobile: `9130123270` ✅

## 🚀 Next Steps
1. Update widget configuration as shown above
2. Get the integration code from MSG91
3. We'll integrate it with your travel planner system

## 🔄 Migration from Current System
Your current SMS system will be replaced by the MSG91 widget, which provides:
- Better user experience
- Built-in resend functionality
- Automatic expiration handling
- Invisible OTP option
- Better security with captcha

## 📋 Integration Files to Update
- `register.html` - Registration form
- `login.html` - Login form
- `booking.html` - Booking forms
- `php/` - Backend verification logic 