<?php
require_once 'php/config.php';

// Check if is_international column exists
$result = $conn->query("SHOW COLUMNS FROM bookings LIKE 'is_international'");
if ($result && $result->num_rows > 0) {
    echo "✅ is_international column already exists.<br>";
} else {
    echo "Adding is_international column...<br>";
    $sql = "ALTER TABLE bookings ADD COLUMN is_international TINYINT(1) NOT NULL DEFAULT 0 AFTER travel_style";
    if ($conn->query($sql)) {
        echo "✅ is_international column added successfully.<br>";
    } else {
        echo "❌ Failed to add is_international column: " . $conn->error . "<br>";
    }
}
?> 