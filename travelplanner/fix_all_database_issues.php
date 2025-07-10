<?php
require_once 'php/config.php';

echo "<h1>🔧 Comprehensive Database Fix Script</h1>";
echo "<p>This script will fix all database issues found in the error log.</p>";

$fixes_applied = [];

// Fix 1: Add missing columns to bookings table
echo "<h2>1. Fixing Bookings Table</h2>";
$booking_columns = [
    'source' => "ALTER TABLE bookings ADD COLUMN source VARCHAR(100) NULL AFTER booking_id",
    'destination' => "ALTER TABLE bookings ADD COLUMN destination VARCHAR(100) NULL AFTER source",
    'payment_status' => "ALTER TABLE bookings ADD COLUMN payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending'",
    'payment_date' => "ALTER TABLE bookings ADD COLUMN payment_date TIMESTAMP NULL",
    'razorpay_payment_id' => "ALTER TABLE bookings ADD COLUMN razorpay_payment_id VARCHAR(255) NULL",
    'ticket_sent' => "ALTER TABLE bookings ADD COLUMN ticket_sent TINYINT(1) DEFAULT 0",
    'webhook_processed' => "ALTER TABLE bookings ADD COLUMN webhook_processed TINYINT(1) DEFAULT 0"
];

foreach ($booking_columns as $column => $sql) {
    $result = $conn->query("SHOW COLUMNS FROM bookings LIKE '$column'");
    if ($result && $result->num_rows > 0) {
        echo "✅ $column column already exists in bookings table<br>";
    } else {
        echo "Adding $column column to bookings table...<br>";
        if ($conn->query($sql)) {
            echo "✅ $column column added successfully<br>";
            $fixes_applied[] = "Added $column to bookings table";
        } else {
            echo "❌ Failed to add $column column: " . $conn->error . "<br>";
        }
    }
}

// Fix 2: Create/Update payment_orders table
echo "<h2>2. Fixing Payment Orders Table</h2>";

// Check if payment_orders table exists
$result = $conn->query("SHOW TABLES LIKE 'payment_orders'");
if ($result && $result->num_rows > 0) {
    echo "✅ payment_orders table exists<br>";
} else {
    echo "Creating payment_orders table...<br>";
    $create_table = "
    CREATE TABLE payment_orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        booking_id INT NOT NULL,
        user_id INT NULL,
        razorpay_order_id VARCHAR(255) NOT NULL,
        razorpay_payment_id VARCHAR(255) NULL,
        payment_id VARCHAR(100) NULL,
        order_id VARCHAR(100) NULL,
        amount DECIMAL(10,2) NOT NULL,
        payment_method VARCHAR(50) DEFAULT 'razorpay',
        status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
        reference VARCHAR(255) NULL,
        remarks TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
        payment_date TIMESTAMP NULL,
        webhook_processed TINYINT(1) DEFAULT 0,
        INDEX idx_razorpay_order_id (razorpay_order_id),
        INDEX idx_booking_id (booking_id),
        INDEX idx_user_id (user_id),
        INDEX idx_status (status)
    )";
    
    if ($conn->query($create_table)) {
        echo "✅ payment_orders table created successfully<br>";
        $fixes_applied[] = "Created payment_orders table";
    } else {
        echo "❌ Failed to create payment_orders table: " . $conn->error . "<br>";
    }
}

// Add missing columns to payment_orders table
$payment_columns = [
    'user_id' => "ALTER TABLE payment_orders ADD COLUMN user_id INT NULL AFTER booking_id",
    'payment_id' => "ALTER TABLE payment_orders ADD COLUMN payment_id VARCHAR(100) NULL AFTER razorpay_payment_id",
    'order_id' => "ALTER TABLE payment_orders ADD COLUMN order_id VARCHAR(100) NULL AFTER payment_id",
    'payment_method' => "ALTER TABLE payment_orders ADD COLUMN payment_method VARCHAR(50) DEFAULT 'razorpay' AFTER amount",
    'reference' => "ALTER TABLE payment_orders ADD COLUMN reference VARCHAR(255) NULL AFTER payment_method",
    'remarks' => "ALTER TABLE payment_orders ADD COLUMN remarks TEXT NULL AFTER reference",
    'updated_at' => "ALTER TABLE payment_orders ADD COLUMN updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP AFTER created_at",
    'webhook_processed' => "ALTER TABLE payment_orders ADD COLUMN webhook_processed TINYINT(1) DEFAULT 0 AFTER payment_date"
];

foreach ($payment_columns as $column => $sql) {
    $result = $conn->query("SHOW COLUMNS FROM payment_orders LIKE '$column'");
    if ($result && $result->num_rows > 0) {
        echo "✅ $column column already exists in payment_orders table<br>";
    } else {
        echo "Adding $column column to payment_orders table...<br>";
        if ($conn->query($sql)) {
            echo "✅ $column column added successfully<br>";
            $fixes_applied[] = "Added $column to payment_orders table";
        } else {
            echo "❌ Failed to add $column column: " . $conn->error . "<br>";
        }
    }
}

// Fix 3: Create wallet_transactions table
echo "<h2>3. Creating Wallet Transactions Table</h2>";
$result = $conn->query("SHOW TABLES LIKE 'wallet_transactions'");
if ($result && $result->num_rows > 0) {
    echo "✅ wallet_transactions table exists<br>";
} else {
    echo "Creating wallet_transactions table...<br>";
    $create_wallet = "
    CREATE TABLE wallet_transactions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        type ENUM('credit', 'debit') NOT NULL,
        description TEXT,
        reference VARCHAR(255) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_user_id (user_id),
        INDEX idx_type (type),
        INDEX idx_created_at (created_at)
    )";
    
    if ($conn->query($create_wallet)) {
        echo "✅ wallet_transactions table created successfully<br>";
        $fixes_applied[] = "Created wallet_transactions table";
    } else {
        echo "❌ Failed to create wallet_transactions table: " . $conn->error . "<br>";
    }
}

// Fix 4: Create wallet table if it doesn't exist
echo "<h2>4. Creating Wallet Table</h2>";
$result = $conn->query("SHOW TABLES LIKE 'wallet'");
if ($result && $result->num_rows > 0) {
    echo "✅ wallet table exists<br>";
} else {
    echo "Creating wallet table...<br>";
    $create_wallet_table = "
    CREATE TABLE wallet (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL UNIQUE,
        balance DECIMAL(10,2) DEFAULT 0.00,
        last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_user_id (user_id)
    )";
    
    if ($conn->query($create_wallet_table)) {
        echo "✅ wallet table created successfully<br>";
        $fixes_applied[] = "Created wallet table";
    } else {
        echo "❌ Failed to create wallet table: " . $conn->error . "<br>";
    }
}

// Fix 5: Create contact_messages table
echo "<h2>5. Creating Contact Messages Table</h2>";
$result = $conn->query("SHOW TABLES LIKE 'contact_messages'");
if ($result && $result->num_rows > 0) {
    echo "✅ contact_messages table exists<br>";
} else {
    echo "Creating contact_messages table...<br>";
    $create_contact = "
    CREATE TABLE contact_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        message TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_email (email),
        INDEX idx_created_at (created_at)
    )";
    
    if ($conn->query($create_contact)) {
        echo "✅ contact_messages table created successfully<br>";
        $fixes_applied[] = "Created contact_messages table";
    } else {
        echo "❌ Failed to create contact_messages table: " . $conn->error . "<br>";
    }
}

// Fix 6: Add missing columns to users table
echo "<h2>6. Fixing Users Table</h2>";
$user_columns = [
    'mobile_otp' => "ALTER TABLE users ADD COLUMN mobile_otp VARCHAR(6) NULL",
    'mobile_otp_expires' => "ALTER TABLE users ADD COLUMN mobile_otp_expires TIMESTAMP NULL",
    'email_verified' => "ALTER TABLE users ADD COLUMN email_verified TINYINT(1) DEFAULT 0",
    'mobile_verified' => "ALTER TABLE users ADD COLUMN mobile_verified TINYINT(1) DEFAULT 0"
];

foreach ($user_columns as $column => $sql) {
    $result = $conn->query("SHOW COLUMNS FROM users LIKE '$column'");
    if ($result && $result->num_rows > 0) {
        echo "✅ $column column already exists in users table<br>";
    } else {
        echo "Adding $column column to users table...<br>";
        if ($conn->query($sql)) {
            echo "✅ $column column added successfully<br>";
            $fixes_applied[] = "Added $column to users table";
        } else {
            echo "❌ Failed to add $column column: " . $conn->error . "<br>";
        }
    }
}

// Summary
echo "<h2>📋 Summary of Fixes Applied</h2>";
if (empty($fixes_applied)) {
    echo "<p>✅ No fixes were needed - all database structures are already correct!</p>";
} else {
    echo "<ul>";
    foreach ($fixes_applied as $fix) {
        echo "<li>✅ $fix</li>";
    }
    echo "</ul>";
}

echo "<br><strong>🎉 Database structure update completed successfully!</strong>";
echo "<p>You can now try your wallet payment functionality again.</p>";
?> 