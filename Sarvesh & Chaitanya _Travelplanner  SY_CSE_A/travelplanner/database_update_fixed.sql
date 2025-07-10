-- Database Update for OTP Verification System (Fixed)
-- Run this in phpMyAdmin to add OTP functionality

-- Check and add first_name column if it doesn't exist
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'users' 
     AND COLUMN_NAME = 'first_name') = 0,
    'ALTER TABLE users ADD COLUMN first_name VARCHAR(50) NULL AFTER id',
    'SELECT "first_name column already exists" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Check and add last_name column if it doesn't exist
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'users' 
     AND COLUMN_NAME = 'last_name') = 0,
    'ALTER TABLE users ADD COLUMN last_name VARCHAR(50) NULL AFTER first_name',
    'SELECT "last_name column already exists" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Check and add mobile column if it doesn't exist
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'users' 
     AND COLUMN_NAME = 'mobile') = 0,
    'ALTER TABLE users ADD COLUMN mobile VARCHAR(15) NULL AFTER last_name',
    'SELECT "mobile column already exists" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Check and add email_verified column if it doesn't exist
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'users' 
     AND COLUMN_NAME = 'email_verified') = 0,
    'ALTER TABLE users ADD COLUMN email_verified TINYINT(1) DEFAULT 0 AFTER mobile',
    'SELECT "email_verified column already exists" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Check and add mobile_verified column if it doesn't exist
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'users' 
     AND COLUMN_NAME = 'mobile_verified') = 0,
    'ALTER TABLE users ADD COLUMN mobile_verified TINYINT(1) DEFAULT 0 AFTER email_verified',
    'SELECT "mobile_verified column already exists" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Check and add email_otp column if it doesn't exist
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'users' 
     AND COLUMN_NAME = 'email_otp') = 0,
    'ALTER TABLE users ADD COLUMN email_otp VARCHAR(6) NULL AFTER mobile_verified',
    'SELECT "email_otp column already exists" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Check and add mobile_otp column if it doesn't exist
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'users' 
     AND COLUMN_NAME = 'mobile_otp') = 0,
    'ALTER TABLE users ADD COLUMN mobile_otp VARCHAR(6) NULL AFTER email_otp',
    'SELECT "mobile_otp column already exists" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Check and add email_otp_expires column if it doesn't exist
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'users' 
     AND COLUMN_NAME = 'email_otp_expires') = 0,
    'ALTER TABLE users ADD COLUMN email_otp_expires TIMESTAMP NULL AFTER email_otp',
    'SELECT "email_otp_expires column already exists" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Check and add mobile_otp_expires column if it doesn't exist
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'users' 
     AND COLUMN_NAME = 'mobile_otp_expires') = 0,
    'ALTER TABLE users ADD COLUMN mobile_otp_expires TIMESTAMP NULL AFTER mobile_otp',
    'SELECT "mobile_otp_expires column already exists" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Create OTP verification table for booking confirmations (if not exists)
CREATE TABLE IF NOT EXISTS booking_otp (
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

-- Create payment verification table (if not exists)
CREATE TABLE IF NOT EXISTS payment_otp (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    booking_id VARCHAR(50) NOT NULL,
    user_id INT(11) NOT NULL,
    mobile_otp VARCHAR(6) NOT NULL,
    mobile_otp_expires TIMESTAMP NOT NULL,
    verified TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Create contact verification table (if not exists)
CREATE TABLE IF NOT EXISTS contact_otp (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL,
    email_otp VARCHAR(6) NOT NULL,
    email_otp_expires TIMESTAMP NOT NULL,
    verified TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Drop and recreate SMS log table with success column
DROP TABLE IF EXISTS sms_log;
CREATE TABLE sms_log (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    mobile VARCHAR(15) NOT NULL,
    message TEXT NOT NULL,
    otp VARCHAR(6) NOT NULL,
    success TINYINT(1) DEFAULT 0,
    api_response TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
); 