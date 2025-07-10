<?php
// php/planner.php - Enhanced Trip planning functionality with source-destination costs
require_once 'config.php';
require_once __DIR__ . '/../vendor/autoload.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $from_city = $_POST['from_city'] ?? '';
    $to_city = $_POST['to_city'] ?? '';
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';
    $travelers = intval($_POST['travelers'] ?? 1);
    $travel_style = $_POST['travel_style'] ?? 'standard';
    $preferred_transport = $_POST['preferred_transport'] ?? 'any';
    $currency = $_POST['currency'] ?? 'INR';

    if (empty($to_city) || empty($start_date) || empty($end_date)) {
        echo json_encode(['status' => 'error', 'msg' => 'Please fill in all required fields']);
        exit;
    }

    // Validate dates - ensure they are not in the past
    $today = new DateTime();
    $today->setTime(0, 0, 0); // Set to start of day
    
    $start = new DateTime($start_date);
    $start->setTime(0, 0, 0);
    
    $end = new DateTime($end_date);
    $end->setTime(0, 0, 0);
    
    // Check if start date is in the past
    if ($start < $today) {
        echo json_encode(['status' => 'error', 'msg' => 'Start date cannot be in the past. Please select today or a future date.']);
        exit;
    }
    
    // Check if end date is in the past
    if ($end < $today) {
        echo json_encode(['status' => 'error', 'msg' => 'End date cannot be in the past. Please select today or a future date.']);
        exit;
    }

    // Calculate trip duration
    $duration = $start->diff($end)->days;

    if ($duration <= 0) {
        echo json_encode(['status' => 'error', 'msg' => 'End date must be after start date']);
        exit;
    }

    // Define international destinations
    $internationalDestinations = ['paris', 'london', 'new_york', 'tokyo', 'dubai', 'singapore', 'bangkok', 'bali'];
    $isInternational = in_array(strtolower($to_city), $internationalDestinations);

    // City plans data - Expanded database
    $cityPlans = [
        'paris' => [
            'sights' => [
                ['name' => 'Eiffel Tower', 'cost' => 25],
                ['name' => 'Louvre Museum', 'cost' => 17],
                ['name' => 'Notre-Dame Cathedral', 'cost' => 0],
                ['name' => 'Montmartre', 'cost' => 0],
                ['name' => 'Seine River Cruise', 'cost' => 15],
                ['name' => 'Palace of Versailles', 'cost' => 20],
                ['name' => 'Sainte-Chapelle', 'cost' => 10]
            ],
            'hotel' => 12000,
            'food' => 2500,
            'transport' => 1000
        ],
        'london' => [
            'sights' => [
                ['name' => 'London Eye', 'cost' => 30],
                ['name' => 'British Museum', 'cost' => 0],
                ['name' => 'Tower of London', 'cost' => 25],
                ['name' => 'Buckingham Palace', 'cost' => 0],
                ['name' => 'Westminster Abbey', 'cost' => 20],
                ['name' => "St. Paul's Cathedral", 'cost' => 18],
                ['name' => 'Hyde Park', 'cost' => 0]
            ],
            'hotel' => 11000,
            'food' => 2400,
            'transport' => 900
        ],
        'new_york' => [
            'sights' => [
                ['name' => 'Statue of Liberty', 'cost' => 20],
                ['name' => 'Central Park', 'cost' => 0],
                ['name' => 'Empire State Building', 'cost' => 38],
                ['name' => 'Metropolitan Museum of Art', 'cost' => 25],
                ['name' => 'Times Square', 'cost' => 0],
                ['name' => 'Brooklyn Bridge', 'cost' => 0],
                ['name' => '9/11 Memorial & Museum', 'cost' => 26]
            ],
            'hotel' => 13000,
            'food' => 3000,
            'transport' => 1000
        ],
        'tokyo' => [
            'sights' => [
                ['name' => 'Tokyo Skytree', 'cost' => 20],
                ['name' => 'Senso-ji Temple', 'cost' => 0],
                ['name' => 'Meiji Shrine', 'cost' => 0],
                ['name' => 'Tokyo Tower', 'cost' => 15],
                ['name' => 'Ueno Zoo', 'cost' => 6],
                ['name' => 'Shinjuku Gyoen National Garden', 'cost' => 5],
                ['name' => 'Odaiba', 'cost' => 0]
            ],
            'hotel' => 10000,
            'food' => 2200,
            'transport' => 800
        ],
        'dubai' => [
            'sights' => [
                ['name' => 'Burj Khalifa', 'cost' => 40],
                ['name' => 'Palm Jumeirah', 'cost' => 0],
                ['name' => 'Dubai Mall', 'cost' => 0],
                ['name' => 'Desert Safari', 'cost' => 60],
                ['name' => 'Dubai Fountain', 'cost' => 0]
            ],
            'hotel' => 14000,
            'food' => 2600,
            'transport' => 1000
        ],
        'singapore' => [
            'sights' => [
                ['name' => 'Marina Bay Sands', 'cost' => 30],
                ['name' => 'Gardens by the Bay', 'cost' => 20],
                ['name' => 'Universal Studios', 'cost' => 70],
                ['name' => 'Sentosa Island', 'cost' => 40],
                ['name' => 'Singapore Zoo', 'cost' => 35]
            ],
            'hotel' => 12000,
            'food' => 2200,
            'transport' => 800
        ],
        'bangkok' => [
            'sights' => [
                ['name' => 'Grand Palace', 'cost' => 15],
                ['name' => 'Wat Phra Kaew', 'cost' => 0],
                ['name' => 'Wat Arun', 'cost' => 5],
                ['name' => 'Chatuchak Market', 'cost' => 0],
                ['name' => 'Floating Market', 'cost' => 20]
            ],
            'hotel' => 8000,
            'food' => 1500,
            'transport' => 600
        ],
        'bali' => [
            'sights' => [
                ['name' => 'Tanah Lot Temple', 'cost' => 10],
                ['name' => 'Ubud Monkey Forest', 'cost' => 8],
                ['name' => 'Mount Batur', 'cost' => 25],
                ['name' => 'Rice Terraces', 'cost' => 0],
                ['name' => 'Beach Clubs', 'cost' => 30]
            ],
            'hotel' => 7000,
            'food' => 1200,
            'transport' => 500
        ],
        'mumbai' => [
            'sights' => [
                ['name' => 'Gateway of India', 'cost' => 0],
                ['name' => 'Marine Drive', 'cost' => 0],
                ['name' => 'Juhu Beach', 'cost' => 0],
                ['name' => 'Elephanta Caves', 'cost' => 10],
                ['name' => 'Bollywood Studio Tour', 'cost' => 25]
            ],
            'hotel' => 4000,
            'food' => 800,
            'transport' => 400
        ],
        'delhi' => [
            'sights' => [
                ['name' => 'Red Fort', 'cost' => 15],
                ['name' => 'Qutub Minar', 'cost' => 10],
                ['name' => 'India Gate', 'cost' => 0],
                ['name' => 'Humayun\'s Tomb', 'cost' => 10],
                ['name' => 'Lotus Temple', 'cost' => 0]
            ],
            'hotel' => 3500,
            'food' => 700,
            'transport' => 350
        ],
        'jaipur' => [
            'sights' => [
                ['name' => 'Amber Fort', 'cost' => 20],
                ['name' => 'City Palace', 'cost' => 15],
                ['name' => 'Hawa Mahal', 'cost' => 10],
                ['name' => 'Jantar Mantar', 'cost' => 10],
                ['name' => 'Nahargarh Fort', 'cost' => 5]
            ],
            'hotel' => 3000,
            'food' => 600,
            'transport' => 300
        ],
        'goa' => [
            'sights' => [
                ['name' => 'Calangute Beach', 'cost' => 0],
                ['name' => 'Baga Beach', 'cost' => 0],
                ['name' => 'Fort Aguada', 'cost' => 25],
                ['name' => 'Basilica of Bom Jesus', 'cost' => 0],
                ['name' => 'Spice Plantation', 'cost' => 800]
            ],
            'hotel' => 3500,
            'food' => 900,
            'transport' => 500
        ],
        'kerala' => [
            'sights' => [
                ['name' => 'Alleppey Backwaters', 'cost' => 0],
                ['name' => 'Munnar Tea Gardens', 'cost' => 0],
                ['name' => 'Kumarakom Bird Sanctuary', 'cost' => 50],
                ['name' => 'Fort Kochi', 'cost' => 0],
                ['name' => 'Varkala Beach', 'cost' => 0]
            ],
            'hotel' => 3000,
            'food' => 800,
            'transport' => 500
        ],
        'udaipur' => [
            'sights' => [
                ['name' => 'Lake Palace', 'cost' => 0],
                ['name' => 'City Palace', 'cost' => 15],
                ['name' => 'Jag Mandir', 'cost' => 10],
                ['name' => 'Sajjangarh Palace', 'cost' => 5],
                ['name' => 'Fateh Sagar Lake', 'cost' => 0]
            ],
            'hotel' => 2800,
            'food' => 600,
            'transport' => 300
        ],
        'varanasi' => [
            'sights' => [
                ['name' => 'Ganga Aarti', 'cost' => 0],
                ['name' => 'Kashi Vishwanath Temple', 'cost' => 0],
                ['name' => 'Sarnath', 'cost' => 10],
                ['name' => 'Ghats', 'cost' => 0],
                ['name' => 'Banaras Hindu University', 'cost' => 0]
            ],
            'hotel' => 2500,
            'food' => 500,
            'transport' => 300
        ],
        'shimla' => [
            'sights' => [
                ['name' => 'Mall Road', 'cost' => 0],
                ['name' => 'Kufri', 'cost' => 15],
                ['name' => 'Jakhu Temple', 'cost' => 0],
                ['name' => 'Christ Church', 'cost' => 0],
                ['name' => 'Viceregal Lodge', 'cost' => 20]
            ],
            'hotel' => 3500,
            'food' => 700,
            'transport' => 400
        ],
        'manali' => [
            'sights' => [
                ['name' => 'Hadimba Temple', 'cost' => 0],
                ['name' => 'Solang Valley', 'cost' => 25],
                ['name' => 'Rohtang Pass', 'cost' => 30],
                ['name' => 'Old Manali', 'cost' => 0],
                ['name' => 'Mall Road', 'cost' => 0]
            ],
            'hotel' => 3200,
            'food' => 650,
            'transport' => 350
        ],
        'ooty' => [
            'sights' => [
                ['name' => 'Ooty Lake', 'cost' => 10],
                ['name' => 'Botanical Gardens', 'cost' => 5],
                ['name' => 'Doddabetta Peak', 'cost' => 0],
                ['name' => 'Tea Museum', 'cost' => 15],
                ['name' => 'Rose Garden', 'cost' => 8]
            ],
            'hotel' => 3000,
            'food' => 600,
            'transport' => 300
        ]
    ];

    // Check if destination city exists (case insensitive)
    $cityFound = false;
    $actualCityKey = '';
    
    foreach ($cityPlans as $key => $data) {
        if (strtolower($to_city) === strtolower($key)) {
            $cityFound = true;
            $actualCityKey = $key;
            break;
        }
    }

    if (!$cityFound) {
        echo json_encode(['status' => 'error', 'msg' => 'Destination city "' . htmlspecialchars($to_city) . '" not found in our database. Available cities: ' . implode(', ', array_keys($cityPlans))]);
        exit;
    }

    $cityData = $cityPlans[$actualCityKey];
    
    // Calculate costs based on travel style
    $styleMultiplier = [
        'budget' => 0.7,
        'standard' => 1.0,
        'luxury' => 1.5
    ];
    
    $multiplier = $styleMultiplier[$travel_style] ?? 1.0;
    
    // Calculate source to destination travel costs
    $sourceToDestCost = 0;
    $sourceToDestMode = '';
    
    if (!empty($from_city)) {
        // Determine transport mode based on preference and route type
        if ($isInternational) {
            $sourceToDestMode = 'flight';
            $sourceToDestCost = 25000; // International flight base cost
        } else {
            // Domestic travel
            switch ($preferred_transport) {
                case 'flight':
                    $sourceToDestMode = 'flight';
                    $sourceToDestCost = 3000;
                    break;
                case 'train':
                    $sourceToDestMode = 'train';
                    $sourceToDestCost = 800;
                    break;
                case 'bus':
                    $sourceToDestMode = 'bus';
                    $sourceToDestCost = 500;
                    break;
                default:
                    // Default to cheapest option
                    $sourceToDestMode = 'bus';
                    $sourceToDestCost = 500;
                    break;
            }
        }
        
        // Apply travel style multiplier to transport cost
        $sourceToDestCost = $sourceToDestCost * $multiplier;
    }
    
    // Calculate destination costs with realistic hotel pricing
    // Hotel cost is per room, not per person
    $roomsNeeded = ($travelers <= 4) ? 1 : 2; // 1 room for up to 4 travelers, 2 rooms for more
    $hotelCostPerRoom = $cityData['hotel'] * $duration * $multiplier;
    $totalHotelCost = $hotelCostPerRoom * $roomsNeeded;
    $hotelCostPerPerson = $totalHotelCost / $travelers; // For cost breakdown display
    
    $foodCost = $cityData['food'] * $duration;
    $localTransportCost = $cityData['transport'] * $duration * $multiplier;
    
    $sightsCost = 0;
    foreach ($cityData['sights'] as $sight) {
        $sightsCost += $sight['cost'];
    }
    
    // Calculate costs per person
    $costPerPerson = [
        'source_to_dest' => $sourceToDestCost,
        'hotel' => $hotelCostPerPerson, // This is now the per-person share of hotel cost
        'food' => $foodCost,
        'local_transport' => $localTransportCost,
        'sights' => $sightsCost
    ];
    
    $totalCostPerPerson = array_sum($costPerPerson);
    $totalCostForAll = $totalCostPerPerson * $travelers;
    
    // Generate HTML for the plan
    $html = "
    <div class='plan-header'>
        <h3>Your Trip to " . ucfirst($actualCityKey) . "</h3>
        <p>Duration: {$duration} days | Travelers: {$travelers} | Style: " . ucfirst($travel_style) . "</p>
        <p>Travel Type: " . ($isInternational ? 'International' : 'Domestic') . "</p>
    </div>
    <div class='plan-content'>";
    
    if (!empty($from_city)) {
        $html .= "
        <div class='plan-section'>
            <h4>✈️ Journey from " . ucfirst($from_city) . " to " . ucfirst($actualCityKey) . "</h4>
            <p>Transport Mode: " . ucfirst($sourceToDestMode) . "</p>
            <p>Cost per person: ₹" . number_format($sourceToDestCost) . "</p>
            <p>Total journey cost: ₹" . number_format($sourceToDestCost * $travelers) . "</p>
        </div>";
    }
    
    $html .= "
        <div class='plan-section'>
            <h4>🏨 Accommodation</h4>
            <p>Rooms needed: {$roomsNeeded} " . ($roomsNeeded == 1 ? 'room' : 'rooms') . " for {$travelers} traveler" . ($travelers > 1 ? 's' : '') . "</p>
            <p>Hotel cost per room per night: ₹" . number_format($cityData['hotel'] * $multiplier) . "</p>
            <p>Total hotel cost for {$duration} days: ₹" . number_format($totalHotelCost) . "</p>
            <p>Hotel cost per person: ₹" . number_format($hotelCostPerPerson) . "</p>
            <p>Based on {$travel_style} style accommodation</p>
        </div>
        <div class='plan-section'>
            <h4>🍽️ Food & Dining</h4>
            <p>Daily food budget per person: ₹" . number_format($cityData['food']) . "</p>
            <p>Total food cost per person: ₹" . number_format($foodCost) . "</p>
            <p>Total food cost for all: ₹" . number_format($foodCost * $travelers) . "</p>
        </div>
        <div class='plan-section'>
            <h4>🚗 Local Transportation</h4>
            <p>Daily local transport per person: ₹" . number_format($cityData['transport']) . "</p>
            <p>Total local transport per person: ₹" . number_format($localTransportCost) . "</p>
            <p>Total local transport for all: ₹" . number_format($localTransportCost * $travelers) . "</p>
        </div>
        <div class='plan-section sights-list'>
            <h4>🎯 Popular Sights</h4>
            <ul>";
    
    foreach ($cityData['sights'] as $sight) {
        $costText = $sight['cost'] > 0 ? " (₹{$sight['cost']})" : " (Free)";
        $html .= "<li>{$sight['name']}{$costText}</li>";
    }
    
    $html .= "</ul>
            <p>Total sights cost per person: ₹" . number_format($sightsCost) . "</p>
            <p>Total sights cost for all: ₹" . number_format($sightsCost * $travelers) . "</p>
        </div>
        <div class='plan-section cost-breakdown'>
            <h4>💰 Cost Breakdown Per Person</h4>
            <ul>";
    
    if (!empty($from_city)) {
        $html .= "<li>Journey to destination: ₹" . number_format($sourceToDestCost) . "</li>";
    }
    $html .= "
                <li>Hotel: ₹" . number_format($hotelCostPerPerson) . "</li>
                <li>Food: ₹" . number_format($foodCost) . "</li>
                <li>Local Transport: ₹" . number_format($localTransportCost) . "</li>
                <li>Sights: ₹" . number_format($sightsCost) . "</li>
                <li class='total'>Total per person: ₹" . number_format($totalCostPerPerson) . "</li>
            </ul>
            <h4>💰 Total Cost for {$travelers} Traveler" . ($travelers > 1 ? 's' : '') . "</h4>
            <p class='grand-total'>₹" . number_format($totalCostForAll) . "</p>
        </div>
        <div class='plan-section'>
            <h4>📋 Important Information</h4>
            <ul>
                <li>Currency: {$currency}</li>
                <li>Best Time to Visit: Check local weather conditions</li>
                <li>Required Documents: " . ($isInternational ? 'Valid passport, visa (if required)' : 'Valid ID proof (Aadhar, PAN, Driving License)') . "</li>
                <li>Emergency Contact: Local emergency services</li>
            </ul>
        </div>
    </div>";

    echo json_encode([
        'status' => 'success',
        'html' => $html,
        'total_cost_per_person' => $totalCostPerPerson,
        'total_cost_for_all' => $totalCostForAll,
        'is_international' => $isInternational,
        'plan_data' => [
            'from_city' => $from_city,
            'to_city' => $actualCityKey,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'travelers' => $travelers,
            'travel_style' => $travel_style,
            'is_international' => $isInternational,
            'total_cost_per_person' => $totalCostPerPerson,
            'total_cost_for_all' => $totalCostForAll,
            'source_to_dest_cost' => $sourceToDestCost,
            'source_to_dest_mode' => $sourceToDestMode,
            'currency' => $currency,
            'city_data' => $cityData,
            'duration' => $duration
        ]
    ]);

} else {
    echo json_encode(['status' => 'error', 'msg' => 'Invalid request method']);
}
?>
