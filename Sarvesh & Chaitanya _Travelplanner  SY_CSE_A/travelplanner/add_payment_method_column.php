<?php
require_once 'php/config.php';

echo "<h2>Adding Missing payment_method Column</h2>";

// Check if payment_method column exists in payment_orders table
$result = $conn->query("SHOW COLUMNS FROM payment_orders LIKE 'payment_method'");
if ($result && $result->num_rows > 0) {
    echo "✅ payment_method column already exists in payment_orders table<br>";
} else {
    echo "Adding payment_method column to payment_orders table...<br>";
    $sql = "ALTER TABLE payment_orders ADD COLUMN payment_method VARCHAR(50) DEFAULT 'razorpay' AFTER amount";
    if ($conn->query($sql)) {
        echo "✅ payment_method column added successfully to payment_orders table<br>";
    } else {
        echo "❌ Failed to add payment_method column: " . $conn->error . "<br>";
    }
}

// Also check if other commonly missing columns exist
$columns_to_check = [
    'user_id' => "ALTER TABLE payment_orders ADD COLUMN user_id INT NULL AFTER booking_id",
    'payment_id' => "ALTER TABLE payment_orders ADD COLUMN payment_id VARCHAR(100) NULL AFTER razorpay_payment_id",
    'order_id' => "ALTER TABLE payment_orders ADD COLUMN order_id VARCHAR(100) NULL AFTER payment_id",
    'reference' => "ALTER TABLE payment_orders ADD COLUMN reference VARCHAR(255) NULL AFTER payment_method",
    'remarks' => "ALTER TABLE payment_orders ADD COLUMN remarks TEXT NULL AFTER reference",
    'updated_at' => "ALTER TABLE payment_orders ADD COLUMN updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP AFTER created_at"
];

foreach ($columns_to_check as $column => $sql) {
    $result = $conn->query("SHOW COLUMNS FROM payment_orders LIKE '$column'");
    if ($result && $result->num_rows > 0) {
        echo "✅ $column column already exists in payment_orders table<br>";
    } else {
        echo "Adding $column column to payment_orders table...<br>";
        if ($conn->query($sql)) {
            echo "✅ $column column added successfully<br>";
        } else {
            echo "❌ Failed to add $column column: " . $conn->error . "<br>";
        }
    }
}

// Show current table structure
echo "<h3>Current Payment Orders Table Structure:</h3>";
$result = $conn->query("DESCRIBE payment_orders");
if ($result) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Default']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Extra']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "<br><strong>✅ Database structure update completed!</strong>";
?> 