<?php
require_once 'php/config.php';

$sql = "CREATE TABLE IF NOT EXISTS `otp_log` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

if ($conn->query($sql)) {
    echo "✅ OTP log table created successfully\n";
} else {
    echo "❌ Error creating table: " . $conn->error . "\n";
}
?> 