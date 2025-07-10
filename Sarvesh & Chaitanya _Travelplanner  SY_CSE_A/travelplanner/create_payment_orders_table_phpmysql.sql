-- Create payment orders table for Razorpay integration
-- Copy and paste this entire script into phpMyAdmin SQL tab

USE travelplanner;

CREATE TABLE IF NOT EXISTS payment_orders (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    booking_id INT(11) NOT NULL,
    razorpay_order_id VARCHAR(100) NOT NULL,
    razorpay_payment_id VARCHAR(100) NULL,
    amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    payment_date TIMESTAMP NULL,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    INDEX idx_razorpay_order_id (razorpay_order_id),
    INDEX idx_booking_id (booking_id),
    INDEX idx_status (status)
); 