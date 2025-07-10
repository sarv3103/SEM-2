<?php
require_once 'php/config.php';

// Check if image_url column exists
$result = $conn->query("SHOW COLUMNS FROM destinations LIKE 'image_url'");
if ($result && $result->num_rows > 0) {
    echo "✅ image_url column already exists in destinations table.<br>";
} else {
    echo "Adding image_url column to destinations table...<br>";
    $sql = "ALTER TABLE destinations ADD COLUMN image_url VARCHAR(255) DEFAULT NULL AFTER location";
    if ($conn->query($sql)) {
        echo "✅ image_url column added successfully.<br>";
    } else {
        echo "❌ Failed to add image_url column: " . $conn->error . "<br>";
    }
}
?> 