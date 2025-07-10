# ₹1 Payment Testing System

## 🎯 **System Overview**

This system is configured to show the **original booking amount** but only charge **₹1** through Razorpay for testing purposes.

### **How It Works:**
- ✅ **Original Amount Displayed:** All tickets, packages, and destinations show their actual prices
- ✅ **₹1 Payment Only:** Razorpay only charges ₹1 for all bookings
- ✅ **Live Keys Used:** Using your live Razorpay keys since ₹1 is a minimal charge
- ✅ **Full Functionality:** All booking features work normally

## 📋 **What Users See:**

### **Booking Summary:**
```
Destination: Dubai Package
Original Price: ₹25,000
Mumbai Ticket: ₹3,500
Total Amount: ₹28,500
```

### **Payment Process:**
```
Razorpay Payment: ₹1
Description: "Booking for Dubai Package (₹1 test payment)"
```

### **Success Message:**
```
Booking ID: BK123456
Payment ID: pay_xxxxxxxxx
Original Amount: ₹28,500
Test Payment: ₹1
```

## 🧪 **Testing URLs:**

### **1. Simple Payment Test**
```
http://localhost/travelplanner/simple_payment_test.php
```
- Tests only Razorpay integration
- Charges ₹1

### **2. Full Booking Test**
```
http://localhost/travelplanner/test_payment_flow.php
```
- Tests complete booking flow
- Shows original amount, charges ₹1

### **3. Connection Test**
```
http://localhost/travelplanner/test_razorpay_connection.php
```
- Tests Razorpay API connection
- Creates ₹1 test order

### **4. Actual Booking Page**
```
http://localhost/travelplanner/package_booking.html
```
- Real booking system
- Shows original prices, charges ₹1

## 💳 **Payment Testing:**

### **Test Cards (Any of These):**
- **Card:** `4111 1111 1111 1111`
- **Expiry:** Any future date
- **CVV:** Any 3 digits
- **Name:** Any name

### **UPI Testing:**
- **UPI ID:** `success@razorpay`
- **Amount:** ₹1

## 🔧 **Configuration Files:**

### **1. php/razorpay_config.php**
```php
$keyId = 'rzp_live_2JdrplZN9MSywf';
$keySecret = '8JHRkWgt4C286TQoNZErbmdK';
```

### **2. php/process_payment.php**
```php
$testAmount = 1; // Always charge ₹1
$originalAmount = $input['amount']; // Store original amount
```

### **3. package_booking.html**
```javascript
amount: paymentData.amount * 100, // ₹1 in paise
description: `Booking for ${destination} (₹1 test payment)`
```

## 📊 **Database Storage:**

### **Payment Orders Table:**
- `amount` field stores the **original amount** (e.g., ₹28,500)
- Razorpay order is created for **₹1**
- Full booking details preserved

### **Example Record:**
```
booking_id: 123
razorpay_order_id: order_xxxxxxxxx
amount: 28500.00 (original amount)
razorpay_payment_id: pay_xxxxxxxxx
status: completed
```

## 🚀 **Benefits of This Approach:**

1. **Minimal Cost:** Only ₹1 charged for testing
2. **Real Integration:** Uses live Razorpay keys
3. **Full Testing:** All features work normally
4. **Easy Transition:** Can easily switch to full amounts later
5. **User Experience:** Users see real prices but pay minimal amount

## 🔄 **Switching to Full Payment:**

When ready for production, simply update `php/process_payment.php`:

```php
// Change this line:
$testAmount = 1; // Always charge ₹1 for testing

// To this:
$testAmount = $originalAmount; // Charge full amount
```

## ⚠️ **Important Notes:**

- **Live Keys:** Using live Razorpay keys since ₹1 is minimal
- **Real Transactions:** ₹1 payments are real transactions
- **Testing Only:** This is for testing purposes only
- **Easy Switch:** Can easily switch to full amounts later

## 🎯 **Next Steps:**

1. **Test the system** with ₹1 payments
2. **Verify all features** work correctly
3. **When ready for production**, switch to full amounts
4. **Update pricing** as needed

**The system is now ready for ₹1 payment testing!** 🎉 