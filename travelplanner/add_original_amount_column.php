<?php
require_once 'php/config.php';

echo "<h2>Adding original_amount column to payment_orders table...</h2>";
$result = $conn->query("SHOW COLUMNS FROM payment_orders LIKE 'original_amount'");
if ($result && $result->num_rows > 0) {
    echo "✅ original_amount column already exists.<br>";
} else {
    $sql = "ALTER TABLE payment_orders ADD COLUMN original_amount DECIMAL(10,2) DEFAULT NULL AFTER amount";
    if ($conn->query($sql)) {
        echo "✅ original_amount column added successfully.<br>";
    } else {
        echo "❌ Failed to add original_amount column: " . $conn->error . "<br>";
    }
}
?> 