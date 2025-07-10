# MSG91 OTP Widget Integration Guide

## 🎯 Integration Strategy

Your current system uses:
- **Email OTP**: Manual input (keep this)
- **SMS OTP**: Manual input (replace with MSG91 widget)

The MSG91 widget will provide:
- ✅ Automatic SMS sending
- ✅ Built-in OTP input interface
- ✅ Automatic verification
- ✅ Resend functionality
- ✅ Better user experience

## 📋 Integration Steps

### Step 1: Get Widget Integration Code
After saving your widget configuration, MSG91 will provide:
```html
<!-- MSG91 OTP Widget Integration -->
<script src="https://widget.msg91.com/js/widget.js"></script>
<script>
  var msg91Widget = new Msg91Widget({
    widgetId: "YOUR_WIDGET_ID_HERE",
    templateId: "YOUR_TEMPLATE_ID_HERE",
    authKey: "457111ARwKRzZTS26856eb4aP1"
  });
</script>
```

### Step 2: Update Registration Form
Replace the manual SMS OTP section with the MSG91 widget:

```html
<!-- Replace this section in register.html -->
<div class="otp-input-group">
  <label for="mobile-otp">📱 Mobile OTP:</label>
  <input type="text" id="mobile-otp" name="mobile_otp" maxlength="6" placeholder="Enter 6-digit OTP" pattern="[0-9]{6}" title="Please enter 6-digit OTP">
</div>

<!-- With this MSG91 widget -->
<div class="otp-input-group">
  <label>📱 Mobile OTP:</label>
  <div id="msg91-otp-widget"></div>
</div>
```

### Step 3: Update JavaScript
Modify the registration flow to use MSG91 widget:

```javascript
// After successful registration, trigger MSG91 widget
if (data.status === 'success') {
  // Initialize MSG91 widget
  msg91Widget.init({
    mobile: formData.get('mobile'),
    onSuccess: function(response) {
      // OTP sent successfully
      console.log('SMS OTP sent:', response);
    },
    onFailure: function(error) {
      // OTP sending failed
      console.error('SMS OTP failed:', error);
    },
    onVerification: function(response) {
      // OTP verified successfully
      console.log('SMS OTP verified:', response);
      // Continue with registration
    }
  });
}
```

## 🔧 Implementation Files

### Files to Update:
1. **register.html** - Add MSG91 widget
2. **login.html** - Add MSG91 widget for login OTP
3. **booking.html** - Add MSG91 widget for booking OTP
4. **php/register.php** - Update to work with widget
5. **php/verify_registration_otp.php** - Update verification logic

## 📱 Widget Configuration Summary

Your current widget settings:
- **Widget Name**: `TravelPlannerOTP`
- **OTP Length**: `6`
- **Expiration**: `15` minutes
- **Resend Count**: `2`
- **Resend After**: `30` seconds
- **Default Country**: `India (+91)`

## 🚀 Benefits of Widget Integration

1. **Better UX**: Users get a professional OTP interface
2. **Automatic Handling**: No need to manually send SMS
3. **Built-in Security**: Captcha and rate limiting
4. **Mobile Responsive**: Works on all devices
5. **Analytics**: Track OTP delivery and success rates

## 🔄 Migration Plan

### Phase 1: Widget Integration
- Add MSG91 widget to registration form
- Test with your mobile number: `9130123270`
- Verify widget functionality

### Phase 2: Backend Updates
- Update PHP files to work with widget
- Remove manual SMS sending code
- Keep email OTP as backup

### Phase 3: Testing & Deployment
- Test complete registration flow
- Test login OTP
- Test booking OTP
- Deploy to production

## 📞 Next Steps

1. **Get your Widget ID** from MSG91 dashboard
2. **Test the widget** with your mobile number
3. **Share the Widget ID** so I can help integrate it
4. **We'll update your forms** to use the widget

## 🎯 Expected Result

After integration, users will:
1. Fill registration form
2. Click "Register"
3. MSG91 widget automatically sends SMS
4. User enters OTP in widget interface
5. Widget verifies OTP automatically
6. Registration completes

This will provide a much better user experience than the current manual system! 