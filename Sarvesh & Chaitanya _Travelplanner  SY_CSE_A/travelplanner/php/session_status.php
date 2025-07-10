<?php
// php/session_status.php - Returns session login status for AJAX check
require_once 'config.php';
session_start();
header('Content-Type: application/json');
$response = ['logged_in' => false];
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT username, is_admin, wallet_balance FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($username, $is_admin, $wallet_balance);
    if ($stmt->fetch()) {
        $response = [
            'logged_in' => true,
            'user_id' => $user_id,
            'username' => $username,
            'is_admin' => $is_admin,
            'wallet_balance' => $wallet_balance
        ];
    }
    $stmt->close();
}
echo json_encode($response);
