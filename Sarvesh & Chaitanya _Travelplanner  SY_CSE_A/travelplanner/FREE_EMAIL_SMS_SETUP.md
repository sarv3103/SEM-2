# Free Email & SMS Setup Guide

## 🚀 **Complete Setup for Real Email & SMS OTP Delivery**

### **Step 1: Install Dependencies**

Run this command in your project directory:
```bash
composer install
```

This will install:
- ✅ PHPMailer (for Gmail SMTP)
- ✅ mPDF (already installed)

### **Step 2: Gmail Email Setup (FREE)**

#### **2.1 Create Gmail App Password**
1. **Go to**: https://myaccount.google.com/
2. **Click "Security"** in the left sidebar
3. **Enable 2-Step Verification** if not already enabled
4. **Go to "App passwords"**
5. **Select "Mail"** and "Other (Custom name)"
6. **Enter name**: "TravelPlanner"
7. **Click "Generate"**
8. **Copy the 16-character password**

#### **2.2 Update Email Configuration**
Edit `php/email_config.php`:
```php
$this->mailer->Username = 'your-email@gmail.com'; // Your Gmail address
$this->mailer->Password = 'your-16-char-app-password'; // The app password you generated
```

### **Step 3: SMS Setup - TextLocal (FREE)**

#### **3.1 Sign Up for TextLocal**
1. **Go to**: https://www.textlocal.in/
2. **Click "Sign Up"**
3. **Fill registration form**
4. **Verify your mobile number**
5. **Login to your account**

#### **3.2 Get API Key**
1. **Login to TextLocal**
2. **Go to "API" section**
3. **Copy your API key**

#### **3.3 Update SMS Configuration**
Edit `php/sms_config.php`:
```php
$this->apiKey = 'your-textlocal-api-key'; // Your TextLocal API key
$this->sender = 'TXTLCL'; // Your sender ID (6 characters max)
```

### **Step 4: Alternative SMS - MSG91 (FREE)**

#### **4.1 Sign Up for MSG91**
1. **Go to**: https://msg91.com/
2. **Click "Sign Up"**
3. **Complete registration**
4. **Verify your account**

#### **4.2 Get API Credentials**
1. **Login to MSG91**
2. **Go to "API" section**
3. **Copy your Auth Key**
4. **Create a Flow and get Flow ID**

#### **4.3 Update MSG91 Configuration**
Edit `php/sms_config.php`:
```php
'flow_id' => 'your-flow-id', // Your MSG91 flow ID
'Authkey: your-msg91-authkey' // Your MSG91 auth key
```

### **Step 5: Test the Setup**

#### **5.1 Test Email**
Create a test file `test_email.php`:
```php
<?php
require_once 'php/email_config.php';

$emailService = new EmailService();
$result = $emailService->sendOTP('your-email@example.com', '123456', 'test');

if ($result) {
    echo "✅ Email sent successfully!";
} else {
    echo "❌ Email failed to send";
}
?>
```

#### **5.2 Test SMS**
Create a test file `test_sms.php`:
```php
<?php
require_once 'php/sms_config.php';

$smsService = new SMSService();
$result = $smsService->sendOTP('your-mobile-number', '123456', 'test');

if ($result) {
    echo "✅ SMS sent successfully!";
} else {
    echo "❌ SMS failed to send";
}
?>
```

### **Step 6: Update Admin Panel**

The admin panel will now show:
- ✅ **Real SMS delivery status**
- ✅ **Email delivery status**
- ✅ **API responses**
- ✅ **Success/failure logs**

### **Step 7: Production Configuration**

#### **7.1 Environment Variables (Recommended)**
Create `.env` file:
```env
GMAIL_USERNAME=your-email@gmail.com
GMAIL_PASSWORD=your-app-password
TEXTLOCAL_API_KEY=your-textlocal-api-key
MSG91_AUTH_KEY=your-msg91-auth-key
```

#### **7.2 Update Configuration Files**
Modify the config files to read from environment variables:
```php
$this->mailer->Username = $_ENV['GMAIL_USERNAME'];
$this->mailer->Password = $_ENV['GMAIL_PASSWORD'];
$this->apiKey = $_ENV['TEXTLOCAL_API_KEY'];
```

## 🎯 **Free Services Summary**

### **Email Services (FREE)**
- ✅ **Gmail SMTP** - 500 emails/day free
- ✅ **SendGrid** - 100 emails/day free
- ✅ **Mailgun** - 5,000 emails/month free

### **SMS Services (FREE)**
- ✅ **TextLocal** - 100 SMS free credits
- ✅ **MSG91** - Free trial credits
- ✅ **Twilio** - Free trial credits

## 🔧 **Troubleshooting**

### **Email Issues**
1. **Check Gmail settings**:
   - Enable "Less secure app access" (if not using app password)
   - Verify 2-Step Verification is enabled
   - Check app password is correct

2. **Check server settings**:
   - Ensure port 587 is open
   - Check SSL/TLS settings

### **SMS Issues**
1. **TextLocal**:
   - Verify API key is correct
   - Check sender ID format (6 characters max)
   - Ensure mobile number has country code

2. **MSG91**:
   - Verify Auth Key
   - Check Flow ID is correct
   - Ensure template is approved

## 📊 **Monitoring**

### **Admin Panel Features**
- ✅ **Real-time SMS delivery status**
- ✅ **Email delivery logs**
- ✅ **API response tracking**
- ✅ **Success/failure rates**
- ✅ **OTP verification statistics**

### **Log Files**
Check these files for errors:
- `php/error_log`
- `apache/logs/error.log`
- `xampp/apache/logs/error.log`

## 🚀 **Ready to Use**

After completing this setup:
1. ✅ **Real emails** will be sent to users
2. ✅ **Real SMS** will be delivered to mobile numbers
3. ✅ **Admin panel** will show delivery status
4. ✅ **Complete OTP verification** system working

Your TravelPlanner application now has **professional-grade email and SMS OTP delivery** using completely free services! 