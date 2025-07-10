<?php
// view_ticket_details.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn = new mysqli('localhost', 'root', '', 'travelplanner');
if ($conn->connect_error) die("<div class='alert alert-danger'>DB Error: " . $conn->connect_error . "</div>");

$bookingId = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;
if (!$bookingId) die("<div class='alert alert-warning'>No booking ID provided.</div>");

// Fetch booking
$stmt = $conn->prepare("SELECT * FROM bookings WHERE id = ?");
$stmt->bind_param("i", $bookingId);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();
if (!$booking) die("<div class='alert alert-warning'>Booking not found for ID $bookingId.</div>");

// If package, fetch package details
$package = null;
if (isset($booking['category']) && strtolower($booking['category']) === 'package' && !empty($booking['name'])) {
    $stmt2 = $conn->prepare("SELECT * FROM packages WHERE name = ?");
    $stmt2->bind_param("s", $booking['name']);
    $stmt2->execute();
    $package = $stmt2->get_result()->fetch_assoc();
}

// Prepare traveler(s) info (future-proof for multiple travelers)
$travelers = [];
if (isset($booking['traveller_name']) && $booking['traveller_name'] !== '-') {
    $travelers[] = [
        'name' => $booking['traveller_name'],
        'email' => $booking['traveller_email'],
        'mobile' => $booking['traveller_mobile'],
        'email_verified' => $booking['email_verified']
    ];
}

// Format date fields
function formatDate($date) {
    if ($date === '0000-00-00' || $date === null || $date === '' || $date === '-') return 'To be confirmed';
    return date('d M Y', strtotime($date));
}
function formatDateTime($dt) {
    if ($dt === '0000-00-00 00:00:00' || $dt === null || $dt === '' || $dt === '-') return 'To be confirmed';
    return date('d M Y H:i', strtotime($dt));
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Ticket Details - TravelPlanner</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body { background: #f5f5f5; }
        .ticket-details-container { max-width: 900px; margin: 40px auto; background: #fff; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); padding: 32px; }
        .ticket-header { background: linear-gradient(135deg, #0077cc, #2193b0); color: white; padding: 30px; border-radius: 12px 12px 0 0; text-align: center; }
        .ticket-header h2 { margin: 0; font-size: 2rem; font-weight: bold; }
        .ticket-section { margin-bottom: 32px; }
        .info-label { font-weight: bold; color: #0077cc; }
        .info-value { color: #333; }
        .package-section { background: #e7f3ff; border-radius: 10px; padding: 20px; margin-bottom: 24px; }
        .traveler-list { background: #f8f9fa; border-radius: 10px; padding: 20px; }
        .traveler-item { border-bottom: 1px solid #e0e0e0; padding-bottom: 10px; margin-bottom: 10px; }
        .traveler-item:last-child { border-bottom: none; margin-bottom: 0; }
        .print-btn { float: right; margin-bottom: 20px; }
        @media print {
            .print-btn, .back-btn { display: none !important; }
            body { background: #fff !important; }
            .ticket-details-container { box-shadow: none !important; border: none !important; }
        }
    </style>
</head>
<body>
    <div class="ticket-details-container">
        <div class="ticket-header">
            <h2>Booking Ticket Details</h2>
            <p class="mb-0">This is a digital ticket. You can print or save as PDF using your browser's print option.</p>
        </div>
        <button class="btn btn-primary print-btn" onclick="window.print()">Print / Save as PDF</button>
        <a href="javascript:history.back()" class="btn btn-secondary back-btn">Back</a>
        <div class="ticket-section">
            <h4>Booking Information</h4>
            <div class="row">
                <div class="col-md-6">
                    <p><span class="info-label">Booking ID:</span> <span class="info-value"><?= htmlspecialchars($booking['id']) ?></span></p>
                    <p><span class="info-label">Type:</span> <span class="info-value"><?= htmlspecialchars($booking['category'] ?? '-') ?></span></p>
                    <p><span class="info-label">From:</span> <span class="info-value"><?= htmlspecialchars($booking['from'] ?? '-') ?></span></p>
                    <p><span class="info-label">To:</span> <span class="info-value"><?= htmlspecialchars($booking['to'] ?? '-') ?></span></p>
                    <p><span class="info-label">Date:</span> <span class="info-value"><?= formatDate($booking['dates'] ?? '-') ?></span></p>
                    <p><span class="info-label">Status:</span> <span class="info-value"><?= htmlspecialchars($booking['status'] ?? '-') ?></span></p>
                </div>
                <div class="col-md-6">
                    <p><span class="info-label">Payment Mode:</span> <span class="info-value"><?= htmlspecialchars($booking['payment_mode'] ?? '-') ?></span></p>
                    <p><span class="info-label">Razorpay Payment ID:</span> <span class="info-value"><?= htmlspecialchars($booking['razorpay_payment_id'] ?? '-') ?></span></p>
                    <p><span class="info-label">Amount:</span> <span class="info-value">₹<?= htmlspecialchars($booking['amount'] ?? '-') ?></span></p>
                    <p><span class="info-label">Payment Status:</span> <span class="info-value"><?= htmlspecialchars($booking['payment_status'] ?? '-') ?></span></p>
                    <p><span class="info-label">Created At:</span> <span class="info-value"><?= formatDateTime($booking['created_at'] ?? '-') ?></span></p>
                </div>
            </div>
        </div>
        <div class="ticket-section package-section">
            <h4>Package Details</h4>
            <?php if ($package): ?>
                <?php foreach ($package as $key => $value): ?>
                    <p><span class="info-label"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $key))) ?>:</span> <span class="info-value"><?= htmlspecialchars($value) ?></span></p>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-muted">No package details for this booking.</p>
            <?php endif; ?>
        </div>
        <div class="ticket-section traveler-list">
            <h4>Traveller(s)</h4>
            <?php if (count($travelers) > 0): ?>
                <?php foreach ($travelers as $trav): ?>
                    <div class="traveler-item">
                        <p><span class="info-label">Name:</span> <span class="info-value"><?= htmlspecialchars($trav['name']) ?></span></p>
                        <p><span class="info-label">Email:</span> <span class="info-value"><?= htmlspecialchars($trav['email']) ?></span></p>
                        <p><span class="info-label">Mobile:</span> <span class="info-value"><?= htmlspecialchars($trav['mobile']) ?></span></p>
                        <p><span class="info-label">Email Verified:</span> <span class="info-value"><?= htmlspecialchars($trav['email_verified']) ?></span></p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-muted">No traveler details available.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html> 