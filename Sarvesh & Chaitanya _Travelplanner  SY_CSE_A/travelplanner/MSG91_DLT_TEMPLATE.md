# MSG91 DLT Template Setup Guide

## ✅ Template Configuration

### Template Details:
- **Template Name**: `TravelPlanner OTP` ✅
- **Sender ID**: `TRAVEL` ✅ (6 characters, perfect!)
- **DLT Template ID**: [You'll get this after approval]

### SMS Content Template:
```
Your TravelPlanner verification code is ##OTP##. Valid for 15 minutes. Do not share this OTP with anyone. - TRAVEL
```

### Alternative Template (if first one is too long):
```
Your TravelPlanner OTP is ##OTP##. Valid for 15 minutes. - TRAVEL
```

## 🔧 Template Variables

### Required Variable:
- **##OTP##** - This will be replaced with the 6-digit OTP code

### Template Format Rules:
- Variables must be in format: `##variable_name##`
- For OTP, use: `##OTP##`
- Template must be approved by DLT platform
- Keep content under 160 characters

## 📋 Template Submission Steps

### Step 1: Template Content
Copy this exact content:
```
Your TravelPlanner verification code is ##OTP##. Valid for 15 minutes. Do not share this OTP with anyone. - TRAVEL
```

### Step 2: Template Details
- **Template Name**: TravelPlanner OTP
- **Sender ID**: TRAVEL
- **Category**: Transactional
- **Language**: English
- **Variables**: ##OTP##

### Step 3: Submit for Approval
1. Submit template to MSG91
2. Wait for DLT approval (24-48 hours)
3. Get your DLT Template ID
4. Update your SMS configuration

## 🔄 Update SMS Configuration

Once you get the DLT Template ID, update your `php/sms_config.php`:

```php
// Update this line in sendViaMSG91 function
'flow_id' => 'YOUR_DLT_TEMPLATE_ID_HERE', // Replace with actual DLT Template ID
```

## 📱 Template Testing

After approval, test with:
- **Mobile Number**: 9130123270
- **Expected SMS**: "Your TravelPlanner verification code is 123456. Valid for 15 minutes. Do not share this OTP with anyone. - TRAVEL"

## 🎯 Benefits of DLT Template

- ✅ **Compliant**: Meets Indian DLT requirements
- ✅ **Reliable**: Higher delivery rates
- ✅ **Professional**: Branded sender ID
- ✅ **Secure**: Approved content template

## 📞 Next Steps

1. **Submit template** with the content above
2. **Wait for approval** (24-48 hours)
3. **Get DLT Template ID** from MSG91
4. **Update SMS config** with the new Template ID
5. **Test OTP delivery** with your mobile number

## 🔗 Related Files

- `php/sms_config.php` - Will need DLT Template ID update
- `test_real_sms.php` - Test OTP delivery
- `MSG91_WIDGET_INTEGRATION.md` - Widget integration guide 