<?php
session_start(); // FIRST LINE
// Test the booking endpoint directly
echo "Testing booking endpoint...\n";

// Simulate the booking request
$test_data = [
    'source' => 'Mumbai',
    'destination' => 'Goa',
    'date' => '2024-12-25',
    'num_travelers' => 2,
    'selected_mode' => 'Flight',
    'selected_fare' => '5000',
    'contact_name' => 'Test User',
    'contact_mobile' => '1234567890',
    'contact_email' => 'test@example.com',
    'travelers' => [
        [
            'name' => 'Test User 1',
            'age' => '25',
            'gender' => 'Male'
        ],
        [
            'name' => 'Test User 2',
            'age' => '30',
            'gender' => 'Female'
        ]
    ]
];

// Set a test user ID
$_SESSION['user_id'] = 1;

echo "Session user_id: " . $_SESSION['user_id'] . "\n";

// Test the booking endpoint
$url = 'http://localhost/travelplanner/php/book_main_travel.php';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($test_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen(json_encode($test_data))
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIE, 'PHPSESSID=' . session_id());

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $http_code\n";
echo "Response:\n$response\n";

// Try to decode as JSON
$json_response = json_decode($response, true);
if ($json_response === null) {
    echo "JSON decode error: " . json_last_error_msg() . "\n";
    echo "Raw response starts with: " . substr($response, 0, 200) . "\n";
} else {
    echo "JSON decoded successfully:\n";
    print_r($json_response);
}
?> 