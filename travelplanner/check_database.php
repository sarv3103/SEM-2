<?php
// check_database.php - Verify database setup
require_once 'php/config.php';

echo "<h2>🔍 Database Verification Report</h2>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: #28a745; font-weight: bold; }
    .error { color: #dc3545; font-weight: bold; }
    .warning { color: #ffc107; font-weight: bold; }
    table { border-collapse: collapse; width: 100%; margin: 10px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
</style>";

// Test database connection
echo "<h3>1. Database Connection</h3>";
if ($conn->ping()) {
    echo "<p class='success'>✅ Database connection successful</p>";
} else {
    echo "<p class='error'>❌ Database connection failed</p>";
    exit;
}

// Check if database exists
echo "<h3>2. Database Check</h3>";
$result = $conn->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = 'travelplanner'");
if ($result->num_rows > 0) {
    echo "<p class='success'>✅ Database 'travelplanner' exists</p>";
} else {
    echo "<p class='error'>❌ Database 'travelplanner' does not exist</p>";
    echo "<p><a href='setup.php'>Click here to run setup</a></p>";
    exit;
}

// Check required tables
echo "<h3>3. Tables Check</h3>";
$required_tables = [
    'users' => 'User accounts and authentication',
    'bookings' => 'Travel bookings and tickets',
    'plans' => 'Trip plans and itineraries',
    'contact_messages' => 'Contact form submissions',
    'packages' => 'Tour packages data',
    'destinations' => 'Destination information'
];

echo "<table>";
echo "<tr><th>Table Name</th><th>Description</th><th>Status</th><th>Records</th></tr>";

foreach ($required_tables as $table => $description) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result->num_rows > 0) {
        $count_result = $conn->query("SELECT COUNT(*) as count FROM $table");
        $count = $count_result->fetch_assoc()['count'];
        echo "<tr>";
        echo "<td>$table</td>";
        echo "<td>$description</td>";
        echo "<td class='success'>✅ Exists</td>";
        echo "<td>$count records</td>";
        echo "</tr>";
    } else {
        echo "<tr>";
        echo "<td>$table</td>";
        echo "<td>$description</td>";
        echo "<td class='error'>❌ Missing</td>";
        echo "<td>-</td>";
        echo "</tr>";
    }
}
echo "</table>";

// Check admin user
echo "<h3>4. Admin User Check</h3>";
$result = $conn->query("SELECT id, username, email, is_admin FROM users WHERE username = 'admin'");
if ($result->num_rows > 0) {
    $admin = $result->fetch_assoc();
    echo "<p class='success'>✅ Admin user exists</p>";
    echo "<ul>";
    echo "<li><strong>Username:</strong> " . $admin['username'] . "</li>";
    echo "<li><strong>Email:</strong> " . $admin['email'] . "</li>";
    echo "<li><strong>Admin Status:</strong> " . ($admin['is_admin'] ? 'Yes' : 'No') . "</li>";
    echo "</ul>";
} else {
    echo "<p class='error'>❌ Admin user missing</p>";
    echo "<p><a href='setup.php'>Click here to create admin user</a></p>";
}

// Check test user
echo "<h3>5. Test User Check</h3>";
$result = $conn->query("SELECT id, username, email FROM users WHERE username = 'test'");
if ($result->num_rows > 0) {
    $test_user = $result->fetch_assoc();
    echo "<p class='success'>✅ Test user exists</p>";
    echo "<ul>";
    echo "<li><strong>Username:</strong> " . $test_user['username'] . "</li>";
    echo "<li><strong>Email:</strong> " . $test_user['email'] . "</li>";
    echo "</ul>";
} else {
    echo "<p class='warning'>⚠️ Test user missing (optional)</p>";
}

// Check sample data
echo "<h3>6. Sample Data Check</h3>";
$tables_with_data = [
    'packages' => 'Tour packages',
    'destinations' => 'Destinations'
];

foreach ($tables_with_data as $table => $description) {
    $result = $conn->query("SELECT COUNT(*) as count FROM $table");
    $count = $result->fetch_assoc()['count'];
    if ($count > 0) {
        echo "<p class='success'>✅ $description: $count records</p>";
    } else {
        echo "<p class='warning'>⚠️ $description: No data found</p>";
    }
}

// Check PDF temp directory
echo "<h3>7. PDF Generation Check</h3>";
$temp_dir = __DIR__ . '/vendor/mpdf/mpdf/tmp';
if (is_dir($temp_dir)) {
    if (is_writable($temp_dir)) {
        echo "<p class='success'>✅ PDF temp directory exists and is writable</p>";
    } else {
        echo "<p class='error'>❌ PDF temp directory exists but is not writable</p>";
    }
} else {
    echo "<p class='error'>❌ PDF temp directory missing</p>";
    echo "<p>Creating directory...</p>";
    if (mkdir($temp_dir, 0777, true)) {
        echo "<p class='success'>✅ PDF temp directory created</p>";
    } else {
        echo "<p class='error'>❌ Failed to create PDF temp directory</p>";
    }
}

// Summary
echo "<h3>8. Summary</h3>";
echo "<p><strong>Database Status:</strong> ";
if ($conn->ping()) {
    echo "<span class='success'>Ready</span>";
} else {
    echo "<span class='error'>Not Ready</span>";
}
echo "</p>";

echo "<p><strong>Next Steps:</strong></p>";
echo "<ul>";
echo "<li><a href='test_mpdf.php'>Test PDF Generation</a></li>";
echo "<li><a href='login.html'>Test Login</a></li>";
echo "<li><a href='index.html'>Go to Main Application</a></li>";
echo "</ul>";

$conn->close();

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== CHECKING DESTINATIONS TABLE ===\n\n";
    
    // Check if table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'destinations'");
    if ($stmt->rowCount() == 0) {
        echo "❌ Destinations table does not exist!\n";
        exit;
    }
    
    // Count total destinations
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM destinations");
    $total = $stmt->fetchColumn();
    echo "Total destinations in database: $total\n\n";
    
    // List all destinations
    $stmt = $pdo->query("SELECT name, location FROM destinations ORDER BY name");
    $destinations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($destinations)) {
        echo "❌ No destinations found in database!\n";
    } else {
        echo "Destinations in database:\n";
        foreach ($destinations as $dest) {
            echo "- {$dest['name']} ({$dest['location']})\n";
        }
    }
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
}
?> 