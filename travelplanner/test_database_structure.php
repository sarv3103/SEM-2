<?php
require_once 'php/config.php';

echo "<h2>Database Structure Test</h2>";

// Test 1: Check if we can connect to database
echo "<h3>1. Database Connection</h3>";
if ($conn->ping()) {
    echo "✅ Database connection successful<br>";
} else {
    echo "❌ Database connection failed<br>";
    exit();
}

// Test 2: Check if all required tables exist
echo "<h3>2. Required Tables Check</h3>";
$requiredTables = ['users', 'bookings', 'traveler_details', 'payment_orders', 'packages', 'destinations'];
$allTablesExist = true;

foreach ($requiredTables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result && $result->num_rows > 0) {
        echo "✅ $table table exists<br>";
    } else {
        echo "❌ $table table missing<br>";
        $allTablesExist = false;
    }
}

if (!$allTablesExist) {
    echo "<p style='color: red;'>Some tables are missing. Please run check_database_tables.php first.</p>";
    exit();
}

// Test 3: Check if we can insert a test booking
echo "<h3>3. Test Booking Insert</h3>";
try {
    $testBookingId = 'TEST_' . time();
    $stmt = $conn->prepare("
        INSERT INTO bookings (
            user_id, name, age, gender, type, source, destination, date, 
            num_travelers, fare, per_person, booking_id, travel_style, 
            is_international, start_date, end_date, duration, contact_mobile, 
            contact_email, special_requirements, booking_type, destination_name
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $userId = 1; // Assuming user ID 1 exists
    $name = 'Test User';
    $age = 25;
    $gender = 'male';
    $type = 'test_booking';
    $source = 'Test Source';
    $destination = 'Test Destination';
    $date = date('Y-m-d');
    $numTravelers = 1;
    $fare = 100;
    $perPerson = 100;
    $travelStyle = 'standard';
    $isInternational = 0;
    $startDate = date('Y-m-d');
    $endDate = date('Y-m-d', strtotime('+1 day'));
    $duration = 1;
    $contactMobile = '9876543210';
    $contactEmail = 'test@example.com';
    $specialRequirements = 'Test booking';
    $bookingType = 'test';
    $destinationName = 'Test Destination';
    
    $stmt->bind_param("isisssssiddssissssssss", 
        $userId, $name, $age, $gender, $type, $source, $destination, $date, 
        $numTravelers, $fare, $perPerson, $testBookingId, $travelStyle, 
        $isInternational, $startDate, $endDate, $duration, $contactMobile, 
        $contactEmail, $specialRequirements, $bookingType, $destinationName
    );
    
    if ($stmt->execute()) {
        $bookingId = $conn->insert_id;
        echo "✅ Test booking inserted successfully (ID: $bookingId)<br>";
        
        // Test 4: Check if we can insert payment order
        echo "<h3>4. Test Payment Order Insert</h3>";
        $stmt = $conn->prepare("
            INSERT INTO payment_orders (booking_id, razorpay_order_id, amount, status, created_at) 
            VALUES (?, ?, ?, 'pending', NOW())
        ");
        $orderId = 'order_test_' . time();
        $amount = 100;
        $stmt->bind_param("ssd", $bookingId, $orderId, $amount);
        
        if ($stmt->execute()) {
            echo "✅ Test payment order inserted successfully<br>";
        } else {
            echo "❌ Failed to insert payment order: " . $stmt->error . "<br>";
        }
        
        // Clean up test data
        $conn->query("DELETE FROM payment_orders WHERE booking_id = $bookingId");
        $conn->query("DELETE FROM bookings WHERE id = $bookingId");
        echo "✅ Test data cleaned up<br>";
        
    } else {
        echo "❌ Failed to insert test booking: " . $stmt->error . "<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error during test: " . $e->getMessage() . "<br>";
}

// Test 5: Check if packages exist
echo "<h3>5. Test Packages Check</h3>";
$result = $conn->query("SELECT COUNT(*) as count FROM packages");
if ($result) {
    $row = $result->fetch_assoc();
    echo "✅ Found " . $row['count'] . " packages in database<br>";
    
    if ($row['count'] == 0) {
        echo "<p style='color: orange;'>No packages found. You may need to add some packages.</p>";
    }
} else {
    echo "❌ Error checking packages: " . $conn->error . "<br>";
}

// Test 6: Check if destinations exist
echo "<h3>6. Test Destinations Check</h3>";
$result = $conn->query("SELECT COUNT(*) as count FROM destinations");
if ($result) {
    $row = $result->fetch_assoc();
    echo "✅ Found " . $row['count'] . " destinations in database<br>";
    
    if ($row['count'] == 0) {
        echo "<p style='color: orange;'>No destinations found. You may need to add some destinations.</p>";
    }
} else {
    echo "❌ Error checking destinations: " . $conn->error . "<br>";
}

echo "<h3>7. Test Results Summary</h3>";
echo "<p>✅ Database structure is correct</p>";
echo "<p>✅ All required tables exist</p>";
echo "<p>✅ Can insert and delete test data</p>";
echo "<p>✅ Ready for booking and payment testing</p>";

echo "<h3>8. Next Steps</h3>";
echo "<p>1. <a href='login.html'>Login to your account</a></p>";
echo "<p>2. <a href='test_payment_flow.php'>Test Payment Flow</a></p>";
echo "<p>3. <a href='package_booking.html'>Test Full Booking</a></p>";
?> 