<?php
require_once 'config.php';
header('Content-Type: application/json');

// Get total revenue and total bookings
$sql = "SELECT COUNT(*) AS total_bookings, COALESCE(SUM(po.amount),0) AS total_revenue
        FROM payment_orders po
        WHERE po.status = 'completed' AND (po.payment_method = 'wallet' OR po.payment_method = 'razorpay')";
$res = $conn->query($sql);
$summary = $res->fetch_assoc();

// Get revenue/bookings for current month
$sql = "SELECT COUNT(*) AS month_bookings, COALESCE(SUM(po.amount),0) AS month_revenue
        FROM payment_orders po
        WHERE po.status = 'completed' AND (po.payment_method = 'wallet' OR po.payment_method = 'razorpay') AND MONTH(po.payment_date) = MONTH(CURRENT_DATE()) AND YEAR(po.payment_date) = YEAR(CURRENT_DATE())";
$res = $conn->query($sql);
$month = $res->fetch_assoc();

// Get all completed payments with booking info
$sql = "SELECT 
    b.id AS booking_id,
    b.destination,
    b.created_at AS booking_date,
    po.amount,
    po.razorpay_payment_id,
    po.payment_date,
    po.payment_method
FROM payment_orders po
JOIN bookings b ON po.booking_id = b.id
WHERE po.status = 'completed' AND (po.payment_method = 'wallet' OR po.payment_method = 'razorpay')
ORDER BY po.payment_date DESC";
$res = $conn->query($sql);
$rows = [];
while ($row = $res->fetch_assoc()) {
    $rows[] = [
        'booking_id' => $row['booking_id'],
        'destination' => $row['destination'],
        'booking_date' => $row['booking_date'],
        'amount' => $row['amount'],
        'razorpay_payment_id' => $row['razorpay_payment_id'],
        'payment_date' => $row['payment_date']
    ];
}

echo json_encode([
    'summary' => [
        'total_bookings' => (int)$summary['total_bookings'],
        'total_revenue' => (float)$summary['total_revenue'],
        'month_bookings' => (int)$month['month_bookings'],
        'month_revenue' => (float)$month['month_revenue']
    ],
    'data' => $rows
]); 