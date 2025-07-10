<?php
// php/get_package.php - API to fetch package details
require_once 'config.php';

header('Content-Type: application/json');

try {
    $name = $_GET['name'] ?? '';
    
    if (empty($name)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Package name is required'
        ]);
        exit;
    }
    
    // Create package information based on the name
    $packageInfo = [
        'name' => $name,
        'type' => 'domestic', // Default to domestic
        'days' => 5,
        'description' => 'Amazing package with unique experiences and beautiful destinations.',
        'price' => 25000 + rand(5000, 20000),
        'includes' => [
            'Hotel accommodation',
            'Daily breakfast',
            'Local transportation',
            'Sightseeing tours',
            'Professional guide'
        ],
        'excludes' => [
            'Airfare',
            'Personal expenses',
            'Optional activities',
            'Travel insurance'
        ]
    ];
    
    // Set international packages
    $internationalPackages = ['Dubai', 'Thailand', 'Maldives', 'Singapore', 'Bali', 'Paris'];
    if (in_array($name, $internationalPackages)) {
        $packageInfo['type'] = 'international';
        $packageInfo['price'] = 35000 + rand(10000, 30000);
        $packageInfo['days'] = 6;
    }
    
    // Set specific package details
    switch ($name) {
        case 'Goa Beach Getaway':
            $packageInfo['days'] = 5;
            $packageInfo['price'] = 18999;
            $packageInfo['description'] = '5 days, 4 nights. Explore pristine beaches, Portuguese heritage, and vibrant nightlife.';
            break;
            
        case 'Dubai Luxury Tour':
            $packageInfo['type'] = 'international';
            $packageInfo['days'] = 4;
            $packageInfo['price'] = 37999;
            $packageInfo['description'] = '4 days, 3 nights. Burj Khalifa, desert safari, and city tour.';
            break;
            
        case 'Kerala Backwaters':
            $packageInfo['days'] = 6;
            $packageInfo['price'] = 22999;
            $packageInfo['description'] = '6 days, 5 nights. Experience the serene backwaters, Ayurveda, and traditional culture.';
            break;
            
        case 'Ladakh Adventure':
            $packageInfo['days'] = 7;
            $packageInfo['price'] = 28999;
            $packageInfo['description'] = '7 days, 6 nights. High-altitude adventure with monasteries and stunning landscapes.';
            break;
            
        case 'Rajasthan Heritage':
            $packageInfo['days'] = 8;
            $packageInfo['price'] = 25999;
            $packageInfo['description'] = '8 days, 7 nights. Explore royal palaces, forts, and desert culture.';
            break;
            
        case 'Maldives Paradise':
            $packageInfo['type'] = 'international';
            $packageInfo['days'] = 5;
            $packageInfo['price'] = 45999;
            $packageInfo['description'] = '5 days, 4 nights. Crystal clear waters, overwater villas, and pristine beaches.';
            break;
            
        case 'Thailand Discovery':
            $packageInfo['type'] = 'international';
            $packageInfo['days'] = 6;
            $packageInfo['price'] = 32999;
            $packageInfo['description'] = '6 days, 5 nights. Temples, beaches, and vibrant street life.';
            break;
            
        case 'Singapore City Tour':
            $packageInfo['type'] = 'international';
            $packageInfo['days'] = 4;
            $packageInfo['price'] = 34999;
            $packageInfo['description'] = '4 days, 3 nights. Modern city-state with stunning architecture and diverse cuisine.';
            break;
            
        case 'Bali Tropical Escape':
            $packageInfo['type'] = 'international';
            $packageInfo['days'] = 6;
            $packageInfo['price'] = 28999;
            $packageInfo['description'] = '6 days, 5 nights. Tropical paradise with beautiful beaches and rich culture.';
            break;
            
        case 'Paris Romantic Getaway':
            $packageInfo['type'] = 'international';
            $packageInfo['days'] = 7;
            $packageInfo['price'] = 45999;
            $packageInfo['description'] = '7 days, 6 nights. City of love with iconic landmarks and exquisite cuisine.';
            break;
            
        default:
            // Use default values
            break;
    }
    
    echo json_encode([
        'status' => 'success',
        'data' => $packageInfo
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to fetch package information: ' . $e->getMessage()
    ]);
}

// Helper function to parse JSON fields safely
function parseJsonField($field) {
    if (empty($field)) {
        return [];
    }
    
    // Try to decode as JSON first
    $decoded = json_decode($field, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        return $decoded;
    }
    
    // If JSON decode fails, try to split by comma
    if (strpos($field, ',') !== false) {
        return array_map('trim', explode(',', $field));
    }
    
    // If it's a single value, return as array
    return [$field];
}
?> 