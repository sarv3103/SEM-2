<?php
require_once "config.php";
require_once "session.php";

// Only allow admin access
requireAdmin();

header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit;
}

$action = $_POST["action"] ?? "";
$booking_id = $_POST["booking_id"] ?? "";

if (!$action || !$booking_id) {
    echo json_encode(["success" => false, "message" => "Missing action or booking ID"]);
    exit;
}

switch ($action) {
    case "confirm":
        // Update booking status to confirmed
        $stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ?");
        $status = "confirmed";
        $stmt->bind_param("si", $status, $booking_id);
        break;
        
    case "cancel":
        // Update booking status to cancelled
        $stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ?");
        $status = "cancelled";
        $stmt->bind_param("si", $status, $booking_id);
        break;
        
    case "delete":
        // Delete booking
        $stmt = $conn->prepare("DELETE FROM bookings WHERE id = ?");
        $stmt->bind_param("i", $booking_id);
        break;
        
    default:
        echo json_encode(["success" => false, "message" => "Invalid action"]);
        exit;
}

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Booking " . $action . "ed successfully"]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to " . $action . " booking"]);
}
?>