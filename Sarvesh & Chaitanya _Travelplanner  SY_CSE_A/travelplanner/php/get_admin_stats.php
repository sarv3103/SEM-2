<?php
require_once 'config.php';
header('Content-Type: application/json');

// Total bookings
$totalBookings = $conn->query("SELECT COUNT(*) AS cnt FROM bookings")->fetch_assoc()['cnt'];
// Total revenue (sum of completed payments)
$totalRevenue = $conn->query("SELECT SUM(amount) AS sum FROM payment_orders WHERE status = 'completed'")->fetch_assoc()['sum'] ?? 0;
// Total users
$totalUsers = $conn->query("SELECT COUNT(*) AS cnt FROM users")->fetch_assoc()['cnt'];
// Total contact messages
$totalMessages = $conn->query("SELECT COUNT(*) AS cnt FROM contact_messages")->fetch_assoc()['cnt'];
// Total plans generated
$totalPlans = $conn->query("SELECT COUNT(*) AS cnt FROM plans")->fetch_assoc()['cnt'];
// Total ticket downloads (if you have a downloads table, else set to 0)
$totalDownloads = 0;
if ($conn->query("SHOW TABLES LIKE 'ticket_downloads'")->num_rows) {
    $totalDownloads = $conn->query("SELECT COUNT(*) AS cnt FROM ticket_downloads")->fetch_assoc()['cnt'];
}
// Total visitors (if you have a visitors table, else set to 0)
$totalVisitors = 0;
if ($conn->query("SHOW TABLES LIKE 'visitors'")->num_rows) {
    $totalVisitors = $conn->query("SELECT SUM(count) AS sum FROM visitors")->fetch_assoc()['sum'] ?? 0;
}

// Add contact messages count
$res2 = $conn->query("SELECT COUNT(*) as total, SUM(status='New') as new_count FROM contact_messages");
$row2 = $res2->fetch_assoc();
$data['total_messages'] = (int)$row2['total'];
$data['new_messages'] = (int)$row2['new_count'];

// Add month_bookings and month_revenue (sync with get_admin_revenue.php)
$sql = "SELECT COUNT(*) AS month_bookings, COALESCE(SUM(po.amount),0) AS month_revenue
        FROM payment_orders po
        WHERE po.status = 'completed' AND MONTH(po.payment_date) = MONTH(CURRENT_DATE()) AND YEAR(po.payment_date) = YEAR(CURRENT_DATE())";
$res = $conn->query($sql);
$month = $res->fetch_assoc();

// Output unified stats

echo json_encode([
    'total_bookings' => (int)$totalBookings,
    'total_revenue' => (float)$totalRevenue,
    'total_users' => (int)$totalUsers,
    'total_messages' => (int)$totalMessages,
    'total_plans' => (int)$totalPlans,
    'total_downloads' => (int)$totalDownloads,
    'total_visitors' => (int)$totalVisitors,
    'month_bookings' => (int)$month['month_bookings'],
    'month_revenue' => (float)$month['month_revenue']
]); 