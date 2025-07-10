<?php
require_once 'php/config.php';

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Define the 6 best packages for the main page
    // Mix of domestic and international destinations
    $main_packages = [
        'Goa',           // Domestic - Beach destination
        'Kerala',        // Domestic - Nature/Culture
        'Maldives',      // International - Luxury beach
        'Thailand',      // International - Popular destination
        'Dubai',         // International - Modern city
        'Rajasthan'      // Domestic - Heritage/Culture
    ];
    
    // Update the display_order for these 6 destinations to show them first
    echo "Updating main page packages...\n\n";
    
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
    $stmt = $pdo->prepare("UPDATE destinations SET display_order = 999 WHERE name NOT IN (" . str_repeat('?,', count($main_packages) - 1) . "?)");
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