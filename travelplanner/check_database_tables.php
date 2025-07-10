<?php
require_once 'php/config.php';

echo "<h2>Database Tables Check</h2>";

// Check and create bookings table if needed
echo "<h3>1. Checking Bookings Table</h3>";
$result = $conn->query("SHOW TABLES LIKE 'bookings'");
if ($result && $result->num_rows > 0) {
    echo "✅ Bookings table exists<br>";
} else {
    echo "❌ Bookings table does not exist. Creating...<br>";
    
    $sql = "CREATE TABLE bookings (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        user_id INT(11) NOT NULL,
        name VARCHAR(100) NOT NULL,
        age INT(3) NOT NULL,
        gender ENUM('male', 'female', 'other') NOT NULL,
        type VARCHAR(50) NOT NULL,
        source VARCHAR(100) NOT NULL,
        destination VARCHAR(100) NOT NULL,
        date DATE NOT NULL,
        num_travelers INT(3) NOT NULL,
        fare DECIMAL(10,2) NOT NULL,
        per_person DECIMAL(10,2) NOT NULL,
        booking_id VARCHAR(50) UNIQUE NOT NULL,
        travel_style ENUM('budget', 'standard', 'luxury') NOT NULL,
        is_international TINYINT(1) DEFAULT 0,
        start_date DATE NOT NULL,
        end_date DATE NOT NULL,
        duration INT(3) NOT NULL,
        contact_mobile VARCHAR(15) NOT NULL,
        contact_email VARCHAR(100) NOT NULL,
        special_requirements TEXT,
        booking_type VARCHAR(50) NOT NULL,
        destination_name VARCHAR(100) NOT NULL,
        payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending',
        payment_date TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    
    if ($conn->query($sql)) {
        echo "✅ Bookings table created successfully<br>";
    } else {
        echo "❌ Failed to create bookings table: " . $conn->error . "<br>";
    }
}

// Check and create traveler_details table if needed
echo "<h3>2. Checking Traveler Details Table</h3>";
$result = $conn->query("SHOW TABLES LIKE 'traveler_details'");
if ($result && $result->num_rows > 0) {
    echo "✅ Traveler details table exists<br>";
} else {
    echo "❌ Traveler details table does not exist. Creating...<br>";
    
    $sql = "CREATE TABLE traveler_details (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        booking_id INT(11) NOT NULL,
        traveler_number INT(3) NOT NULL,
        name VARCHAR(100) NOT NULL,
        age INT(3) NOT NULL,
        gender ENUM('male', 'female', 'other') NOT NULL,
        passport_number VARCHAR(20),
        nationality VARCHAR(50) DEFAULT 'Indian',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
    )";
    
    if ($conn->query($sql)) {
        echo "✅ Traveler details table created successfully<br>";
    } else {
        echo "❌ Failed to create traveler details table: " . $conn->error . "<br>";
    }
}

// Check and create payment_orders table if needed
echo "<h3>3. Checking Payment Orders Table</h3>";
$result = $conn->query("SHOW TABLES LIKE 'payment_orders'");
if ($result && $result->num_rows > 0) {
    echo "✅ Payment orders table exists<br>";
} else {
    echo "❌ Payment orders table does not exist. Creating...<br>";
    
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
        echo "✅ Payment orders table created successfully<br>";
    } else {
        echo "❌ Failed to create payment orders table: " . $conn->error . "<br>";
    }
}

// Check and create packages table if needed
echo "<h3>4. Checking Packages Table</h3>";
$result = $conn->query("SHOW TABLES LIKE 'packages'");
if ($result && $result->num_rows > 0) {
    echo "✅ Packages table exists<br>";
} else {
    echo "❌ Packages table does not exist. Creating...<br>";
    
    $sql = "CREATE TABLE packages (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        price_per_person DECIMAL(10,2) NOT NULL,
        type ENUM('domestic', 'international') DEFAULT 'domestic',
        image_url VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($sql)) {
        echo "✅ Packages table created successfully<br>";
        
        // Insert some test packages
        $testPackages = [
            ['Dubai Package', 'Amazing Dubai tour package', 25000, 'international', 'dubai.jpg'],
            ['Goa Package', 'Beautiful Goa beach package', 15000, 'domestic', 'goa.jpg'],
            ['Kerala Package', 'Scenic Kerala backwaters', 18000, 'domestic', 'kerala.jpg']
        ];
        
        $stmt = $conn->prepare("INSERT INTO packages (name, description, price_per_person, type, image_url) VALUES (?, ?, ?, ?, ?)");
        foreach ($testPackages as $package) {
            $stmt->bind_param("ssdss", $package[0], $package[1], $package[2], $package[3], $package[4]);
            $stmt->execute();
        }
        echo "✅ Test packages inserted<br>";
    } else {
        echo "❌ Failed to create packages table: " . $conn->error . "<br>";
    }
}

// Check and create destinations table if needed
echo "<h3>5. Checking Destinations Table</h3>";
$result = $conn->query("SHOW TABLES LIKE 'destinations'");
if ($result && $result->num_rows > 0) {
    echo "✅ Destinations table exists<br>";
} else {
    echo "❌ Destinations table does not exist. Creating...<br>";
    
    $sql = "CREATE TABLE destinations (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        price_range VARCHAR(50),
        location ENUM('domestic', 'international') DEFAULT 'domestic',
        image_url VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($sql)) {
        echo "✅ Destinations table created successfully<br>";
        
        // Insert some test destinations
        $testDestinations = [
            ['Mumbai', 'City of Dreams', '₹5000-15000', 'domestic', 'mumbai.jpg'],
            ['Delhi', 'Heart of India', '₹3000-12000', 'domestic', 'delhi.jpg'],
            ['Dubai', 'City of Gold', '₹20000-50000', 'international', 'dubai.jpg']
        ];
        
        $stmt = $conn->prepare("INSERT INTO destinations (name, description, price_range, location, image_url) VALUES (?, ?, ?, ?, ?)");
        foreach ($testDestinations as $destination) {
            $stmt->bind_param("sssss", $destination[0], $destination[1], $destination[2], $destination[3], $destination[4]);
            $stmt->execute();
        }
        echo "✅ Test destinations inserted<br>";
    } else {
        echo "❌ Failed to create destinations table: " . $conn->error . "<br>";
    }
}

echo "<h3>6. All Tables Status</h3>";
$tables = ['users', 'bookings', 'traveler_details', 'payment_orders', 'packages', 'destinations'];
foreach ($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result && $result->num_rows > 0) {
        echo "✅ $table table exists<br>";
    } else {
        echo "❌ $table table missing<br>";
    }
}

echo "<h3>7. Test Links</h3>";
echo "<a href='test_payment_flow.php'>Test Payment Flow</a><br>";
echo "<a href='debug_booking.php'>Debug Booking</a><br>";
echo "<a href='simple_payment_test.php'>Simple Payment Test</a><br>";
?> 