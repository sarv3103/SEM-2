<?php
// update_user_bookings.php - Helper function to update user's bookings JSON file

function updateUserBookingsFile($userId, $conn) {
    try {
        // Fetch all bookings for the user
        $userBookings = [];
        $stmt = $conn->prepare("SELECT * FROM bookings WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $userBookings[] = $row;
        }
        $stmt->close();
        
        // Create directory if it doesn't exist
        $dir = __DIR__ . '/../user_bookings/';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        
        // Write to JSON file
        $filePath = $dir . 'user_' . $userId . '.json';
        $success = file_put_contents($filePath, json_encode($userBookings, JSON_PRETTY_PRINT));
        
        if ($success === false) {
            error_log("Failed to write user bookings file: " . $filePath);
            return false;
        }
        
        return true;
    } catch (Exception $e) {
        error_log("Error updating user bookings file: " . $e->getMessage());
        return false;
    }
}
?> 