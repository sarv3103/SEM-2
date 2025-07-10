<?php
// mybookings.php - Show all bookings for the logged-in user
session_start();
require_once __DIR__ . '/session.php';
// Anti-cache headers
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');
requireLogin();
$userId = $_SESSION['user_id'];
// Fetch wallet balance
require_once __DIR__ . '/config.php';
$wallet_balance = 0;
$stmt = $conn->prepare("SELECT wallet_balance FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($wallet_balance);
$stmt->fetch();
$stmt->close();
$dir = __DIR__ . '/../user_bookings/';
$file = $dir . 'user_' . $userId . '.json';
$bookings = file_exists($file) ? json_decode(file_get_contents($file), true) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Bookings - TravelPlanner</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        .booking-card { background: #fff; border-radius: 10px; box-shadow: 0 2px 8px #0001; padding: 1.5em; margin: 1em 0; }
        .booking-card h3 { color: #0077cc; }
        .download-btn { background: #0077cc; color: #fff; border: none; padding: 0.5em 1em; border-radius: 5px; cursor: pointer; }
    </style>
</head>
<body>
    <div style="position: absolute; top: 20px; right: 30px; background: #fff; border: 2px solid #0077cc; color: #0077cc; border-radius: 20px; padding: 8px 18px; font-weight: 600; font-size: 1.1rem; box-shadow: 0 2px 8px #0077cc22; z-index: 1000;"><i class="fas fa-wallet"></i> Wallet: ₹<?= number_format($wallet_balance, 2) ?></div>
    <h1>My Bookings</h1>
    <?php if (!$bookings): ?>
        <p>No bookings found.</p>
    <?php else: ?>
        <?php foreach (array_reverse($bookings) as $i => $b): ?>
            <div class="booking-card">
                <h3>
                    <?php
                    // Show package name or destination
                    if (isset($b['package_name'])) {
                        echo htmlspecialchars($b['package_name']);
                    } elseif (isset($b['destination'])) {
                        echo htmlspecialchars($b['destination']);
                    } elseif (isset($b['destination_name'])) {
                        echo htmlspecialchars($b['destination_name']);
                    } else {
                        echo 'Booking';
                    }
                    ?>
                </h3>
                <div><b>Type:</b> <?= htmlspecialchars($b['type'] ?? 'N/A') ?></div>
                <div><b>Date:</b> <?= htmlspecialchars($b['travel_date'] ?? ($b['date'] ?? '')) ?></div>
                <div><b>Persons:</b> <?= htmlspecialchars($b['num_persons'] ?? ($b['num_travelers'] ?? '')) ?></div>
                <div><b>Phone:</b> <?= htmlspecialchars($b['phone'] ?? 'N/A') ?></div>
                <div><b>Booked At:</b> <?= htmlspecialchars($b['timestamp'] ?? '') ?></div>
                <a href="#" class="download-btn view-ticket-btn" data-booking='<?= htmlspecialchars(json_encode($b), ENT_QUOTES, 'UTF-8') ?>' style="margin-right:10px;">View Ticket</a>
                <a href="download_ticket.php?booking_id=<?= urlencode($b['id'] ?? $b['booking_id'] ?? '') ?>&user_id=<?= $userId ?>&format=html" target="_blank" class="download-btn" style="text-decoration: none; display: inline-block;">Download Ticket (HTML)</a>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
    <a href="../index.html" class="back-btn" style="display:inline-block;margin-top:20px;padding:10px 20px;background:#0077cc;color:#fff;text-decoration:none;border-radius:5px;">&larr; Back to Home</a>
    <script src="../js/ticket_modal.js"></script>
</body>
</html>
