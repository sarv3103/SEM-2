# Gmail App Password Setup Guide

## Step 1: Enable 2-Step Verification
1. Go to [Google Account Settings](https://myaccount.google.com/)
2. Click "Security" in the left sidebar
3. Find "2-Step Verification" and click "Get started"
4. Follow the setup process (usually involves your phone number)
5. **Important**: Complete the entire 2-Step Verification setup

## Step 2: Create App Password
1. Go back to [Google Account Settings](https://myaccount.google.com/)
2. Click "Security" → "2-Step Verification"
3. Scroll down to "App passwords" and click it
4. Click "Select app" → "Other (Custom name)"
5. Enter "TravelPlanner" as the name
6. Click "Generate"
7. **Copy the 16-character password** (it will look like: `pinm lcxd vbhe dwbl`)

## Step 3: Update Email Configuration
1. Open `php/email_config.php`
2. Replace the password line with your actual app password:
```php
$this->mailer->Password = 'your-16-char-app-password'; // Replace with the 16-character app password
```

## Step 4: Test Email
1. Run: `http://localhost/travelplanner/test_email_config.php`
2. Check your inbox for the test email

## Troubleshooting

### If you don't see "App passwords":
- Make sure 2-Step Verification is fully enabled
- Wait a few minutes after enabling 2-Step Verification
- Try refreshing the page

### If app password doesn't work:
- Make sure you copied all 16 characters exactly
- Don't include spaces in the password
- The password should look like: `abcd efgh ijkl mnop` (remove spaces when pasting)

### Common App Password Format:
- 16 characters total
- Usually 4 groups of 4 characters
- Example: `abcd efgh ijkl mnop`
- Remove spaces when using in code

## Security Notes
- App passwords are different from your regular Gmail password
- Each app password is unique and secure
- You can revoke app passwords anytime from Google Account settings
- Never share your app password publicly 