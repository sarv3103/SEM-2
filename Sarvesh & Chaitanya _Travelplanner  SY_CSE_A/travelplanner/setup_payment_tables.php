<?php
require_once 'php/config.php';

echo "<h2>Setting up Payment Tables</h2>";

// Create payment_orders table
$sql = "
CREATE TABLE IF NOT EXISTS payment_orders (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    booking_id INT(11) NOT NULL,
    razorpay_order_id VARCHAR(100) NOT NULL,
    razorpay_payment_id VARCHAR(100) NULL,
    amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    payment_date TIMESTAMP NULL,
    INDEX idx_razorpay_order_id (razorpay_order_id),
    INDEX idx_booking_id (booking_id),
    INDEX idx_status (status)
)";

if ($conn->query($sql)) {
    echo "✅ payment_orders table created successfully<br>";
} else {
    echo "❌ Error creating payment_orders table: " . $conn->error . "<br>";
}

// Check if payment_status column exists in bookings table
$result = $conn->query("SHOW COLUMNS FROM bookings LIKE 'payment_status'");
if ($result && $result->num_rows > 0) {
    echo "✅ payment_status column already exists in bookings table<br>";
} else {
    echo "Adding payment_status column to bookings table...<br>";
    $sql = "ALTER TABLE bookings ADD COLUMN payment_status ENUM('pending', 'paid', 'failed') NOT NULL DEFAULT 'pending' AFTER is_international";
    if ($conn->query($sql)) {
        echo "✅ payment_status column added successfully<br>";
    } else {
        echo "❌ Failed to add payment_status column: " . $conn->error . "<br>";
    }
}

// Check if payment_date column exists in bookings table
$result = $conn->query("SHOW COLUMNS FROM bookings LIKE 'payment_date'");
if ($result && $result->num_rows > 0) {
    echo "✅ payment_date column already exists in bookings table<br>";
} else {
    echo "Adding payment_date column to bookings table...<br>";
    $sql = "ALTER TABLE bookings ADD COLUMN payment_date TIMESTAMP NULL AFTER payment_status";
    if ($conn->query($sql)) {
        echo "✅ payment_date column added successfully<br>";
    } else {
        echo "❌ Failed to add payment_date column: " . $conn->error . "<br>";
    }
}

// Check if ticket_sent column exists in bookings table
$result = $conn->query("SHOW COLUMNS FROM bookings LIKE 'ticket_sent'");
if ($result && $result->num_rows > 0) {
    echo "✅ ticket_sent column already exists in bookings table<br>";
} else {
    echo "Adding ticket_sent column to bookings table...<br>";
    $sql = "ALTER TABLE bookings ADD COLUMN ticket_sent TINYINT(1) NOT NULL DEFAULT 0 AFTER payment_date";
    if ($conn->query($sql)) {
        echo "✅ ticket_sent column added successfully<br>";
    } else {
        echo "❌ Failed to add ticket_sent column: " . $conn->error . "<br>";
    }
}

echo "<br><strong>Payment tables setup complete!</strong>";
?> 