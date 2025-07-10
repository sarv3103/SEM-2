# Razorpay Test Guide

## 🎯 **Current Setup: TEST MODE**
- Using Razorpay **test keys** for development
- All payments are **test transactions** (no real money)
- Perfect for testing the booking system

## 🧪 **How to Test Payments**

### **Test Cards (Use Any of These):**

#### **Domestic Cards (Recommended):**
- **Card Number:** `4111 1111 1111 1111`
- **Expiry:** Any future date (e.g., 12/25)
- **CVV:** Any 3 digits (e.g., 123)
- **Name:** Any name

#### **International Cards:**
- **Card Number:** `4000 0000 0000 0002`
- **Expiry:** Any future date
- **CVV:** Any 3 digits
- **Name:** Any name

#### **UPI Testing:**
- **UPI ID:** `success@razorpay`
- **Amount:** Any amount

## 🚀 **Test URLs**

### **1. Simple Payment Test**
```
http://localhost/travelplanner/simple_payment_test.php
```
- Tests only Razorpay integration
- No database dependencies
- Quick verification

### **2. Full Booking Test**
```
http://localhost/travelplanner/test_payment_flow.php
```
- Tests complete booking + payment flow
- Creates database records
- Full integration test

### **3. Setup Verification**
```
http://localhost/travelplanner/test_razorpay_setup.php
```
- Checks all configurations
- Verifies database tables
- Confirms API keys

## 📋 **Test Steps**

1. **Login to your account first**
2. **Visit any test URL above**
3. **Click "Test Payment"**
4. **Use test card details:**
   - Card: `4111 1111 1111 1111`
   - Expiry: `12/25`
   - CVV: `123`
   - Name: `Test User`
5. **Complete payment**
6. **Verify success message**

## ✅ **Expected Results**

### **Successful Payment:**
- Payment modal opens
- Card details accepted
- Success message appears
- Payment ID shown
- No real money deducted

### **If Payment Fails:**
- Check browser console for errors
- Verify you're logged in
- Try different test card
- Check network connection

## 🔄 **Switching to Production**

When ready for live payments:

1. **Update `php/razorpay_config.php`:**
   ```php
   $keyId = 'rzp_live_2JdrplZN9MSywf';
   $keySecret = '8JHRkWgt4C286TQoNZErbmdK';
   ```

2. **Update `php/process_payment.php`:**
   ```php
   'key_id' => 'rzp_live_2JdrplZN9MSywf'
   ```

3. **Update booking page:**
   ```javascript
   key: 'rzp_live_2JdrplZN9MSywf'
   ```

## 🚨 **Important Notes**

- **Test Mode:** No real money is charged
- **Live Mode:** Real transactions occur
- **Always test with small amounts first**
- **Keep test keys for development**
- **Use live keys only for production**

## 🆘 **Troubleshooting**

### **"International cards not supported"**
- Use domestic test card: `4111 1111 1111 1111`
- Or use UPI: `success@razorpay`

### **"Payment failed"**
- Check browser console
- Verify API keys are correct
- Ensure you're logged in

### **"Order creation failed"**
- Check Razorpay account status
- Verify API keys
- Check network connection

## 📞 **Support**

- **Razorpay Docs:** https://razorpay.com/docs/
- **Test Cards:** https://razorpay.com/docs/payments/test-mode/
- **API Reference:** https://razorpay.com/docs/api/ 