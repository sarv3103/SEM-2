<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once 'config.php';
header('Content-Type: application/json');
if (!isset($_POST['user_id'])) {
    echo json_encode(['success'=>false, 'error'=>'No user_id']);
    exit;
}
$user_id = intval($_POST['user_id']);
$stmt = $conn->prepare("DELETE FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$ok = $stmt->execute();
if (!$ok) {
    echo json_encode(['success'=>false, 'error'=>$stmt->error]);
    exit;
}
echo json_encode(['success'=>$ok]); 