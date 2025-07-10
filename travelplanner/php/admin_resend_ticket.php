<?php
require_once 'config.php';
header('Content-Type: application/json');
if (!isset($_POST['booking_id'])) {
    echo json_encode(['success'=>false, 'error'=>'No booking_id']);
    exit;
}
$booking_id = intval($_POST['booking_id']);
$res = $conn->query("SELECT b.*, u.email, u.username FROM bookings b LEFT JOIN users u ON b.user_id = u.id WHERE b.id = $booking_id");
if (!$res || !$res->num_rows) {
    echo json_encode(['success'=>false, 'error'=>'Booking not found']);
    exit;
}
$row = $res->fetch_assoc();
require_once 'generate_ticket_pdf.php';
$pdf_content = generate_ticket_pdf($row);
if (!$pdf_content) {
    echo json_encode(['success'=>false, 'error'=>'PDF generation failed']);
    exit;
}
$email = $row['email'];
$subject = "Your TravelPlanner Ticket (Booking ID: " . $row['booking_id'] . ")";
$message = "Dear " . htmlspecialchars($row['username'] ?? $email) . ",\n\nPlease find your ticket attached.\n\nThank you for booking with TravelPlanner!";
$boundary = md5(time());
$headers = "From: no-reply@travelplanner.com\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n";
$body = "--$boundary\r\n";
$body .= "Content-Type: text/plain; charset=UTF-8\r\n\r\n$message\r\n";
$body .= "--$boundary\r\n";
$body .= "Content-Type: application/pdf; name=\"ticket.pdf\"\r\n";
$body .= "Content-Transfer-Encoding: base64\r\n";
$body .= "Content-Disposition: attachment; filename=\"ticket.pdf\"\r\n\r\n";
$body .= chunk_split(base64_encode($pdf_content));
$body .= "--$boundary--";
$sent = mail($email, $subject, $body, $headers);
echo json_encode(['success'=>$sent]); 