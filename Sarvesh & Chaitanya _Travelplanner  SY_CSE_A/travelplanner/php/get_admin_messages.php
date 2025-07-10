<?php
require_once 'config.php';
header('Content-Type: application/json');

$sql = "
SELECT id, name, email, phone, subject, message, created_at, status
FROM contact_messages
ORDER BY created_at DESC
LIMIT 500
";
$res = $conn->query($sql);
$rows = [];
while ($row = $res->fetch_assoc()) {
    $rows[] = [
        'id' => $row['id'],
        'name' => $row['name'],
        'email' => $row['email'],
        'phone' => $row['phone'],
        'subject' => $row['subject'],
        'message' => $row['message'],
        'date' => $row['created_at'],
        'status' => $row['status'] ?? 'New'
    ];
}
echo json_encode(['data' => $rows]); 