<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn = new mysqli('localhost', 'root', '', 'travelplanner');
if ($conn->connect_error) die('DB Error: ' . $conn->connect_error);

$bookingId = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : 0;
$action = $_POST['action'] ?? '';
$paymentRef = $_POST['payment_ref'] ?? '';
$remarks = $_POST['remarks'] ?? '';

if (!$bookingId || !$action) die('Invalid request');

if ($action === 'mark_paid') {
    $stmt = $conn->prepare("UPDATE bookings SET payment_status='completed', status='completed', payment_mode='manual', razorpay_payment_id=?, admin_remarks=? WHERE id=?");
    $stmt->bind_param("ssi", $paymentRef, $remarks, $bookingId);
    if ($stmt->execute()) {
        echo 'success';
    } else {
        echo 'DB error: ' . $conn->error;
    }
} elseif ($action === 'cancel') {
    $stmt = $conn->prepare("UPDATE bookings SET status='cancelled', payment_status='cancelled', admin_remarks=? WHERE id=?");
    $stmt->bind_param("si", $remarks, $bookingId);
    if ($stmt->execute()) {
        echo 'success';
    } else {
        echo 'DB error: ' . $conn->error;
    }
} else {
    echo 'Invalid action';
} 