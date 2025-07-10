<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn = new mysqli('localhost', 'root', '', 'travelplanner');
if ($conn->connect_error) die('<div class="alert alert-danger">DB Error: ' . $conn->connect_error . '</div>');

$bookingId = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;
if (!$bookingId) die('<div class="alert alert-warning">No booking ID provided.</div>');

$stmt = $conn->prepare("SELECT * FROM bookings WHERE id = ?");
$stmt->bind_param("i", $bookingId);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();
if (!$booking) die('<div class="alert alert-warning">Booking not found.</div>');

function formatDate($date) {
    if ($date === '0000-00-00' || $date === null || $date === '' || $date === '-') return 'To be confirmed';
    return date('d M Y', strtotime($date));
}
function formatDateTime($dt) {
    if ($dt === '0000-00-00 00:00:00' || $dt === null || $dt === '' || $dt === '-') return 'To be confirmed';
    return date('d M Y H:i', strtotime($dt));
}
?>
<div class="container-fluid">
  <div class="row mb-2">
    <div class="col-md-6">
      <p><strong>Booking ID:</strong> <?= htmlspecialchars($booking['id']) ?></p>
      <p><strong>User:</strong> <?= htmlspecialchars($booking['user'] ?? '-') ?></p>
      <p><strong>Type:</strong> <?= htmlspecialchars($booking['category'] ?? '-') ?></p>
      <p><strong>From:</strong> <?= htmlspecialchars($booking['from'] ?? '-') ?></p>
      <p><strong>To:</strong> <?= htmlspecialchars($booking['to'] ?? '-') ?></p>
      <p><strong>Date:</strong> <?= formatDate($booking['dates'] ?? '-') ?></p>
      <p><strong>Status:</strong> <?= htmlspecialchars($booking['status'] ?? '-') ?></p>
    </div>
    <div class="col-md-6">
      <p><strong>Payment Mode:</strong> <?= htmlspecialchars($booking['payment_mode'] ?? '-') ?></p>
      <p><strong>Razorpay Payment ID:</strong> <?= htmlspecialchars($booking['razorpay_payment_id'] ?? '-') ?></p>
      <p><strong>Amount:</strong> ₹<?= htmlspecialchars($booking['amount'] ?? '-') ?></p>
      <p><strong>Payment Status:</strong> <?= htmlspecialchars($booking['payment_status'] ?? '-') ?></p>
      <p><strong>Created At:</strong> <?= formatDateTime($booking['created_at'] ?? '-') ?></p>
    </div>
  </div>
  <div class="mb-3">
    <label for="adminPaymentRef" class="form-label">Payment Reference (if any)</label>
    <input type="text" class="form-control" id="adminPaymentRef" placeholder="Enter payment reference, UPI, or transaction ID">
  </div>
  <div class="mb-3">
    <label for="adminRemarks" class="form-label">Admin Remarks</label>
    <textarea class="form-control" id="adminRemarks" rows="2" placeholder="Enter any remarks or notes"></textarea>
  </div>
  <div class="alert alert-info">
    <strong>Instructions:</strong> Use this panel to verify payment and confirm or cancel the booking. All actions are logged.
  </div>
</div> 