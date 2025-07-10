<?php
require_once 'config.php';
header('Content-Type: application/json');

$sql = "
SELECT b.id, b.user_id, b.name, b.age, b.gender, b.type, b.source, b.destination, b.date, b.num_travelers, b.fare, b.per_person, b.created_at,
       u.username, u.email, u.mobile, u.is_verified,
       po.status AS payment_status, po.amount, po.razorpay_payment_id, po.payment_date
FROM bookings b
LEFT JOIN users u ON b.user_id = u.id
LEFT JOIN payment_orders po ON po.booking_id = b.id
ORDER BY b.created_at DESC
LIMIT 500
";
$res = $conn->query($sql);
$rows = [];
while ($row = $res->fetch_assoc()) {
    // Determine category (ticket type)
    $category = $row['type'] ?? 'Ticket';
    // Dates - use only the date column since start_date/end_date don't exist
    $dates = $row['date'] ?? '-';
    // Determine payment/booking status
    $status = 'Pending';
    if (isset($row['payment_status'])) {
        if ($row['payment_status'] === 'completed' || $row['payment_status'] === 'paid') {
            $status = 'Paid';
        } else if ($row['payment_status']) {
            $status = ucfirst($row['payment_status']);
        }
    }
    $rows[] = [
        'id' => $row['id'],
        'booking_id' => $row['id'],
        'user' => $row['username'] . ' (' . $row['email'] . ')',
        'category' => $category,
        'name' => $row['name'] ?: '-',
        'from' => $row['source'] ?? '-',
        'to' => $row['destination'] ?? '-',
        'dates' => $dates,
        'travelers' => $row['num_travelers'],
        'status' => $status,
        'payment_status' => $status,
        'amount' => $row['fare'] ?? $row['amount'] ?? $row['per_person'],
        'razorpay_payment_id' => $row['razorpay_payment_id'],
        'created_at' => $row['created_at'],
        'payment_mode' => '-',
        'payment_date' => $row['payment_date'] ?? '-',
        'traveller_name' => $row['username'] ?? '-',
        'traveller_email' => $row['email'] ?? '-',
        'traveller_mobile' => $row['mobile'] ?? '-',
        'email_verified' => isset($row['is_verified']) ? ($row['is_verified'] ? 'Yes' : 'No') : '-',
    ];
}
echo json_encode(['data' => $rows]); 