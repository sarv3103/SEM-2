<?php
require_once 'php/config.php';

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== VERIFYING DESTINATION INFORMATION ===\n\n";
    
    // Check the destinations that were supposed to be updated
    $destinations_to_check = ['Mumbai', 'Delhi', 'Bangalore', 'Chennai', 'Ahmedabad'];
    
    foreach ($destinations_to_check as $dest_name) {
        $stmt = $pdo->prepare("SELECT name, why_visit, popular_for, local_sights, popular_places, highlights, features FROM destinations WHERE name = ?");
        $stmt->execute([$dest_name]);
        $dest = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($dest) {
            echo "📍 $dest_name:\n";
            echo "   Why Visit: " . (strlen($dest['why_visit']) > 50 ? '✅ Updated' : '❌ Default') . "\n";
            echo "   Popular For: " . (strlen($dest['popular_for']) > 30 ? '✅ Updated' : '❌ Default') . "\n";
            echo "   Local Sights: " . ($dest['local_sights'] && $dest['local_sights'] != 'Information not available' ? '✅ Updated' : '❌ Default') . "\n";
            echo "   Popular Places: " . ($dest['popular_places'] && $dest['popular_places'] != 'Information not available' ? '✅ Updated' : '❌ Default') . "\n";
            echo "   Highlights: " . ($dest['highlights'] && $dest['highlights'] != 'Information not available' ? '✅ Updated' : '❌ Default') . "\n";
            echo "   Features: " . ($dest['features'] && $dest['features'] != 'Information not available' ? '✅ Updated' : '❌ Default') . "\n";
            echo "\n";
        } else {
            echo "❌ Destination '$dest_name' not found in database\n\n";
        }
    }
    
    // Also check if there are any destinations still with default information
    $stmt = $pdo->query("SELECT name FROM destinations WHERE 
        why_visit = 'This destination offers unique experiences and beautiful landscapes.' OR
        popular_for = 'Tourism, sightseeing, and cultural experiences.' OR
        local_sights = 'Information not available' OR
        popular_places = 'Information not available' OR
        highlights = 'Information not available' OR
        features = 'Information not available'");
    
    $still_missing = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($still_missing)) {
        echo "✅ All destinations now have complete information!\n";
    } else {
        echo "❌ Destinations still with default information:\n";
        foreach ($still_missing as $dest) {
            echo "   - $dest\n";
        }
    }
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
}
?> 