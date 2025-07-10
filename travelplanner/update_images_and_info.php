<?php
require_once 'php/config.php';

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Update Thailand image
    $thailand_image_url = 'https://th.bing.com/th/id/OIP.ZnddV6vHCb6xS2_o73GemAHaE7?w=245&h=180&c=7&r=0&o=7&dpr=1.3&pid=1.7&rm=';
    $stmt1 = $pdo->prepare("UPDATE destinations SET image_url = ? WHERE name = 'Thailand'");
    $stmt1->execute([$thailand_image_url]);
    if ($stmt1->rowCount() > 0) {
        echo "✅ Thailand image updated successfully!\n";
    } else {
        echo "❌ Thailand destination not found\n";
    }

    // Update Varanasi image
    $varanasi_image_url = 'https://th.bing.com/th/id/OIP.DmUuCIPQLurxIWCFiHDLjwHaEK?w=294&h=180&c=7&r=0&o=7&dpr=1.3&pid=1.7&rm=';
    $stmt2 = $pdo->prepare("UPDATE destinations SET image_url = ? WHERE name = 'Varanasi'");
    $stmt2->execute([$varanasi_image_url]);
    if ($stmt2->rowCount() > 0) {
        echo "✅ Varanasi image updated successfully!\n";
    } else {
        echo "❌ Varanasi destination not found\n";
    }

    echo "\n=== CHECKING DESTINATIONS WITH MISSING INFORMATION ===\n";
    
    // Check which destinations have missing information
    $stmt = $pdo->query("SELECT name, why_visit, popular_for, local_sights, popular_places, highlights, features FROM destinations");
    $destinations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $missing_info = [];
    foreach ($destinations as $dest) {
        $missing = [];
        if (empty($dest['why_visit']) || $dest['why_visit'] == 'This destination offers unique experiences and beautiful landscapes.') {
            $missing[] = 'why_visit';
        }
        if (empty($dest['popular_for']) || $dest['popular_for'] == 'Tourism, sightseeing, and cultural experiences.') {
            $missing[] = 'popular_for';
        }
        if (empty($dest['local_sights']) || $dest['local_sights'] == 'Information not available') {
            $missing[] = 'local_sights';
        }
        if (empty($dest['popular_places']) || $dest['popular_places'] == 'Information not available') {
            $missing[] = 'popular_places';
        }
        if (empty($dest['highlights']) || $dest['highlights'] == 'Information not available') {
            $missing[] = 'highlights';
        }
        if (empty($dest['features']) || $dest['features'] == 'Information not available') {
            $missing[] = 'features';
        }
        
        if (!empty($missing)) {
            $missing_info[$dest['name']] = $missing;
        }
    }
    
    if (empty($missing_info)) {
        echo "✅ All destinations have complete information!\n";
    } else {
        echo "❌ Destinations with missing information:\n";
        foreach ($missing_info as $dest => $missing) {
            echo "📍 $dest: " . implode(', ', $missing) . "\n";
        }
    }
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
}
?> 