-- Database Update for OTP Verification System
-- Run this in phpMyAdmin to add OTP functionality

-- Add OTP fields to users table
ALTER TABLE users 
ADD COLUMN first_name VARCHAR(50) NULL AFTER id,
ADD COLUMN last_name VARCHAR(50) NULL AFTER first_name,
ADD COLUMN mobile VARCHAR(15) NULL AFTER last_name,
ADD COLUMN email_verified TINYINT(1) DEFAULT 0 AFTER mobile,
ADD COLUMN mobile_verified TINYINT(1) DEFAULT 0 AFTER email_verified,
ADD COLUMN email_otp VARCHAR(6) NULL AFTER mobile_verified,
ADD COLUMN mobile_otp VARCHAR(6) NULL AFTER email_otp,
ADD COLUMN email_otp_expires TIMESTAMP NULL AFTER email_otp,
ADD COLUMN mobile_otp_expires TIMESTAMP NULL AFTER mobile_otp;

-- Create OTP verification table for booking confirmations
CREATE TABLE booking_otp (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    booking_id VARCHAR(50) NOT NULL,
    user_id INT(11) NOT NULL,
    email_otp VARCHAR(6) NULL,
    mobile_otp VARCHAR(6) NULL,
    email_otp_expires TIMESTAMP NULL,
    mobile_otp_expires TIMESTAMP NULL,
    email_verified TINYINT(1) DEFAULT 0,
    mobile_verified TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Create payment verification table
CREATE TABLE payment_otp (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    booking_id VARCHAR(50) NOT NULL,
    user_id INT(11) NOT NULL,
    mobile_otp VARCHAR(6) NOT NULL,
    mobile_otp_expires TIMESTAMP NOT NULL,
    verified TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Create contact verification table
CREATE TABLE contact_otp (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL,
    email_otp VARCHAR(6) NOT NULL,
    email_otp_expires TIMESTAMP NOT NULL,
    verified TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
); 