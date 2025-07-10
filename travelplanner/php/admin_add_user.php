<?php
require_once 'config.php';
header('Content-Type: application/json');
$username = trim($_POST['username'] ?? '');
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$mobile = trim($_POST['mobile'] ?? '');
$password = $_POST['password'] ?? '';
if (!$username || !$email || !$password) {
    echo json_encode(['success'=>false, 'error'=>'Username, email, and password required']);
    exit;
}
list($first_name, $last_name) = array_pad(explode(' ', $name, 2), 2, '');
$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $conn->prepare("INSERT INTO users (username, first_name, last_name, email, mobile, password, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
$stmt->bind_param("ssssss", $username, $first_name, $last_name, $email, $mobile, $hash);
$ok = $stmt->execute();
if ($ok) {
    echo json_encode(['success'=>true, 'user_id'=>$conn->insert_id]);
} else {
    echo json_encode(['success'=>false, 'error'=>'Insert failed']);
} 