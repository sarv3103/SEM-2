<?php
require_once 'php/config.php';

// Check if travel_style column exists
$result = $conn->query("SHOW COLUMNS FROM bookings LIKE 'travel_style'");
if ($result && $result->num_rows > 0) {
    echo "✅ travel_style column already exists.<br>";
} else {
    echo "Adding travel_style column...<br>";
    $sql = "ALTER TABLE bookings ADD COLUMN travel_style ENUM('budget', 'standard', 'luxury') NOT NULL DEFAULT 'standard' AFTER booking_id";
    if ($conn->query($sql)) {
        echo "✅ travel_style column added successfully.<br>";
    } else {
        echo "❌ Failed to add travel_style column: " . $conn->error . "<br>";
    }
}
?> 