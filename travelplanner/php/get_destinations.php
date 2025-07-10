<?php
// php/get_destinations.php - API to fetch destinations
require_once 'config.php';

header('Content-Type: application/json');

// Get query parameters
$category = $_GET['category'] ?? '';
$search = $_GET['search'] ?? '';
$limit = $_GET['limit'] ?? 20;
$offset = $_GET['offset'] ?? 0;

try {
    // Build query
    $whereConditions = [];
    $params = [];
    $types = '';
    
    if (!empty($category) && $category !== 'all') {
        $whereConditions[] = "category = ?";
        $params[] = $category;
        $types .= 's';
    }
    
    if (!empty($search)) {
        $whereConditions[] = "(name LIKE ? OR location LIKE ? OR description LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= 'sss';
    }
    
    $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
    
    // Get total count
    $countQuery = "SELECT COUNT(*) as total FROM destinations $whereClause";
    $countStmt = $conn->prepare($countQuery);
    
    if (!empty($params)) {
        $countStmt->bind_param($types, ...$params);
    }
    
    $countStmt->execute();
    $totalResult = $countStmt->get_result()->fetch_assoc();
    $total = $totalResult['total'];
    
    // Determine ordering - use display_order for main page (limit=6), otherwise by name
    $orderBy = ($limit == 6) ? 'ORDER BY display_order ASC, name ASC' : 'ORDER BY name ASC';
    
    // Get destinations
    $query = "SELECT * FROM destinations $whereClause $orderBy LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($query);
    
    $params[] = (int)$limit;
    $params[] = (int)$offset;
    $types .= 'ii';
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $destinations = [];
    while ($row = $result->fetch_assoc()) {
        // Convert JSON strings to arrays with proper error handling
        $row['features'] = parseJsonField($row['features']);
        $row['local_sights'] = parseJsonField($row['local_sights']);
        $row['popular_places'] = parseJsonField($row['popular_places']);
        $row['highlights'] = parseJsonField($row['highlights']);
        
        // Ensure all fields have default values if they're null
        $row['category'] = $row['category'] ?? 'destination';
        $row['location'] = $row['location'] ?? 'India';
        $row['description'] = $row['description'] ?? 'Amazing destination with unique experiences.';
        $row['why_visit'] = $row['why_visit'] ?? 'This destination offers unique experiences and beautiful landscapes.';
        $row['popular_for'] = $row['popular_for'] ?? 'Tourism, sightseeing, and cultural experiences.';
        $row['best_time'] = $row['best_time'] ?? 'October to March';
        $row['duration'] = $row['duration'] ?? '5-7 days';
        $row['difficulty'] = $row['difficulty'] ?? 'Easy';
        $row['price_range'] = $row['price_range'] ?? 'From ₹15,000';
        
        $destinations[] = $row;
    }
    
    echo json_encode([
        'status' => 'success',
        'data' => $destinations,
        'total' => $total,
        'limit' => (int)$limit,
        'offset' => (int)$offset
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to fetch destinations: ' . $e->getMessage()
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