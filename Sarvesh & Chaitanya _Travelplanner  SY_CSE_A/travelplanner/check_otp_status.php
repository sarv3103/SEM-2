<?php
// Check OTP status and timezone
require_once 'php/config.php';

// Set timezone
date_default_timezone_set('Asia/Kolkata');

echo "<h2>OTP Status Check</h2>";

echo "<h3>Current Time Information:</h3>";
echo "Current Time: " . date('Y-m-d H:i:s') . "<br>";
echo "Timezone: " . date_default_timezone_get() . "<br>";
echo "Server Time: " . date('Y-m-d H:i:s', time()) . "<br>";

echo "<h3>Recent OTP Records:</h3>";
$result = $conn->query("
    SELECT email, otp, created_at, expiry, used, 
           TIMESTAMPDIFF(MINUTE, NOW(), expiry) as minutes_left
    FROM otp_logs 
    WHERE type = 'booking_verification' 
    ORDER BY created_at DESC 
    LIMIT 10
");

if ($result && $result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Email</th><th>OTP</th><th>Created</th><th>Expiry</th><th>Used</th><th>Minutes Left</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        $status = $row['used'] ? 'Used' : ($row['minutes_left'] > 0 ? 'Valid' : 'Expired');
        $color = $row['used'] ? 'red' : ($row['minutes_left'] > 0 ? 'green' : 'orange');
        
        echo "<tr>";
        echo "<td>" . substr($row['email'], 0, 20) . "...</td>";
        echo "<td>" . $row['otp'] . "</td>";
        echo "<td>" . $row['created_at'] . "</td>";
        echo "<td>" . $row['expiry'] . "</td>";
        echo "<td>" . $row['used'] . "</td>";
        echo "<td style='color: $color;'>" . $row['minutes_left'] . " ($status)</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "No OTP records found.";
}

echo "<h3>Database Time Check:</h3>";
$timeResult = $conn->query("SELECT NOW() as db_time, UTC_TIMESTAMP() as utc_time");
if ($timeResult) {
    $timeRow = $timeResult->fetch_assoc();
    echo "Database Time: " . $timeRow['db_time'] . "<br>";
    echo "UTC Time: " . $timeRow['utc_time'] . "<br>";
}
?> 