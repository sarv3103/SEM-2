<?php
require_once 'php/config.php';

echo "<h2>Updating Destination Images</h2>";

// Destination image mappings
$destinationImages = [
    'Ahmedabad' => 'https://th.bing.com/th/id/OIP.cx61idddBNW7VbcmKXkFjAHaES?w=281&h=180&c=7&r=0&o=7&dpr=1.3&pid=1.7&rm=3',
    'Pune' => 'https://th.bing.com/th/id/OIP.pz7XLuSJmVocTEtcZ_QNjQHaEK?w=297&h=180&c=7&r=0&o=7&dpr=1.3&pid=1.7&rm=3',
    'Kolkata' => 'https://th.bing.com/th/id/OIP.3eMNILTMIy4eum5vLjngLAHaE4?w=308&h=180&c=7&r=0&o=7&dpr=1.3&pid=1.7&rm=3',
    'Hyderabad' => 'https://th.bing.com/th/id/OIP.0Ks-8SP4IQvjFtZ6h3vb2gHaEc?w=313&h=188&c=7&r=0&o=7&dpr=1.3&pid=1.7&rm=3',
    'Chennai' => 'https://th.bing.com/th/id/OIP.tNmNNW8BAQkflxbUvLirQQHaFC?w=266&h=181&c=7&r=0&o=7&dpr=1.3&pid=1.7&rm=3',
    'Bangalore' => 'https://th.bing.com/th/id/OIP.o3xTmhn_Ak49HK-nmsxmCwHaDt?w=333&h=175&c=7&r=0&o=7&dpr=1.3&pid=1.7&rm=3',
    'Delhi' => 'https://th.bing.com/th/id/OIP.nolG_jwRXPmDOY5FxtYKqgHaE8?w=315&h=180&c=7&r=0&o=7&dpr=1.3&pid=1.7&rm=3',
    'Mumbai' => 'https://ts3.mm.bing.net/th?id=OIP.DVFsw4q9-7yUehvBdSWfdgHaEC&pid=15.1'
];

// Update each destination
foreach ($destinationImages as $destinationName => $imageUrl) {
    $stmt = $conn->prepare("UPDATE destinations SET image_url = ? WHERE name = ?");
    $stmt->bind_param("ss", $imageUrl, $destinationName);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo "✅ Updated image for $destinationName<br>";
        } else {
            echo "⚠️ No changes for $destinationName (already has this image or destination not found)<br>";
        }
    } else {
        echo "❌ Failed to update $destinationName: " . $stmt->error . "<br>";
    }
}

// Check if Goa needs an image (you might want to provide one)
echo "<h3>Checking Goa Image</h3>";
$stmt = $conn->prepare("SELECT image_url FROM destinations WHERE name = ?");
$stmt->bind_param("s", "Goa");
$stmt->execute();
$result = $stmt->get_result();
$goa = $result->fetch_assoc();

if ($goa && $goa['image_url']) {
    echo "✅ Goa already has an image: " . $goa['image_url'] . "<br>";
} else {
    echo "⚠️ Goa doesn't have an image yet. You can provide one later.<br>";
}

echo "<h3>All Destination Images Updated!</h3>";
echo "<p>Your destinations now have proper images from Bing.</p>";
echo "<p><a href='index.html'>→ Go to Homepage to see the images</a></p>";
echo "<p><a href='destinations.html'>→ View All Destinations</a></p>";
?> 