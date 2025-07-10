<?php
require_once 'php/config.php';

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $destinations = ['Dubai', 'Thailand', 'Varanasi'];
    
    foreach ($destinations as $dest) {
        $stmt = $pdo->prepare("SELECT name, image_url FROM destinations WHERE TRIM(LOWER(name)) = TRIM(LOWER(?))");
        $stmt->execute([$dest]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            echo "📍 $dest:\n";
            echo "   Current image: " . ($result['image_url'] ?: 'NULL') . "\n";
        } else {
            echo "❌ $dest: Not found in database\n";
        }
        echo "\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
}
?> 