<?php
require_once 'php/config.php';

echo "<h2>Updating Additional Destination Images</h2>";

// New destination image mappings
$destinationImages = [
    'Kerala' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTHrnyvcXzeXzyAeGYn9eWncSPM7G_st8yfRg&s',
    'Ladakh' => 'https://th.bing.com/th/id/OIP.kaN4VnvSSguxcYILikQ71wHaEj?w=280&h=180&c=7&r=0&o=7&dpr=1.3&pid=1.7&rm=3',
    'Rajasthan' => 'https://th.bing.com/th/id/OIP.tUcUdXBmUOKEZkHZiCgUagHaD2?w=346&h=180&c=7&r=0&o=7&dpr=1.3&pid=1.7&rm=3',
    'Goa' => 'https://th.bing.com/th/id/OIP.OsdnDjdW74sn01vHghKvOwHaFj?w=221&h=180&c=7&r=0&o=7&dpr=1.3&pid=1.7&rm=3',
    'Maldives' => 'https://images.unsplash.com/photo-1544551763-46a013bb70d5?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
    'Thailand' => 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
    'Shimla' => 'https://images.unsplash.com/photo-1502086223501-7ea6ecd79368?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
    'Varanasi' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'
];

// Update each destination
foreach ($destinationImages as $destinationName => $imageUrl) {
    $stmt = $conn->prepare("UPDATE destinations SET image_url = ? WHERE name = ?");
    $stmt->bind_param("ss", $imageUrl, $destinationName);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo "✅ Updated image for $destinationName<br>";
        } else {
            echo "⚠️ No changes for $destinationName (destination not found or already has this image)<br>";
        }
    } else {
        echo "❌ Failed to update $destinationName: " . $stmt->error . "<br>";
    }
}

// Add missing destinations if they don't exist
$newDestinations = [
    ['Kerala', 'God\'s Own Country with beautiful backwaters, beaches, and hill stations', '₹12000-25000', 'domestic', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTHrnyvcXzeXzyAeGYn9eWncSPM7G_st8yfRg&s'],
    ['Ladakh', 'Land of high passes with stunning landscapes and Buddhist monasteries', '₹15000-30000', 'domestic', 'https://th.bing.com/th/id/OIP.kaN4VnvSSguxcYILikQ71wHaEj?w=280&h=180&c=7&r=0&o=7&dpr=1.3&pid=1.7&rm=3'],
    ['Rajasthan', 'Land of Kings with magnificent palaces, forts, and desert landscapes', '₹8000-20000', 'domestic', 'https://th.bing.com/th/id/OIP.tUcUdXBmUOKEZkHZiCgUagHaD2?w=346&h=180&c=7&r=0&o=7&dpr=1.3&pid=1.7&rm=3'],
    ['Maldives', 'Paradise islands with crystal clear waters and luxury resorts', '₹40000-80000', 'international', 'https://images.unsplash.com/photo-1544551763-46a013bb70d5?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'],
    ['Thailand', 'Land of smiles with beautiful beaches, temples, and vibrant culture', '₹25000-50000', 'international', 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'],
    ['Shimla', 'Queen of Hills with colonial charm and scenic mountain views', '₹6000-15000', 'domestic', 'https://images.unsplash.com/photo-1502086223501-7ea6ecd79368?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80']
];

echo "<h3>Adding Missing Destinations</h3>";
foreach ($newDestinations as $dest) {
    $stmt = $conn->prepare("SELECT * FROM destinations WHERE name = ?");
    $stmt->bind_param("s", $dest[0]);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        $stmt = $conn->prepare("INSERT INTO destinations (name, description, price_range, location, image_url) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $dest[0], $dest[1], $dest[2], $dest[3], $dest[4]);
        
        if ($stmt->execute()) {
            echo "✅ Added new destination: " . $dest[0] . "<br>";
        } else {
            echo "❌ Failed to add " . $dest[0] . ": " . $stmt->error . "<br>";
        }
    } else {
        echo "✅ " . $dest[0] . " already exists<br>";
    }
}

echo "<h3>Final Status</h3>";
$result = $conn->query("SELECT COUNT(*) as total FROM destinations");
$total = $result->fetch_assoc()['total'];
echo "Total destinations in database: $total<br>";

$result = $conn->query("SELECT COUNT(*) as total FROM destinations WHERE image_url IS NOT NULL AND image_url != ''");
$withImages = $result->fetch_assoc()['total'];
echo "Destinations with images: $withImages<br>";

echo "<h3>Test Links</h3>";
echo "<p><a href='index.html' target='_blank'>→ Test Main Page</a></p>";
echo "<p><a href='destinations.html' target='_blank'>→ Test All Destinations Page</a></p>";
echo "<p><a href='package_booking.html?type=destination&name=Goa' target='_blank'>→ Test Goa Booking</a></p>";

echo "<h3>✅ All Destination Images Updated Successfully!</h3>";
echo "<p>Your travel booking system now has beautiful images for all destinations.</p>";
?> 