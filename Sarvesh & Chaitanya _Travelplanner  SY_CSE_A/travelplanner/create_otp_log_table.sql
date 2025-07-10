-- Create OTP log table for tracking OTP operations
CREATE TABLE IF NOT EXISTS `otp_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `mobile` varchar(20) DEFAULT NULL,
  `type` enum('registration','login','forgot_password','profile_update') NOT NULL,
  `status` enum('pending','success','failed','expired') NOT NULL DEFAULT 'pending',
  `details` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `email` (`email`),
  KEY `type` (`type`),
  KEY `status` (`status`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create OTP logs table for booking verification
USE travelplanner;

CREATE TABLE IF NOT EXISTS otp_logs (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL,
    mobile VARCHAR(15) NOT NULL,
    otp VARCHAR(6) NOT NULL,
    type ENUM('registration', 'forgot_password', 'booking_verification') NOT NULL,
    expiry DATETIME NOT NULL,
    used TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email_type (email, type),
    INDEX idx_expiry (expiry),
    INDEX idx_used (used)
);

-- Add used column to existing otp_logs table if it doesn't exist
ALTER TABLE otp_logs ADD COLUMN IF NOT EXISTS used TINYINT(1) DEFAULT 0;
ALTER TABLE otp_logs ADD COLUMN IF NOT EXISTS type ENUM('registration', 'forgot_password', 'booking_verification') DEFAULT 'registration'; 