<?php
require_once 'config.php';
header('Content-Type: application/json');

$sql = "
SELECT po.id, po.razorpay_payment_id, po.razorpay_order_id, po.booking_id, po.amount, po.status, po.payment_date, po.method,
       b.booking_id AS booking_code, b.package_name, b.destination_name, b.date, b.start_date, b.end_date,
       u.username, u.email
FROM payment_orders po
LEFT JOIN bookings b ON po.booking_id = b.id
LEFT JOIN users u ON b.user_id = u.id
ORDER BY po.payment_date DESC, po.created_at DESC
LIMIT 500
";
$res = $conn->query($sql);
$rows = [];
while ($row = $res->fetch_assoc()) {
    // Payment method fallback
    $method = $row['method'] ?? 'Razorpay';
    // Booking name
    $bookingName = $row['package_name'] ?: $row['destination_name'] ?: '-';
    // Dates
    $dates = $row['start_date'] && $row['end_date'] ? ($row['start_date'] . ' to ' . $row['end_date']) : ($row['date'] ?? '');
    $rows[] = [
        'id' => $row['id'],
        'payment_id' => $row['razorpay_payment_id'],
        'order_id' => $row['razorpay_order_id'],
        'booking_id' => $row['booking_code'],
        'user' => $row['username'] . ' (' . $row['email'] . ')',
        'amount' => $row['amount'],
        'status' => $row['status'],
        'date' => $row['payment_date'],
        'method' => $method,
        'name' => $bookingName,
        'dates' => $dates
    ];
}
echo json_encode(['data' => $rows]); 