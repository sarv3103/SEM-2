<?php
// php/get_destination.php - API to fetch destination details
require_once 'config.php';

header('Content-Type: application/json');

// Get query parameters
$id = $_GET['id'] ?? '';
$name = $_GET['name'] ?? '';

if (empty($id) && empty($name)) {
    echo json_encode(['status' => 'error', 'message' => 'Please provide either id or name parameter']);
    exit();
}

try {
    $stmt = null;
    
    if (!empty($id)) {
        $stmt = $conn->prepare("SELECT * FROM destinations WHERE id = ?");
        $stmt->bind_param("i", $id);
    } else {
        $stmt = $conn->prepare("SELECT * FROM destinations WHERE name = ?");
        $stmt->bind_param("s", $name);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $destination = $result->fetch_assoc();
    
    if (!$destination) {
        echo json_encode(['status' => 'error', 'message' => 'Destination not found']);
        exit();
    }
    
    // Parse JSON fields
    $destination['features'] = parseJsonField($destination['features']);
    $destination['local_sights'] = parseJsonField($destination['local_sights']);
    $destination['popular_places'] = parseJsonField($destination['popular_places']);
    $destination['highlights'] = parseJsonField($destination['highlights']);
    
    // Ensure all fields have default values
    $destination['category'] = $destination['category'] ?? 'destination';
    $destination['location'] = $destination['location'] ?? 'India';
    $destination['description'] = $destination['description'] ?? 'Amazing destination with unique experiences.';
    $destination['why_visit'] = $destination['why_visit'] ?? 'This destination offers unique experiences and beautiful landscapes.';
    $destination['popular_for'] = $destination['popular_for'] ?? 'Tourism, sightseeing, and cultural experiences.';
    $destination['best_time'] = $destination['best_time'] ?? 'October to March';
    $destination['duration'] = $destination['duration'] ?? '5-7 days';
    $destination['difficulty'] = $destination['difficulty'] ?? 'Easy';
    $destination['price_range'] = $destination['price_range'] ?? 'From ₹15,000';
    
    echo json_encode([
        'status' => 'success',
        'data' => $destination
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to fetch destination: ' . $e->getMessage()
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