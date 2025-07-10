-- Fix SMS Log Table Structure
-- Run this in phpMyAdmin to fix the SMS service

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