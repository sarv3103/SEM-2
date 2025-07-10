# SMS Setup Troubleshooting Guide

## 🚨 **What specific issue are you facing?**

### **Issue 1: Can't sign up for MSG91/TextLocal**
**Solutions:**
- Try different browser (Chrome, Firefox)
- Clear browser cache and cookies
- Use different email address
- Try mobile browser

### **Issue 2: Can't find API key**
**Solutions:**
- Make sure you're logged in
- Look for "API" or "Developer" section
- Check if account is verified
- Contact support if needed

### **Issue 3: SMS not sending**
**Solutions:**
- Check API key is correct
- Verify you have SMS credits
- Check mobile number format (10 digits)
- Test with your own number first

### **Issue 4: Don't receive SMS**
**Solutions:**
- Check mobile network
- Make sure number is not in DND list
- Check spam/junk folder
- Wait 1-2 minutes (sometimes delayed)

## 🎯 **Quick Fix Options**

### **Option A: Use TextLocal (Easier)**
1. Go to https://www.textlocal.in/
2. Sign up (2 minutes)
3. Get API key from dashboard
4. Update `php/sms_config.php`
5. Test SMS

### **Option B: Use Email-Only (Working Now)**
- ✅ Email OTP is working perfectly
- ✅ Can use system immediately
- ✅ Add SMS later when needed

### **Option C: Manual SMS Setup Help**
Tell me exactly what step you're stuck on:
- Sign up process?
- Finding API key?
- Updating configuration?
- Testing SMS?

## 🔧 **Step-by-Step TextLocal Setup**

### **Step 1: Sign Up**
1. Go to https://www.textlocal.in/
2. Click "Sign Up"
3. Fill in your details
4. Verify email

### **Step 2: Get API Key**
1. Login to dashboard
2. Go to "API" section
3. Copy your API key

### **Step 3: Update Config**
1. Open `php/sms_config.php`
2. Replace `your-textlocal-api-key` with your actual API key
3. Save the file

### **Step 4: Test**
1. Run: `http://localhost/travelplanner/check_sms_status.php`
2. Enter your mobile number
3. Click "Send Test SMS"

## 📞 **Need More Help?**

**Tell me exactly:**
1. Which step are you stuck on?
2. What error message do you see?
3. Which service are you trying (MSG91 or TextLocal)?
4. What happens when you try to sign up?

## 🎯 **Current Status**
- ✅ Email OTP: Working perfectly
- ✅ Database: Ready
- ✅ System: Functional
- ⚠️ SMS OTP: Needs configuration

**Your system works with email OTPs right now!** You can use it while we fix SMS. 