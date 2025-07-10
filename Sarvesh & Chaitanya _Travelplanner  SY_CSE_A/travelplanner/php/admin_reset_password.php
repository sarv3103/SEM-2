<?php
require_once 'config.php';
header('Content-Type: application/json');
if (!isset($_POST['user_id'], $_POST['new_password'])) {
    echo json_encode(['success'=>false, 'error'=>'Missing params']);
    exit;
}
$user_id = intval($_POST['user_id']);
$new_password = $_POST['new_password'];
$hash = password_hash($new_password, PASSWORD_DEFAULT);
$stmt = $conn->prepare("UPDATE users SET password=? WHERE id=?");
$stmt->bind_param("si", $hash, $user_id);
$ok = $stmt->execute();
echo json_encode(['success'=>$ok]); 