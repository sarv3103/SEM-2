<?php
// Check booking table structure
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Database connection
    $conn = new mysqli('localhost', 'root', '', 'travelplanner');
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Get booking ID from URL or use default
    $bookingId = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 66;
    
    // First, let's see the table structure
    echo "<h2>Bookings Table Structure:</h2>";
    $result = $conn->query("DESCRIBE bookings");
    if ($result) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Default']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Extra']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<h2>Sample Booking Data (ID: $bookingId):</h2>";
    $stmt = $conn->prepare("SELECT * FROM bookings WHERE id = ?");
    $stmt->bind_param("i", $bookingId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo "<p>No booking found with ID: $bookingId</p>";
    } else {
        $booking = $result->fetch_assoc();
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Column</th><th>Value</th></tr>";
        foreach ($booking as $column => $value) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($column) . "</td>";
            echo "<td>" . htmlspecialchars($value) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch (Exception $e) {
    echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?> 