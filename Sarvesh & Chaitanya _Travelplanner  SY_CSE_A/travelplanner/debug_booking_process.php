<?php
session_start();
require_once 'php/config.php';

echo "<h2>Booking Process Debug</h2>";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<p>Please <a href='login.html'>login first</a> to debug the booking process.</p>";
    exit();
}

$userId = $_SESSION['user_id'];
echo "<p>✅ User logged in (ID: $userId)</p>";

// Test 1: Check if all required tables exist
echo "<h3>1. Database Tables Check</h3>";
$requiredTables = ['users', 'bookings', 'traveler_details', 'payment_orders', 'packages', 'destinations'];
foreach ($requiredTables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result && $result->num_rows > 0) {
        echo "✅ $table table exists<br>";
    } else {
        echo "❌ $table table missing<br>";
    }
}

// Test 2: Check bookings table structure
echo "<h3>2. Bookings Table Structure</h3>";
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

// Test 3: Check if Goa destination exists
echo "<h3>3. Destination Check (Goa)</h3>";
$stmt = $conn->prepare("SELECT * FROM destinations WHERE name = ?");
$stmt->bind_param("s", "Goa");
$stmt->execute();
$result = $stmt->get_result();
$destination = $result->fetch_assoc();

if ($destination) {
    echo "✅ Goa destination found<br>";
    echo "Location: " . $destination['location'] . "<br>";
    echo "Price Range: " . $destination['price_range'] . "<br>";
} else {
    echo "❌ Goa destination not found<br>";
    
    // Add Goa destination if missing
    echo "Adding Goa destination...<br>";
    $stmt = $conn->prepare("INSERT INTO destinations (name, description, price_range, location, image_url) VALUES (?, ?, ?, ?, ?)");
    $name = 'Goa';
    $description = 'Beautiful beach destination in India';
    $priceRange = '₹8000-20000';
    $location = 'domestic';
    $imageUrl = 'goa.jpg';
    $stmt->bind_param("sssss", $name, $description, $priceRange, $location, $imageUrl);
    
    if ($stmt->execute()) {
        echo "✅ Goa destination added successfully<br>";
    } else {
        echo "❌ Failed to add Goa destination: " . $stmt->error . "<br>";
    }
}

// Test 4: Simulate booking data
echo "<h3>4. Simulate Booking Data</h3>";
$testBookingData = [
    'booking_type' => 'destination',
    'destination_name' => 'Goa',
    'start_date' => date('Y-m-d', strtotime('+7 days')),
    'end_date' => date('Y-m-d', strtotime('+10 days')),
    'num_travelers' => 2,
    'travel_style' => 'standard',
    'contact_mobile' => '9876543210',
    'contact_email' => 'test@example.com',
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
        'type' => 'flight',
        'price' => 5000
    ],
    'source_city' => 'Mumbai',
    'special_requirements' => 'Test booking',
    'is_international' => false
];

echo "✅ Test booking data prepared<br>";

// Test 5: Test database insertion
echo "<h3>5. Test Database Insertion</h3>";
try {
    // Generate booking ID
    $bookingId = 'TEST_' . time();
    
    // Test booking insertion
    $stmt = $conn->prepare("
        INSERT INTO bookings (
            user_id, name, age, gender, type, source, destination, date, 
            num_travelers, fare, per_person, booking_id, travel_style, 
            is_international, start_date, end_date, duration, contact_mobile, 
            contact_email, special_requirements, booking_type, destination_name
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $mainTraveler = $testBookingData['travelers'][0];
    $type = 'destination_booking';
    $source = 'Mumbai';
    $date = $testBookingData['start_date'];
    $numTravelers = $testBookingData['num_travelers'];
    $totalAmount = 25000;
    $totalPerPerson = 12500;
    $travelStyle = $testBookingData['travel_style'];
    $isInternational = false;
    $startDate = $testBookingData['start_date'];
    $endDate = $testBookingData['end_date'];
    $duration = 4;
    $contactMobile = $testBookingData['contact_mobile'];
    $contactEmail = $testBookingData['contact_email'];
    $specialRequirements = $testBookingData['special_requirements'];
    $bookingType = $testBookingData['booking_type'];
    $destinationName = $testBookingData['destination_name'];
    
    $stmt->bind_param("isisssssiddssissssssss", 
        $userId, 
        $mainTraveler['name'], 
        $mainTraveler['age'], 
        $mainTraveler['gender'], 
        $type, 
        $source, 
        $destinationName, 
        $date, 
        $numTravelers, 
        $totalAmount, 
        $totalPerPerson, 
        $bookingId, 
        $travelStyle, 
        $isInternational,
        $startDate,
        $endDate,
        $duration,
        $contactMobile,
        $contactEmail,
        $specialRequirements,
        $bookingType,
        $destinationName
    );
    
    if ($stmt->execute()) {
        $bookingDbId = $conn->insert_id;
        echo "✅ Test booking inserted successfully (ID: $bookingDbId)<br>";
        
        // Test traveler details insertion
        $stmt = $conn->prepare("
            INSERT INTO traveler_details (
                booking_id, traveler_number, name, age, gender, 
                passport_number, nationality
            ) VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        foreach ($testBookingData['travelers'] as $index => $traveler) {
            $travelerNumber = $index + 1;
            $passportNumber = '';
            $nationality = 'Indian';
            
            $stmt->bind_param("iisiss", 
                $bookingDbId, 
                $travelerNumber, 
                $traveler['name'], 
                $traveler['age'], 
                $traveler['gender'],
                $passportNumber,
                $nationality
            );
            
            if ($stmt->execute()) {
                echo "✅ Traveler " . ($index + 1) . " details inserted<br>";
            } else {
                echo "❌ Failed to insert traveler " . ($index + 1) . ": " . $stmt->error . "<br>";
            }
        }
        
        // Clean up test data
        $conn->query("DELETE FROM traveler_details WHERE booking_id = $bookingDbId");
        $conn->query("DELETE FROM bookings WHERE id = $bookingDbId");
        echo "✅ Test data cleaned up<br>";
        
    } else {
        echo "❌ Failed to insert test booking: " . $stmt->error . "<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error during test: " . $e->getMessage() . "<br>";
}

echo "<h3>6. Next Steps</h3>";
echo "<p>If all tests passed above, try the booking again:</p>";
echo "<p><a href='package_booking.html?type=destination&name=Goa'>→ Test Goa Booking</a></p>";
echo "<p><a href='test_complete_booking.php'>→ Complete Booking Test</a></p>";
?> 