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

$user_id = intval($_POST["user_id"] ?? 0);
$username = trim($_POST["username"] ?? "");
$email = trim($_POST["email"] ?? "");
$mobile = trim($_POST["mobile"] ?? "");
$is_verified = intval($_POST["is_verified"] ?? 0);

if (!$user_id || !$username || !$email) {
    echo json_encode(["success" => false, "message" => "Missing required fields"]);
    exit;
}

try {
    $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, mobile = ?, is_verified = ? WHERE id = ?");
    $stmt->bind_param("sssii", $username, $email, $mobile, $is_verified, $user_id);
    
    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "User updated successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to update user"]);
    }
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
}
?>