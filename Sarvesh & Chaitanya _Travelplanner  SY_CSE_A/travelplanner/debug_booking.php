<?php
session_start();
require_once 'php/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "Please login first";
    exit();
}

echo "<h2>Booking Debug Test</h2>";

// Test booking data
$testBookingData = [
    'booking_type' => 'package',
    'destination_name' => 'Dubai Package',
    'start_date' => date('Y-m-d', strtotime('+7 days')),
    'end_date' => date('Y-m-d', strtotime('+14 days')),
    'num_travelers' => 2,
    'travel_style' => 'standard',
    'contact_mobile' => '9876543210',
    'contact_email' => 'test@example.com',
    'source_city' => 'Mumbai',
    'special_requirements' => 'Test booking',
    'travelers' => [
        [
            'name' => 'John Doe',
            'age' => 30,
            'gender' => 'male'
        ],
        [
            'name' => 'Jane Doe',
            'age' => 28,
            'gender' => 'female'
        ]
    ],
    'transport' => [
        'mode' => 'flight',
        'price' => 5000
    ],
    'mumbai_ticket' => [
        'skip_booking' => false,
        'from_city' => 'Delhi',
        'transport' => 'flight',
        'price' => 3000
    ],
    'total_amount' => 25000,
    'is_international' => false
];

echo "<h3>Test Data:</h3>";
echo "<pre>" . json_encode($testBookingData, JSON_PRETTY_PRINT) . "</pre>";

echo "<h3>Testing Booking Process:</h3>";

try {
    $response = file_get_contents('http://localhost/travelplanner/php/book_destination_package.php', false, stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/json',
            'content' => json_encode($testBookingData)
        ]
    ]));
    
    if ($response === false) {
        echo "<p style='color: red;'>Failed to make request</p>";
    } else {
        echo "<h4>Response:</h4>";
        echo "<pre>" . htmlspecialchars($response) . "</pre>";
        
        $data = json_decode($response, true);
        if ($data) {
            if ($data['status'] === 'success') {
                echo "<p style='color: green;'>✅ Booking successful!</p>";
                echo "<p>Booking ID: " . $data['booking_id'] . "</p>";
            } else {
                echo "<p style='color: red;'>❌ Booking failed: " . $data['message'] . "</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ Invalid JSON response</p>";
        }
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<h3>Database Check:</h3>";

// Check if bookings table exists
$result = $conn->query("SHOW TABLES LIKE 'bookings'");
if ($result && $result->num_rows > 0) {
    echo "✅ Bookings table exists<br>";
} else {
    echo "❌ Bookings table does not exist<br>";
}

// Check if traveler_details table exists
$result = $conn->query("SHOW TABLES LIKE 'traveler_details'");
if ($result && $result->num_rows > 0) {
    echo "✅ Traveler details table exists<br>";
} else {
    echo "❌ Traveler details table does not exist<br>";
}

// Check if destinations table exists
$result = $conn->query("SHOW TABLES LIKE 'destinations'");
if ($result && $result->num_rows > 0) {
    echo "✅ Destinations table exists<br>";
} else {
    echo "❌ Destinations table does not exist<br>";
}

// Check if packages table exists
$result = $conn->query("SHOW TABLES LIKE 'packages'");
if ($result && $result->num_rows > 0) {
    echo "✅ Packages table exists<br>";
} else {
    echo "❌ Packages table does not exist<br>";
}

echo "<h3>Test Package Data:</h3>";
$result = $conn->query("SELECT * FROM packages WHERE name LIKE '%Dubai%' LIMIT 1");
if ($result && $result->num_rows > 0) {
    $package = $result->fetch_assoc();
    echo "✅ Found package: " . $package['name'] . "<br>";
} else {
    echo "❌ Dubai package not found in database<br>";
    echo "<p>Creating test package...</p>";
    
    $stmt = $conn->prepare("INSERT INTO packages (name, description, price_per_person, type, image_url) VALUES (?, ?, ?, ?, ?)");
    $name = 'Dubai Package';
    $description = 'Amazing Dubai tour package';
    $price = 25000;
    $type = 'international';
    $image = 'dubai.jpg';
    
    $stmt->bind_param("ssdis", $name, $description, $price, $type, $image);
    if ($stmt->execute()) {
        echo "✅ Test package created<br>";
    } else {
        echo "❌ Failed to create test package: " . $stmt->error . "<br>";
    }
}
?> 