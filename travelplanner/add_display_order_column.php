<?php
require_once 'php/config.php';

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if display_order column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM destinations LIKE 'display_order'");
    $column_exists = $stmt->rowCount() > 0;
    
    if (!$column_exists) {
        // Add display_order column
        $pdo->exec("ALTER TABLE destinations ADD COLUMN display_order INT DEFAULT 999");
        echo "✅ Added display_order column to destinations table\n";
    } else {
        echo "ℹ️ display_order column already exists\n";
    }
    
    // Now arrange the packages
    $main_packages = [
        'Goa',           // Domestic - Beach destination
        'Kerala',        // Domestic - Nature/Culture
        'Maldives',      // International - Luxury beach
        'Thailand',      // International - Popular destination
        'Dubai',         // International - Modern city
        'Rajasthan'      // Domestic - Heritage/Culture
    ];
    
    echo "\nUpdating main page packages...\n\n";
    
    foreach ($main_packages as $index => $destination) {
        $display_order = $index + 1; // 1-6 for main page
        
        $stmt = $pdo->prepare("UPDATE destinations SET display_order = ? WHERE name = ?");
        $stmt->execute([$display_order, $destination]);
        
        if ($stmt->rowCount() > 0) {
            echo "✅ Set $destination as package #$display_order\n";
        } else {
            echo "❌ Destination '$destination' not found\n";
        }
    }
    
    // Set other destinations to higher display order (won't show on main page)
    $placeholders = str_repeat('?,', count($main_packages) - 1) . '?';
    $stmt = $pdo->prepare("UPDATE destinations SET display_order = 999 WHERE name NOT IN ($placeholders)");
    $stmt->execute($main_packages);
    
    echo "\n✅ Main page packages arranged successfully!\n";
    echo "The 6 packages will now show in this order:\n";
    foreach ($main_packages as $index => $destination) {
        echo ($index + 1) . ". $destination\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
}
?> 