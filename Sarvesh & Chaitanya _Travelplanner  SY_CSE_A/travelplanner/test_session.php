<?php
// Test session status
session_start();
echo "Session ID: " . session_id() . "\n";
echo "Session data:\n";
print_r($_SESSION);

// Test session_status.php endpoint
$url = 'http://localhost/travelplanner/php/session_status.php';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIE, 'PHPSESSID=' . session_id());

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "\nSession status endpoint response:\n";
echo "HTTP Code: $http_code\n";
echo "Response: $response\n";

$json_response = json_decode($response, true);
if ($json_response) {
    echo "Decoded response:\n";
    print_r($json_response);
}
?> 