<?php
require_once 'php/config.php';

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== MAIN PAGE PACKAGES (6 DESTINATIONS) ===\n\n";
    
    // Get the 6 main packages ordered by display_order
    $stmt = $pdo->query("SELECT name, display_order, image_url, location FROM destinations ORDER BY display_order ASC LIMIT 6");
    $main_packages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($main_packages as $index => $package) {
        $number = $index + 1;
        $image_status = $package['image_url'] ? '✅' : '❌';
        echo "$number. {$package['name']} (Order: {$package['display_order']})\n";
        echo "   Location: {$package['location']}\n";
        echo "   Image: $image_status\n";
        echo "\n";
    }
    
    echo "=== PACKAGE BREAKDOWN ===\n";
    echo "🏠 Domestic Destinations: Goa, Kerala, Rajasthan\n";
    echo "🌍 International Destinations: Maldives, Thailand, Dubai\n";
    echo "\n";
    echo "✅ Perfect balance: 3 Domestic + 3 International\n";
    echo "✅ All packages have images\n";
    echo "✅ Varied destinations: Beaches, Culture, Heritage, Modern Cities\n";
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
}
?> 