<?php
require_once 'php/config.php';

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Debug: Print how many rows match before update
    $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM destinations WHERE TRIM(LOWER(name)) = TRIM(LOWER(?))");
    $stmt_check->execute(['Dubai']);
    $count_dubai = $stmt_check->fetchColumn();
    echo "Dubai matches before update: $count_dubai\n";

    // Update Dubai image with the new link (case-insensitive, trimmed)
    $dubai_image_url = 'https://www.gibsons.co.uk/wp-content/uploads/2023/12/Dubai-400x500.jpg';
    $stmt1 = $pdo->prepare("UPDATE destinations SET image_url = ? WHERE TRIM(LOWER(name)) = TRIM(LOWER(?))");
    $stmt1->execute([$dubai_image_url, 'Dubai']);
    if ($stmt1->rowCount() > 0) {
        echo "✅ Dubai image updated successfully!\n";
    } else {
        echo "❌ Dubai destination not found in database\n";
    }

    // Debug: Print how many rows match before update for Thailand
    $stmt_check2 = $pdo->prepare("SELECT COUNT(*) FROM destinations WHERE TRIM(LOWER(name)) = TRIM(LOWER(?))");
    $stmt_check2->execute(['Thailand']);
    $count_thailand = $stmt_check2->fetchColumn();
    echo "Thailand matches before update: $count_thailand\n";

    // Update Thailand image (case-insensitive, trimmed)
    $thailand_image_url = 'https://images.unsplash.com/photo-1509228468518-180dd4864904?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80';
    $stmt2 = $pdo->prepare("UPDATE destinations SET image_url = ? WHERE TRIM(LOWER(name)) = TRIM(LOWER(?))");
    $stmt2->execute([$thailand_image_url, 'Thailand']);
    if ($stmt2->rowCount() > 0) {
        echo "✅ Thailand image updated successfully!\n";
    } else {
        echo "❌ Thailand destination not found in database\n";
    }

    // Debug: Print how many rows match before update for Varanasi
    $stmt_check3 = $pdo->prepare("SELECT COUNT(*) FROM destinations WHERE TRIM(LOWER(name)) = TRIM(LOWER(?))");
    $stmt_check3->execute(['Varanasi']);
    $count_varanasi = $stmt_check3->fetchColumn();
    echo "Varanasi matches before update: $count_varanasi\n";

    // Update Varanasi image (case-insensitive, trimmed)
    $varanasi_image_url = 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80';
    $stmt3 = $pdo->prepare("UPDATE destinations SET image_url = ? WHERE TRIM(LOWER(name)) = TRIM(LOWER(?))");
    $stmt3->execute([$varanasi_image_url, 'Varanasi']);
    if ($stmt3->rowCount() > 0) {
        echo "✅ Varanasi image updated successfully!\n";
    } else {
        echo "❌ Varanasi destination not found in database\n";
    }

} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
}
?> 