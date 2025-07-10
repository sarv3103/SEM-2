<?php
require_once 'config.php';
header('Content-Type: application/json');

$sql = "
SELECT id, username, CONCAT(first_name, ' ', last_name) AS name, email, mobile, created_at, is_verified, is_admin
FROM users
ORDER BY created_at DESC
LIMIT 500
";
$res = $conn->query($sql);
$rows = [];
while ($row = $res->fetch_assoc()) {
    $rows[] = [
        'id' => $row['id'],
        'username' => $row['username'],
        'name' => $row['name'],
        'email' => $row['email'],
        'mobile' => $row['mobile'],
        'registered' => $row['created_at'],
        'verified' => $row['is_verified'] ? 'Yes' : 'No',
        'is_admin' => $row['is_admin'] ? 'Yes' : 'No'
    ];
}
echo json_encode(['data' => $rows]); 