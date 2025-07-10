<?php
require_once __DIR__ . '/../vendor/autoload.php';
use Mpdf\Mpdf;

function generate_ticket_pdf($row) {
    // $row: booking + user info
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>TravelPlanner Ticket - ' . htmlspecialchars($row['booking_id']) . '</title>
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
        </style>
    </head>
    <body>
        <div class="ticket-container">
            <div class="ticket-header">
                <h1>✈️ TravelPlanner</h1>
                <p>Official Travel Ticket</p>
            </div>
            
            <div class="ticket-body">';
    
    // Booking Information
    $html .= '<div class="ticket-section">';
    $html .= '<h3>📋 Booking Information</h3>';
    $html .= '<div class="info-grid">';
    $html .= '<div class="info-item"><div class="info-label">Booking ID</div><div class="info-value">' . htmlspecialchars($row['booking_id']) . '</div></div>';
    $html .= '<div class="info-item"><div class="info-label">Booking Date</div><div class="info-value">' . date('d M Y, h:i A') . '</div></div>';
    $html .= '<div class="info-item"><div class="info-label">Status</div><div class="info-value">' . htmlspecialchars($row['status']) . '</div></div>';
    $html .= '<div class="info-item"><div class="info-label">Category</div><div class="info-value">' . htmlspecialchars($row['booking_type'] ?? '-') . '</div></div>';
    $html .= '</div>';
    $html .= '</div>';

    // Travel Details
    $html .= '<div class="ticket-section">';
    $html .= '<h3>🌍 Travel Details</h3>';
    $html .= '<div class="info-grid">';
    $html .= '<div class="info-item"><div class="info-label">Destination/Package</div><div class="info-value">' . htmlspecialchars($row['destination_name'] ?? $row['package_name'] ?? '-') . '</div></div>';
    $html .= '<div class="info-item"><div class="info-label">Start Date</div><div class="info-value">' . htmlspecialchars($row['start_date'] ?? $row['date'] ?? '-') . '</div></div>';
    $html .= '<div class="info-item"><div class="info-label">End Date</div><div class="info-value">' . htmlspecialchars($row['end_date'] ?? '-') . '</div></div>';
    $html .= '<div class="info-item"><div class="info-label">Number of Travelers</div><div class="info-value">' . htmlspecialchars($row['num_travelers'] ?? '-') . '</div></div>';
    $html .= '<div class="info-item"><div class="info-label">Fare</div><div class="info-value">₹' . htmlspecialchars($row['fare'] ?? '-') . '</div></div>';
    $html .= '</div>';
    $html .= '</div>';

    // Customer Information
    $html .= '<div class="ticket-section">';
    $html .= '<h3>👤 Customer Information</h3>';
    $html .= '<div class="info-grid">';
    $html .= '<div class="info-item"><div class="info-label">Name</div><div class="info-value">' . htmlspecialchars($row['username'] ?? $row['email'] ?? '-') . '</div></div>';
    $html .= '<div class="info-item"><div class="info-label">Email</div><div class="info-value">' . htmlspecialchars($row['email'] ?? '-') . '</div></div>';
    if (!empty($row['phone'])) {
        $html .= '<div class="info-item"><div class="info-label">Phone</div><div class="info-value">' . htmlspecialchars($row['phone']) . '</div></div>';
    }
    $html .= '</div>';
    $html .= '</div>';

    // Add traveler details if available
    if (!empty($row['traveler_details'])) {
        $travelers = json_decode($row['traveler_details'], true);
        if (is_array($travelers)) {
            $html .= '<div class="ticket-section">';
            $html .= '<h3>👥 Traveler Details</h3>';
            foreach ($travelers as $index => $traveler) {
                $html .= '<div class="info-grid">';
                $html .= '<div class="info-item"><div class="info-label">Traveler ' . ($index + 1) . ' - Name</div><div class="info-value">' . htmlspecialchars($traveler['name'] ?? 'N/A') . '</div></div>';
                $html .= '<div class="info-item"><div class="info-label">Age</div><div class="info-value">' . htmlspecialchars($traveler['age'] ?? 'N/A') . '</div></div>';
                $html .= '<div class="info-item"><div class="info-label">Gender</div><div class="info-value">' . htmlspecialchars($traveler['gender'] ?? 'N/A') . '</div></div>';
                if (!empty($traveler['passport'])) {
                    $html .= '<div class="info-item"><div class="info-label">Passport</div><div class="info-value">' . htmlspecialchars($traveler['passport']) . '</div></div>';
                }
                if (!empty($traveler['nationality'])) {
                    $html .= '<div class="info-item"><div class="info-label">Nationality</div><div class="info-value">' . htmlspecialchars($traveler['nationality']) . '</div></div>';
                }
                $html .= '</div>';
            }
            $html .= '</div>';
        }
    }

    // Add plan details if available
    if (!empty($row['plan_details'])) {
        $html .= '<div class="ticket-section">';
        $html .= '<h3>📅 Itinerary</h3>';
        $html .= '<div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 15px;">';
        $html .= '<p><strong>Generated Plan:</strong></p>';
        $html .= '<p>' . nl2br(htmlspecialchars($row['plan_details'])) . '</p>';
        $html .= '</div>';
        $html .= '</div>';
    }

    // Add special requirements if any
    if (!empty($row['special_requirements'])) {
        $html .= '<div class="ticket-section">';
        $html .= '<h3>📝 Special Requirements</h3>';
        $html .= '<div style="background: #fff3cd; padding: 15px; border-radius: 8px; border-left: 4px solid #ffc107;">';
        $html .= '<p>' . nl2br(htmlspecialchars($row['special_requirements'])) . '</p>';
        $html .= '</div>';
        $html .= '</div>';
    }

    $html .= '</div>
            
            <div class="ticket-footer">
                <p><strong>Thank you for choosing TravelPlanner!</strong></p>
                <p>Have a safe and enjoyable journey</p>
                <p>Generated on: ' . date('Y-m-d H:i:s') . '</p>
            </div>
        </div>
    </body>
    </html>';
    
    try {
        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_top' => 15,
            'margin_bottom' => 15,
            'default_font' => 'dejavusans'
        ]);
        $mpdf->WriteHTML($html);
        return $mpdf->Output('', 'S'); // Return as string
    } catch (Exception $e) {
        return false;
    }
} 