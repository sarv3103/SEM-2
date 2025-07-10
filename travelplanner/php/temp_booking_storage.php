<?php
// Temporary booking storage system
// This file handles storing and retrieving booking data for PDF generation

session_start();

// Function to store booking data temporarily
function storeTempBooking($tempId, $bookingData) {
    $tempDir = __DIR__ . '/../temp_bookings/';
    if (!is_dir($tempDir)) {
        mkdir($tempDir, 0777, true);
    }
    
    $filename = $tempDir . $tempId . '.json';
    $data = [
        'booking_data' => $bookingData,
        'created_at' => time(),
        'expires_at' => time() + 300 // Expires in 5 minutes
    ];
    
    return file_put_contents($filename, json_encode($data));
}

// Function to retrieve booking data
function getTempBooking($tempId) {
    $tempDir = __DIR__ . '/../temp_bookings/';
    $filename = $tempDir . $tempId . '.json';
    
    if (!file_exists($filename)) {
        return null;
    }
    
    $data = json_decode(file_get_contents($filename), true);
    
    // Check if expired
    if ($data['expires_at'] < time()) {
        unlink($filename); // Clean up expired file
        return null;
    }
    
    return $data['booking_data'];
}

// Function to clean up expired files
function cleanupExpiredBookings() {
    $tempDir = __DIR__ . '/../temp_bookings/';
    if (!is_dir($tempDir)) {
        return;
    }
    
    $files = glob($tempDir . '*.json');
    foreach ($files as $file) {
        $data = json_decode(file_get_contents($file), true);
        if ($data && $data['expires_at'] < time()) {
            unlink($file);
        }
    }
}

// Clean up expired files on each request
cleanupExpiredBookings();

// Handle AJAX requests for storing booking data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    if ($_POST['action'] === 'store') {
        $tempId = $_POST['temp_id'] ?? '';
        $bookingData = $_POST['booking_data'] ?? '';
        
        if ($tempId && $bookingData) {
            $success = storeTempBooking($tempId, $bookingData);
            echo json_encode(['success' => $success]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Missing data']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
    exit;
}
?> 