<?php
require_once 'php/config.php';

// Check if payment_status column exists
$result = $conn->query("SHOW COLUMNS FROM bookings LIKE 'payment_status'");
if ($result && $result->num_rows > 0) {
    echo "✅ payment_status column already exists.<br>";
} else {
    echo "Adding payment_status column...<br>";
    $sql = "ALTER TABLE bookings ADD COLUMN payment_status ENUM('pending', 'paid', 'failed') NOT NULL DEFAULT 'pending' AFTER is_international";
    if ($conn->query($sql)) {
        echo "✅ payment_status column added successfully.<br>";
    } else {
        echo "❌ Failed to add payment_status column: " . $conn->error . "<br>";
    }
}

// Check if payment_date column exists
$result = $conn->query("SHOW COLUMNS FROM bookings LIKE 'payment_date'");
if ($result && $result->num_rows > 0) {
    echo "✅ payment_date column already exists.<br>";
} else {
    echo "Adding payment_date column...<br>";
    $sql = "ALTER TABLE bookings ADD COLUMN payment_date TIMESTAMP NULL AFTER payment_status";
    if ($conn->query($sql)) {
        echo "✅ payment_date column added successfully.<br>";
    } else {
        echo "❌ Failed to add payment_date column: " . $conn->error . "<br>";
    }
}
?> 