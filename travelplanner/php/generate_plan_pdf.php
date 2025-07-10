<?php
// php/generate_plan_pdf.php - Generate PDF travel plan
require_once __DIR__ . '/../vendor/autoload.php';
require 'config.php';

try {
    // Get POST data - handle form data
    $planData = null;
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['plan_data'])) {
            // Form data
            $planData = json_decode($_POST['plan_data'], true);
        } else {
            // Try JSON input as fallback
            $input = json_decode(file_get_contents('php://input'), true);
            $planData = $input['plan_data'] ?? null;
        }
    }
    
    if (!$planData) {
        throw new Exception('No plan data provided');
    }
    
    // Extract plan data
    $fromCity = $planData['from_city'] ?? '';
    $toCity = $planData['to_city'] ?? '';
    $startDate = $planData['start_date'] ?? '';
    $endDate = $planData['end_date'] ?? '';
    $travelers = $planData['travelers'] ?? 1;
    $travelStyle = $planData['travel_style'] ?? 'standard';
    $isInternational = $planData['is_international'] ?? false;
    $totalCostPerPerson = $planData['total_cost_per_person'] ?? 0;
    $totalCostForAll = $planData['total_cost_for_all'] ?? 0;
    $sourceToDestCost = $planData['source_to_dest_cost'] ?? 0;
    $sourceToDestMode = $planData['source_to_dest_mode'] ?? '';
    $currency = $planData['currency'] ?? 'INR';
    $cityData = $planData['city_data'] ?? [];
    $duration = $planData['duration'] ?? 0;
    
    // Calculate costs per person with realistic hotel pricing
    // Hotel cost is per room, not per person
    $roomsNeeded = ($travelers <= 4) ? 1 : 2; // 1 room for up to 4 travelers, 2 rooms for more
    $hotelCostPerRoom = $cityData['hotel'] * $duration;
    $totalHotelCost = $hotelCostPerRoom * $roomsNeeded;
    $hotelCostPerPerson = $totalHotelCost / $travelers; // For cost breakdown display
    
    $foodCost = $cityData['food'] * $duration;
    $localTransportCost = $cityData['transport'] * $duration;
    $sightsCost = 0;
    foreach ($cityData['sights'] as $sight) {
        $sightsCost += $sight['cost'];
    }
    
    // Calculate total cost per person
    $totalCostPerPerson = $sourceToDestCost + $hotelCostPerPerson + $foodCost + $localTransportCost + $sightsCost;
    $totalCostForAll = $totalCostPerPerson * $travelers;
    
    // Set headers for PDF download
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="TravelPlanner_Plan_' . ucfirst($toCity) . '.pdf"');
    
    // Create professional PDF travel plan
    $pdfContent = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>TravelPlanner - Trip to ' . ucfirst($toCity) . '</title>
        <style>
            body { 
                font-family: Arial, sans-serif; 
                margin: 0; 
                padding: 20px; 
                background: #f5f5f5; 
                color: #333;
            }
            .plan-container { 
                max-width: 800px; 
                margin: 0 auto; 
                background: white; 
                border-radius: 15px; 
                box-shadow: 0 10px 30px rgba(0,0,0,0.1); 
                overflow: hidden; 
            }
            .plan-header { 
                background: linear-gradient(135deg, #0077cc, #2193b0); 
                color: white; 
                padding: 30px; 
                text-align: center; 
            }
            .plan-header h1 { 
                margin: 0; 
                font-size: 32px; 
                font-weight: bold; 
            }
            .plan-header p { 
                margin: 10px 0 0 0; 
                opacity: 0.9; 
                font-size: 16px;
            }
            .plan-body { 
                padding: 30px; 
            }
            .plan-section { 
                margin-bottom: 25px; 
                border-bottom: 1px solid #eee;
                padding-bottom: 20px;
            }
            .plan-section:last-child {
                border-bottom: none;
            }
            .plan-section h3 { 
                color: #0077cc; 
                border-bottom: 2px solid #0077cc; 
                padding-bottom: 8px; 
                margin-bottom: 15px; 
                font-size: 20px;
            }
            .info-grid { 
                display: table; 
                width: 100%; 
                margin-bottom: 20px; 
            }
            .info-item { 
                display: table-cell; 
                width: 50%; 
                background: #f8f9fa; 
                padding: 12px; 
                border-radius: 8px; 
                border-left: 4px solid #0077cc; 
                margin-right: 10px; 
            }
            .info-label { 
                font-weight: bold; 
                color: #333; 
                margin-bottom: 5px; 
            }
            .info-value { 
                color: #666; 
            }
            .cost-breakdown {
                background: #f8f9fa;
                padding: 20px;
                border-radius: 10px;
                margin: 20px 0;
            }
            .cost-item {
                display: flex;
                justify-content: space-between;
                margin-bottom: 10px;
                padding: 8px 0;
                border-bottom: 1px solid #ddd;
            }
            .cost-item:last-child {
                border-bottom: none;
            }
            .total-cost {
                font-size: 18px;
                font-weight: bold;
                color: #0077cc;
                text-align: center;
                margin-top: 20px;
                padding: 15px;
                background: #e3f2fd;
                border-radius: 8px;
            }
            .sights-list {
                list-style: none;
                padding: 0;
            }
            .sights-list li {
                padding: 8px 0;
                border-bottom: 1px solid #eee;
            }
            .sights-list li:last-child {
                border-bottom: none;
            }
            .plan-footer { 
                background: #333; 
                color: white; 
                padding: 20px; 
                text-align: center; 
            }
            .plan-footer p { 
                margin: 5px 0; 
            }
            .important-info {
                background: #fff3cd;
                border: 1px solid #ffeaa7;
                border-radius: 8px;
                padding: 15px;
                margin: 20px 0;
            }
            .important-info h4 {
                color: #856404;
                margin-top: 0;
            }
            .important-info ul {
                margin: 10px 0;
                padding-left: 20px;
            }
            .important-info li {
                margin-bottom: 5px;
            }
        </style>
    </head>
    <body>
        <div class="plan-container">
            <div class="plan-header">
                <h1>✈️ TravelPlanner</h1>
                <p>Your Complete Travel Plan</p>
                <p><strong>Trip to ' . ucfirst($toCity) . '</strong></p>
            </div>
            
            <div class="plan-body">
                <div class="plan-section">
                    <h3>📋 Trip Overview</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">Destination</div>
                            <div class="info-value">' . ucfirst($toCity) . '</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Travel Type</div>
                            <div class="info-value">' . ($isInternational ? 'International' : 'Domestic') . '</div>
                        </div>
                    </div>
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">Duration</div>
                            <div class="info-value">' . $duration . ' days</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Travelers</div>
                            <div class="info-value">' . $travelers . ' person' . ($travelers > 1 ? 's' : '') . '</div>
                        </div>
                    </div>
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">Travel Style</div>
                            <div class="info-value">' . ucfirst($travelStyle) . '</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Currency</div>
                            <div class="info-value">' . $currency . '</div>
                        </div>
                    </div>
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">Start Date</div>
                            <div class="info-value">' . date('F j, Y', strtotime($startDate)) . '</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">End Date</div>
                            <div class="info-value">' . date('F j, Y', strtotime($endDate)) . '</div>
                        </div>
                    </div>
                </div>';
    
    if (!empty($fromCity)) {
        $pdfContent .= '
                <div class="plan-section">
                    <h3>✈️ Journey Details</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">From</div>
                            <div class="info-value">' . ucfirst($fromCity) . '</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">To</div>
                            <div class="info-value">' . ucfirst($toCity) . '</div>
                        </div>
                    </div>
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">Transport Mode</div>
                            <div class="info-value">' . ucfirst($sourceToDestMode) . '</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Cost per Person</div>
                            <div class="info-value">₹' . number_format($sourceToDestCost) . '</div>
                        </div>
                    </div>
                </div>';
    }
    
    $pdfContent .= '
                <div class="plan-section">
                    <h3>🏨 Accommodation</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">Rooms Needed</div>
                            <div class="info-value">' . $roomsNeeded . ' room' . ($roomsNeeded > 1 ? 's' : '') . ' for ' . $travelers . ' traveler' . ($travelers > 1 ? 's' : '') . '</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Daily Rate per Room</div>
                            <div class="info-value">₹' . number_format($cityData['hotel']) . '</div>
                        </div>
                    </div>
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">Total Hotel Cost</div>
                            <div class="info-value">₹' . number_format($totalHotelCost) . '</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Cost per Person</div>
                            <div class="info-value">₹' . number_format($hotelCostPerPerson) . '</div>
                        </div>
                    </div>
                </div>
                
                <div class="plan-section">
                    <h3>🍽️ Food & Dining</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">Daily Budget per Person</div>
                            <div class="info-value">₹' . number_format($cityData['food']) . '</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Total Food Cost per Person</div>
                            <div class="info-value">₹' . number_format($foodCost) . '</div>
                        </div>
                    </div>
                </div>
                
                <div class="plan-section">
                    <h3>🚗 Local Transportation</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">Daily Transport per Person</div>
                            <div class="info-value">₹' . number_format($cityData['transport']) . '</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Total Transport per Person</div>
                            <div class="info-value">₹' . number_format($localTransportCost) . '</div>
                        </div>
                    </div>
                </div>
                
                <div class="plan-section">
                    <h3>🎯 Popular Sights & Attractions</h3>
                    <ul class="sights-list">';
    
    foreach ($cityData['sights'] as $sight) {
        $costText = $sight['cost'] > 0 ? " (₹{$sight['cost']})" : " (Free)";
        $pdfContent .= '<li><strong>' . $sight['name'] . '</strong>' . $costText . '</li>';
    }
    
    $pdfContent .= '
                    </ul>
                    <p><strong>Total Sights Cost per Person: ₹' . number_format($sightsCost) . '</strong></p>
                </div>
                
                <div class="plan-section">
                    <h3>💰 Cost Breakdown</h3>
                    <div class="cost-breakdown">
                        <div class="cost-item">
                            <span>Cost per Person:</span>
                        </div>';
    
    if (!empty($fromCity)) {
        $pdfContent .= '
                        <div class="cost-item">
                            <span>Journey to Destination:</span>
                            <span>₹' . number_format($sourceToDestCost) . '</span>
                        </div>';
    }
    
    $pdfContent .= '
                        <div class="cost-item">
                            <span>Accommodation:</span>
                            <span>₹' . number_format($hotelCostPerPerson) . '</span>
                        </div>
                        <div class="cost-item">
                            <span>Food & Dining:</span>
                            <span>₹' . number_format($foodCost) . '</span>
                        </div>
                        <div class="cost-item">
                            <span>Local Transportation:</span>
                            <span>₹' . number_format($localTransportCost) . '</span>
                        </div>
                        <div class="cost-item">
                            <span>Sights & Attractions:</span>
                            <span>₹' . number_format($sightsCost) . '</span>
                        </div>
                        <div class="total-cost">
                            Total per Person: ₹' . number_format($totalCostPerPerson) . '
                        </div>
                        <div class="total-cost">
                            Total for ' . $travelers . ' Traveler' . ($travelers > 1 ? 's' : '') . ': ₹' . number_format($totalCostForAll) . '
                        </div>
                    </div>
                </div>
                
                <div class="plan-section">
                    <h3>📋 Important Information</h3>
                    <div class="important-info">
                        <h4>Required Documents:</h4>
                        <ul>
                            <li>' . ($isInternational ? 'Valid passport with at least 6 months validity' : 'Valid ID proof (Aadhar Card, PAN Card, Driving License)') . '</li>
                            <li>' . ($isInternational ? 'Visa (if required for your nationality)' : 'Any government-issued photo ID') . '</li>
                            <li>Travel insurance (recommended)</li>
                            <li>Emergency contact information</li>
                        </ul>
                        
                        <h4>Travel Tips:</h4>
                        <ul>
                            <li>Check local weather conditions before travel</li>
                            <li>Book accommodations in advance</li>
                            <li>Carry local currency for small expenses</li>
                            <li>Keep emergency contact numbers handy</li>
                            <li>Respect local customs and traditions</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="plan-footer">
                <p><strong>Thank you for choosing TravelPlanner!</strong></p>
                <p>Have a safe and enjoyable journey</p>
                <p>Generated on: ' . date('F j, Y \a\t g:i A') . '</p>
                <p>For support: +91 9130123270 | sarveshtravelplanner@gmail.com</p>
            </div>
        </div>
    </body>
    </html>';
    
    // Create temp directory if it doesn't exist
    $tempDir = __DIR__ . '/../vendor/mpdf/mpdf/tmp';
    if (!is_dir($tempDir)) {
        mkdir($tempDir, 0777, true);
    }
    
    // Generate PDF using mPDF
    $mpdf = new \Mpdf\Mpdf([
        'tempDir' => $tempDir,
        'mode' => 'utf-8',
        'format' => 'A4',
        'margin_left' => 15,
        'margin_right' => 15,
        'margin_top' => 15,
        'margin_bottom' => 15
    ]);
    
    $mpdf->WriteHTML($pdfContent);
    $mpdf->Output('TravelPlanner_Plan_' . ucfirst($toCity) . '.pdf', 'D');
    
} catch (Exception $e) {
    // If PDF generation fails, return error
    http_response_code(500);
    echo json_encode(['error' => 'PDF generation failed: ' . $e->getMessage()]);
}
?> 