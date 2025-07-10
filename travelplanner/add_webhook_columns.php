<?php
require_once 'php/config.php';

try {
    // Add webhook_processed column to payment_orders table
    $sql = "ALTER TABLE payment_orders ADD COLUMN webhook_processed TINYINT(1) DEFAULT 0";
    if ($conn->query($sql)) {
        echo "✅ Added webhook_processed column to payment_orders table<br>";
    } else {
        echo "❌ Error adding webhook_processed column to payment_orders: " . $conn->error . "<br>";
    }
    
    // Add webhook_processed column to bookings table
    $sql = "ALTER TABLE bookings ADD COLUMN webhook_processed TINYINT(1) DEFAULT 0";
    if ($conn->query($sql)) {
        echo "✅ Added webhook_processed column to bookings table<br>";
    } else {
        echo "❌ Error adding webhook_processed column to bookings: " . $conn->error . "<br>";
    }
    
    echo "<br>🎉 Database update completed!";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?> 