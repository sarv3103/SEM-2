<?php
// test_destinations_system.php - Test the destinations system
require_once 'php/config.php';

echo "<h2>🧪 Testing Destinations System</h2>";

// Test 1: Check destinations table
echo "<h3>1. Destinations Table Check:</h3>";
$result = $conn->query("SELECT COUNT(*) as count FROM destinations");
if ($result) {
    $count = $result->fetch_assoc()['count'];
    echo "✅ Destinations table exists with $count records<br>";
} else {
    echo "❌ Destinations table not found<br>";
}

// Test 2: Test get_destinations.php API
echo "<h3>2. Testing get_destinations.php API:</h3>";
$url = "http://localhost/travelplanner/php/get_destinations.php";
$response = file_get_contents($url);

if ($response !== false) {
    $data = json_decode($response, true);
    if ($data && isset($data['status'])) {
        if ($data['status'] === 'success') {
            echo "✅ API working correctly<br>";
            echo "📊 Found " . count($data['data']) . " destinations<br>";
            echo "📈 Total destinations: " . $data['total'] . "<br>";
        } else {
            echo "❌ API returned error: " . $data['message'] . "<br>";
        }
    } else {
        echo "❌ Invalid JSON response from API<br>";
    }
} else {
    echo "❌ Could not connect to API<br>";
}

// Test 3: Test get_destination.php API
echo "<h3>3. Testing get_destination.php API:</h3>";
$url = "http://localhost/travelplanner/php/get_destination.php?id=1";
$response = file_get_contents($url);

if ($response !== false) {
    $data = json_decode($response, true);
    if ($data && isset($data['status'])) {
        if ($data['status'] === 'success') {
            echo "✅ Single destination API working correctly<br>";
            echo "📍 Destination: " . $data['data']['name'] . "<br>";
            echo "🏷️ Category: " . $data['data']['category'] . "<br>";
            echo "💰 Price: " . $data['data']['price'] . "<br>";
        } else {
            echo "❌ Single destination API returned error: " . $data['message'] . "<br>";
        }
    } else {
        echo "❌ Invalid JSON response from single destination API<br>";
    }
} else {
    echo "❌ Could not connect to single destination API<br>";
}

// Test 4: Test category filtering
echo "<h3>4. Testing Category Filtering:</h3>";
$url = "http://localhost/travelplanner/php/get_destinations.php?category=beach";
$response = file_get_contents($url);

if ($response !== false) {
    $data = json_decode($response, true);
    if ($data && isset($data['status']) && $data['status'] === 'success') {
        echo "✅ Category filtering working correctly<br>";
        echo "🏖️ Found " . count($data['data']) . " beach destinations<br>";
    } else {
        echo "❌ Category filtering failed<br>";
    }
} else {
    echo "❌ Could not test category filtering<br>";
}

// Test 5: Test search functionality
echo "<h3>5. Testing Search Functionality:</h3>";
$url = "http://localhost/travelplanner/php/get_destinations.php?search=goa";
$response = file_get_contents($url);

if ($response !== false) {
    $data = json_decode($response, true);
    if ($data && isset($data['status']) && $data['status'] === 'success') {
        echo "✅ Search functionality working correctly<br>";
        echo "🔍 Found " . count($data['data']) . " destinations matching 'goa'<br>";
    } else {
        echo "❌ Search functionality failed<br>";
    }
} else {
    echo "❌ Could not test search functionality<br>";
}

// Test 6: Sample destinations data
echo "<h3>6. Sample Destinations Data:</h3>";
$result = $conn->query("SELECT id, name, category, price FROM destinations LIMIT 5");
if ($result && $result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Category</th><th>Price</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['name']}</td>";
        echo "<td>{$row['category']}</td>";
        echo "<td>{$row['price']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "❌ No destinations found in database<br>";
}

echo "<h3>🎯 Test Summary:</h3>";
echo "The destinations system should now be fully functional with:<br>";
echo "• Database-driven destinations<br>";
echo "• API endpoints for fetching destinations<br>";
echo "• Category filtering<br>";
echo "• Search functionality<br>";
echo "• Detailed destination information<br>";
echo "• Integration with booking system<br>";

echo "<h3>📝 Next Steps:</h3>";
echo "1. <a href='destinations.html'>Test Destinations Page</a><br>";
echo "2. <a href='index.html#destinations'>Test Main Page Destinations</a><br>";
echo "3. <a href='booking.html'>Test Booking Integration</a><br>";
echo "4. <a href='admin_dashboard.php'>Check Admin Dashboard</a><br>";
?> 