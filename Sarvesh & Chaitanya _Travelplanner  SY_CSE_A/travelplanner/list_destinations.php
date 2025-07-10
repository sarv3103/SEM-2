<?php
require_once 'php/config.php';

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $pdo->query("SELECT name FROM destinations");
    $destinations = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Destination names in the database:\n";
    foreach ($destinations as $name) {
        echo "- $name\n";
    }
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}
?> 