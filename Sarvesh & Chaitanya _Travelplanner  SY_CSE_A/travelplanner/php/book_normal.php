<?php
// php/book_normal.php - Handle normal booking (without ID fields)
require_once 'config.php';
require_once 'session.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'msg' => 'Invalid request method']);
    exit;
}

// Get form data
$name = trim($_POST['name'] ?? '');
$age = intval($_POST['age'] ?? 0);
$gender = trim($_POST['gender'] ?? '');
$mobile = trim($_POST['mobile'] ?? '');
$source = trim($_POST['source'] ?? '');
$destination = trim($_POST['destination'] ?? '');
$date = $_POST['date'] ?? '';
$num_travelers = intval($_POST['num_travelers'] ?? 0);
$type = trim($_POST['type'] ?? 'domestic');
$transport_mode = trim($_POST['transport_mode'] ?? 'flight');
$special_requirements = trim($_POST['special_requirements'] ?? '');

// Validate required fields
if (empty($name) || empty($gender) || empty($mobile) || empty($source) || empty($destination) || empty($date) || $num_travelers <= 0 || $age <= 0) {
    echo json_encode(['status' => 'error', 'msg' => 'Please fill in all required fields']);
    exit;
}

// Validate mobile number
if (!preg_match('/^[0-9]{10}$/', $mobile)) {
    echo json_encode(['status' => 'error', 'msg' => 'Please enter a valid 10-digit mobile number']);
    exit;
}

// Validate age
if ($age < 1 || $age > 120) {
    echo json_encode(['status' => 'error', 'msg' => 'Please enter a valid age']);
    exit;
}

// Validate date
$travel_date = new DateTime($date);
$today = new DateTime();
if ($travel_date < $today) {
    echo json_encode(['status' => 'error', 'msg' => 'Travel date cannot be in the past']);
    exit;
}

// Calculate fare based on transport mode and type
function calculateFare($transport_mode, $type, $num_travelers) {
    $base_fare = 0;
    
    switch ($transport_mode) {
        case 'flight':
            $base_fare = $type === 'international' ? 8000 : 3000;
            break;
        case 'train':
            $base_fare = 1500;
            break;
        case 'bus':
            $base_fare = 800;
            break;
        case 'car':
            $base_fare = 2000;
            break;
        default:
            $base_fare = 1500;
    }
    
    return $base_fare * $num_travelers;
}

$fare = calculateFare($transport_mode, $type, $num_travelers);
$per_person = $fare / $num_travelers;

try {
    // Get user ID if logged in, otherwise use 0 for guest booking
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
    
    // Insert booking into database
    $stmt = $conn->prepare("INSERT INTO bookings (user_id, name, age, gender, type, source, destination, date, num_travelers, fare, per_person, special_requirements, transport_mode) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    if (!$stmt) {
        throw new Exception('Database prepare failed: ' . $conn->error);
    }
    
    $stmt->bind_param("isssssssiidss", $user_id, $name, $age, $gender, $type, $source, $destination, $date, $num_travelers, $fare, $per_person, $special_requirements, $transport_mode);
    
    if (!$stmt->execute()) {
        throw new Exception('Database insert failed: ' . $stmt->error);
    }
    
    $booking_id = $conn->insert_id;
    
    // Update user's bookings JSON file if user is logged in
    if ($user_id > 0) {
        require_once 'update_user_bookings.php';
        updateUserBookingsFile($user_id, $conn);
    }
    
    // Return success response
    echo json_encode([
        'status' => 'success',
        'msg' => 'Booking successful!',
        'booking_id' => $booking_id,
        'name' => $name,
        'source' => $source,
        'destination' => $destination,
        'date' => $date,
        'num_travelers' => $num_travelers,
        'total_amount' => number_format($fare, 2),
        'per_person' => number_format($per_person, 2),
        'transport_mode' => $transport_mode,
        'type' => $type
    ]);
    
} catch (Exception $e) {
    error_log("Booking error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'msg' => 'Booking failed. Please try again.'
    ]);
}
?> 