<?php
require_once 'php/config.php';

echo "<h2>Adding Goa Destination</h2>";

// Check if Goa already exists
$goaName = "Goa";
$stmt = $conn->prepare("SELECT * FROM destinations WHERE name = ?");
$stmt->bind_param("s", $goaName);
$stmt->execute();
$result = $stmt->get_result();
$destination = $result->fetch_assoc();

if ($destination) {
    echo "✅ Goa destination already exists<br>";
    echo "Location: " . $destination['location'] . "<br>";
    echo "Price Range: " . $destination['price_range'] . "<br>";
} else {
    echo "Adding Goa destination...<br>";
    
    // Add Goa destination
    $stmt = $conn->prepare("INSERT INTO destinations (name, description, price_range, location, image_url) VALUES (?, ?, ?, ?, ?)");
    $name = 'Goa';
    $description = 'Beautiful beach destination in India with pristine beaches, vibrant nightlife, and Portuguese heritage';
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

// Add more popular destinations
$destinations = [
    ['Mumbai', 'City of Dreams with Bollywood, Gateway of India, and Marine Drive', '₹5000-15000', 'domestic', 'mumbai.jpg'],
    ['Delhi', 'Heart of India with Red Fort, Qutub Minar, and historical monuments', '₹3000-12000', 'domestic', 'delhi.jpg'],
    ['Bangalore', 'Garden City and IT hub of India', '₹4000-12000', 'domestic', 'bangalore.jpg'],
    ['Chennai', 'Gateway to South India with beaches and temples', '₹3500-10000', 'domestic', 'chennai.jpg'],
    ['Hyderabad', 'City of Pearls with Charminar and delicious biryani', '₹4000-12000', 'domestic', 'hyderabad.jpg'],
    ['Kolkata', 'City of Joy with Victoria Memorial and Howrah Bridge', '₹3000-10000', 'domestic', 'kolkata.jpg'],
    ['Pune', 'Oxford of the East with educational institutions', '₹2500-8000', 'domestic', 'pune.jpg'],
    ['Ahmedabad', 'Manchester of India with Sabarmati Ashram', '₹3000-10000', 'domestic', 'ahmedabad.jpg']
];

echo "<h3>Adding More Destinations</h3>";
foreach ($destinations as $dest) {
    $destName = $dest[0];
    $stmt = $conn->prepare("SELECT * FROM destinations WHERE name = ?");
    $stmt->bind_param("s", $destName);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        $stmt = $conn->prepare("INSERT INTO destinations (name, description, price_range, location, image_url) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $dest[0], $dest[1], $dest[2], $dest[3], $dest[4]);
        
        if ($stmt->execute()) {
            echo "✅ Added " . $dest[0] . "<br>";
        } else {
            echo "❌ Failed to add " . $dest[0] . ": " . $stmt->error . "<br>";
        }
    } else {
        echo "✅ " . $dest[0] . " already exists<br>";
    }
}

echo "<h3>Test Links</h3>";
echo "<p><a href='package_booking.html?type=destination&name=Goa'>→ Test Goa Booking</a></p>";
echo "<p><a href='index.html'>→ Go to Homepage</a></p>";
?> 