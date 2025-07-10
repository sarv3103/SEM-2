<?php
// test_send_ticket_email.php
// Sends a test ticket email using the send_ticket_email.php endpoint

$testEmail = 'sarveshkulkarni3103@gmail.com'; // <-- Change this to your test email address

$bookingData = [
    'source' => 'Mumbai',
    'destination' => 'Goa',
    'date' => '2024-07-01',
    'selected_mode' => 'flight',
    'contact_name' => 'Test User',
    'contact_mobile' => '9876543210',
    'contact_email' => $testEmail,
    'travelers' => [
        ['name' => 'Test User', 'age' => 30, 'gender' => 'M']
    ]
];

$data = [
    'bookingData' => $bookingData,
    'bookingId' => 'TEST123',
    'totalAmount' => 1,
    'emailTo' => $testEmail
];

$options = [
    'http' => [
        'header'  => "Content-Type: application/json\r\n",
        'method'  => 'POST',
        'content' => json_encode($data),
        'ignore_errors' => true
    ]
];
$context  = stream_context_create($options);
$result = file_get_contents('http://localhost/travelplanner/php/send_ticket_email.php', false, $context);

header('Content-Type: application/json');
echo $result; 