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
$payment_id = $_POST["payment_id"] ?? "";

if (!$action || !$payment_id) {
    echo json_encode(["success" => false, "message" => "Missing action or payment ID"]);
    exit;
}

switch ($action) {
    case "verify":
        // Update payment status to completed
        $stmt = $conn->prepare("UPDATE payment_orders SET status = ? WHERE id = ?");
        $status = "completed";
        $stmt->bind_param("si", $status, $payment_id);
        break;
        
    case "reject":
        // Update payment status to failed
        $stmt = $conn->prepare("UPDATE payment_orders SET status = ? WHERE id = ?");
        $status = "failed";
        $stmt->bind_param("si", $status, $payment_id);
        break;
        
    default:
        echo json_encode(["success" => false, "message" => "Invalid action"]);
        exit;
}

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Payment " . $action . "ed successfully"]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to " . $action . " payment"]);
}
?>