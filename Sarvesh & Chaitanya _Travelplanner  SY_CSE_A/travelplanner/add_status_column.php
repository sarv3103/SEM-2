<?php
require_once 'php/config.php';

// Add status column to users table
$sql = "ALTER TABLE users ADD COLUMN IF NOT EXISTS `status` enum('pending','active','inactive','suspended') NOT NULL DEFAULT 'active' AFTER `mobile_verified`";

if ($conn->query($sql)) {
    echo "✅ Status column added to users table\n";
} else {
    echo "❌ Error adding status column: " . $conn->error . "\n";
}

// Check current table structure
$result = $conn->query("DESCRIBE users");
echo "\nCurrent users table structure:\n";
while ($row = $result->fetch_assoc()) {
    echo "- {$row['Field']}: {$row['Type']}\n";
}
?> 