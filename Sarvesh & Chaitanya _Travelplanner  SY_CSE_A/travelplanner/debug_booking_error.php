<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "Testing booking process...\n";

// Test 1: Check if config.php loads
try {
    require_once 'php/config.php';
    echo "✓ Config loaded successfully\n";
} catch (Exception $e) {
    echo "✗ Config error: " . $e->getMessage() . "\n";
    exit;
}

// Test 2: Check database connection
try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    echo "✓ Database connected successfully\n";
} catch (Exception $e) {
    echo "✗ Database error: " . $e->getMessage() . "\n";
    exit;
}

// Test 3: Check if user_bookings directory is writable
if (is_writable('user_bookings')) {
    echo "✓ user_bookings directory is writable\n";
} else {
    echo "✗ user_bookings directory is not writable\n";
}

// Test 4: Check session
session_start();
if (isset($_SESSION['user_id'])) {
    echo "✓ User is logged in (ID: " . $_SESSION['user_id'] . ")\n";
} else {
    echo "✗ User is not logged in\n";
}

// Test 5: Check bookings table structure
$result = $conn->query("DESCRIBE bookings");
if ($result) {
    echo "✓ Bookings table structure:\n";
    while ($row = $result->fetch_assoc()) {
        echo "  - " . $row['Field'] . " (" . $row['Type'] . ")\n";
    }
} else {
    echo "✗ Error checking bookings table: " . $conn->error . "\n";
}

// Test 6: Check traveler_details table structure
$result = $conn->query("DESCRIBE traveler_details");
if ($result) {
    echo "✓ Traveler_details table structure:\n";
    while ($row = $result->fetch_assoc()) {
        echo "  - " . $row['Field'] . " (" . $row['Type'] . ")\n";
    }
} else {
    echo "✗ Error checking traveler_details table: " . $conn->error . "\n";
}

echo "\nDebug complete.\n";
?> 