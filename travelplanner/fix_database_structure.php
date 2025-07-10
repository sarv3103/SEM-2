<?php
require_once 'php/config.php';

echo "<h2>Database Structure Fix</h2>";

// Check if bookings table has the correct structure
$result = $conn->query("SHOW COLUMNS FROM bookings LIKE 'id'");
if ($result && $result->num_rows > 0) {
    echo "✅ Bookings table has 'id' column<br>";
} else {
    echo "❌ Bookings table missing 'id' column<br>";
    
    // Try to add the column if it doesn't exist
    $conn->query("ALTER TABLE bookings ADD COLUMN id INT AUTO_INCREMENT PRIMARY KEY FIRST");
    echo "Attempted to add 'id' column<br>";
}

// Check if payment_orders table exists and has correct structure
$result = $conn->query("SHOW TABLES LIKE 'payment_orders'");
if ($result && $result->num_rows > 0) {
    echo "✅ Payment orders table exists<br>";
    
    // Check if it has booking_id column
    $result = $conn->query("SHOW COLUMNS FROM payment_orders LIKE 'booking_id'");
    if ($result && $result->num_rows > 0) {
        echo "✅ Payment orders table has 'booking_id' column<br>";
    } else {
        echo "❌ Payment orders table missing 'booking_id' column<br>";
    }
} else {
    echo "❌ Payment orders table does not exist<br>";
    
    // Create the table
    $sql = "CREATE TABLE payment_orders (
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
        echo "✅ Created payment_orders table<br>";
    } else {
        echo "❌ Failed to create payment_orders table: " . $conn->error . "<br>";
    }
}

// Show current table structures
echo "<h3>Current Bookings Table Structure:</h3>";
$result = $conn->query("DESCRIBE bookings");
if ($result) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "<h3>Current Payment Orders Table Structure:</h3>";
$result = $conn->query("DESCRIBE payment_orders");
if ($result) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}
?> 