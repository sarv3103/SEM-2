<?php
require_once 'config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success'=>false, 'error'=>'Invalid request method']);
    exit;
}

$id = intval($_POST['message_id'] ?? 0);
if (!$id) {
    echo json_encode(['success'=>false, 'error'=>'Missing message_id']);
    exit;
}
$stmt = $conn->prepare("UPDATE contact_messages SET status='Read' WHERE id=?");
$stmt->bind_param('i', $id);
$ok = $stmt->execute();
echo json_encode(['success'=>$ok]); 