<?php
require_once 'php/config.php';

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->query("SELECT name, is_international, image_url FROM destinations ORDER BY is_international, name");
    $destinations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "=== DESTINATIONS BY TYPE ===\n\n";
    
    echo "🏠 DOMESTIC DESTINATIONS:\n";
    echo "------------------------\n";
    foreach ($destinations as $dest) {
        if (!$dest['is_international']) {
            echo "• " . $dest['name'] . " (Image: " . ($dest['image_url'] ? '✅' : '❌') . ")\n";
        }
    }
    
    echo "\n🌍 INTERNATIONAL DESTINATIONS:\n";
    echo "-----------------------------\n";
    foreach ($destinations as $dest) {
        if ($dest['is_international']) {
            echo "• " . $dest['name'] . " (Image: " . ($dest['image_url'] ? '✅' : '❌') . ")\n";
        }
    }
    
    echo "\n=== SUMMARY ===\n";
    $domestic_count = count(array_filter($destinations, function($d) { return !$d['is_international']; }));
    $international_count = count(array_filter($destinations, function($d) { return $d['is_international']; }));
    echo "Total Domestic: $domestic_count\n";
    echo "Total International: $international_count\n";
    echo "Total Destinations: " . count($destinations) . "\n";
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
}
?> 