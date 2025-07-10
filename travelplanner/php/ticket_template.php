<?php
// php/ticket_template.php - Generates ticket HTML from $booking array
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>TravelPlanner Ticket - <?= htmlspecialchars($ticketNo) ?></title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
        .ticket-container { max-width: 800px; margin: 0 auto; background: white; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); overflow: hidden; }
        .ticket-header { background: linear-gradient(135deg, #0077cc, #2193b0); color: white; padding: 30px; text-align: center; }
        .ticket-header h1 { margin: 0; font-size: 28px; font-weight: bold; }
        .ticket-header p { margin: 10px 0 0 0; opacity: 0.9; }
        .ticket-body { padding: 30px; }
        .ticket-section { margin-bottom: 25px; }
        .ticket-section h3 { color: #0077cc; border-bottom: 2px solid #0077cc; padding-bottom: 8px; margin-bottom: 15px; }
        .info-grid { display: table; width: 100%; margin-bottom: 20px; }
        .info-item { display: table-cell; width: 50%; background: #f8f9fa; padding: 12px; border-radius: 8px; border-left: 4px solid #0077cc; margin-right: 10px; }
        .info-label { font-weight: bold; color: #333; margin-bottom: 5px; }
        .info-value { color: #666; }
        .ticket-footer { background: #333; color: white; padding: 20px; text-align: center; }
        .ticket-footer p { margin: 5px 0; }
        .qr-placeholder { width: 100px; height: 100px; background: #e0e0e0; margin: 20px auto; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="ticket-container">
        <div class="ticket-header">
            <h1>✈️ TravelPlanner</h1>
            <p>Official Travel Ticket</p>
            <div class="qr-placeholder">QR Code</div>
        </div>
        <div class="ticket-body">
            <div class="ticket-section">
                <h3>Booking Details</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Booking ID</div>
                        <div class="info-value"><?= htmlspecialchars($booking['booking_id'] ?? $booking['id'] ?? '-') ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Type</div>
                        <div class="info-value"><?= htmlspecialchars($booking['type'] ?? $booking['booking_type'] ?? '-') ?></div>
                    </div>
                </div>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">From</div>
                        <div class="info-value"><?= htmlspecialchars($booking['source'] ?? $booking['from'] ?? '-') ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">To</div>
                        <div class="info-value"><?= htmlspecialchars($booking['destination'] ?? $booking['to'] ?? '-') ?></div>
                    </div>
                </div>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Date</div>
                        <div class="info-value"><?= htmlspecialchars($booking['date'] ?? $booking['travel_date'] ?? '-') ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Travelers</div>
                        <div class="info-value"><?= htmlspecialchars($booking['num_travelers'] ?? $booking['num_persons'] ?? '-') ?></div>
                    </div>
                </div>
            </div>
            <div class="ticket-section">
                <h3>Contact Information</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Mobile</div>
                        <div class="info-value"><?= htmlspecialchars($booking['contact_mobile'] ?? $booking['phone'] ?? '-') ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Email</div>
                        <div class="info-value"><?= htmlspecialchars($booking['contact_email'] ?? $booking['email'] ?? '-') ?></div>
                    </div>
                </div>
            </div>
            <div class="ticket-section">
                <h3>Fare Summary</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Amount Paid</div>
                        <div class="info-value">&#8377;<?= htmlspecialchars(number_format($booking['fare'] ?? $booking['total_amount'] ?? 1, 2)) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Payment Method</div>
                        <div class="info-value"><?= htmlspecialchars($booking['payment_method'] ?? 'Wallet/Test') ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="ticket-footer">
            <p><strong>Thank you for choosing TravelPlanner!</strong></p>
            <p>Have a safe and enjoyable journey</p>
            <p>Generated on: <?= date('Y-m-d H:i:s') ?></p>
        </div>
    </div>
</body>
</html> 