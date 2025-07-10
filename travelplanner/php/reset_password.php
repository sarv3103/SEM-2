<?php
// php/reset_password.php - User sets a new password after temp login
session_start();
require_once __DIR__ . '/config.php';
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status'=>'error','msg'=>'Not logged in.']);
    exit();
}
$data = json_decode(file_get_contents('php://input'), true);
$new_password = trim($data['new_password'] ?? '');
if (strlen($new_password) < 6) {
    echo json_encode(['status'=>'error','msg'=>'Password must be at least 6 characters.']);
    exit();
}
$hash = password_hash($new_password, PASSWORD_DEFAULT);
$user_id = intval($_SESSION['user_id']);
$conn->query("UPDATE users SET password='$hash', must_change_pw=0 WHERE id=$user_id");
echo json_encode(['status'=>'success','msg'=>'Password changed successfully. Please log in again.']);
session_destroy();
