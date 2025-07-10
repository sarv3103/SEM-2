<?php
require_once 'config.php';
header('Content-Type: application/json');

function parseJsonField($field) {
    if (empty($field)) return [];
    $decoded = json_decode($field, true);
    if (json_last_error() === JSON_ERROR_NONE) return $decoded;
    if (strpos($field, ',') !== false) return array_map('trim', explode(',', $field));
    return [$field];
}

try {
    // Get 6 domestic destinations
    $domestic = [];
    $stmt = $conn->prepare("SELECT * FROM destinations WHERE (location = 'India' OR location = 'domestic') ORDER BY display_order ASC, name ASC LIMIT 6");
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $domestic[] = [
            'name' => $row['name'],
            'img' => $row['image_url'],
            'meta' => $row['duration'] . '<br>Location: ' . $row['location'] . '<br>Best Time: ' . $row['best_time'] . '<br>Difficulty: ' . $row['difficulty'],
            'price' => 15000 + rand(5000, 20000), // Generate random price
            'desc' => $row['description'] ?: 'Amazing destination with unique experiences.'
        ];
    }
    
    // Get 6 international destinations - try different location values
    $international = [];
    $stmt = $conn->prepare("SELECT * FROM destinations WHERE (location = 'International' OR location = 'international' OR name IN ('Dubai', 'Thailand', 'Maldives')) ORDER BY display_order ASC, name ASC LIMIT 6");
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $international[] = [
            'name' => $row['name'],
            'img' => $row['image_url'],
            'meta' => $row['duration'] . '<br>Location: ' . $row['location'] . '<br>Best Time: ' . $row['best_time'] . '<br>Difficulty: ' . $row['difficulty'],
            'price' => 25000 + rand(10000, 40000), // Generate random price for international
            'desc' => $row['description'] ?: 'Amazing international destination with unique experiences.'
        ];
    }
    
    // If we don't have 6 international, add some fallback ones
    while (count($international) < 6) {
        $fallback_international = [
            [
                'name' => 'Singapore',
                'img' => 'https://images.unsplash.com/photo-1525625293386-3f8f99389edd?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
                'meta' => '5-7 Days<br>Location: Singapore<br>Best Time: February to April<br>Difficulty: Easy',
                'price' => 35000 + rand(5000, 15000),
                'desc' => 'Modern city-state with stunning architecture, diverse cuisine, and vibrant culture.'
            ],
            [
                'name' => 'Bali',
                'img' => 'https://images.unsplash.com/photo-1537953773345-d172ccf13cf1?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
                'meta' => '6-8 Days<br>Location: Indonesia<br>Best Time: April to October<br>Difficulty: Easy',
                'price' => 28000 + rand(5000, 15000),
                'desc' => 'Tropical paradise with beautiful beaches, temples, and rich cultural heritage.'
            ],
            [
                'name' => 'Paris',
                'img' => 'https://th.bing.com/th/id/OIP.Z97bkX40h0DN2g8WwHpqJwHaLO?w=186&h=281&c=7&r=0&o=7&dpr=1.3&pid=1.7&rm=3',
                'meta' => '7-10 Days<br>Location: France<br>Best Time: April to October<br>Difficulty: Easy',
                'price' => 45000 + rand(10000, 20000),
                'desc' => 'City of love with iconic landmarks, world-class museums, and exquisite cuisine.'
            ]
        ];
        
        foreach ($fallback_international as $fallback) {
            if (count($international) < 6) {
                $international[] = $fallback;
            }
        }
        break; // Prevent infinite loop
    }
    
    echo json_encode([
        'status' => 'success',
        'domestic' => $domestic,
        'international' => $international
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to fetch packages: ' . $e->getMessage()
    ]);
}
?> 