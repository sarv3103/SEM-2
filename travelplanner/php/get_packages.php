<?php
// php/get_packages.php - Serves package data as JSON
header('Content-Type: application/json');

// Sample package data (in a real app, this would come from a database)
$domesticPackages = [
    [
        'id' => 1,
        'name' => 'Kerala Backwaters',
        'duration' => '5 days',
        'cities' => ['Kochi', 'Alleppey', 'Munnar'],
        'price_per_person' => 25000,
        'meals' => 'Breakfast & Dinner',
        'hotels' => '3-star',
        'travel_mode' => 'Flight + Cab',
        'image' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
        'description' => 'Experience the serene backwaters of Kerala, stay in a houseboat, and explore the beautiful hill stations.'
    ],
    [
        'id' => 2,
        'name' => 'Rajasthan Heritage',
        'duration' => '7 days',
        'cities' => ['Jaipur', 'Udaipur', 'Jodhpur'],
        'price_per_person' => 35000,
        'meals' => 'All Meals',
        'hotels' => '4-star',
        'travel_mode' => 'Flight + Cab',
        'image' => 'https://images.unsplash.com/photo-1596178065887-1198b6148b2b?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
        'description' => 'Explore the royal heritage of Rajasthan, visit magnificent palaces and forts.'
    ],
    [
        'id' => 3,
        'name' => 'Goa Beach Holiday',
        'duration' => '4 days',
        'cities' => ['North Goa', 'South Goa'],
        'price_per_person' => 20000,
        'meals' => 'Breakfast',
        'hotels' => '3-star',
        'travel_mode' => 'Flight',
        'image' => 'https://images.unsplash.com/photo-1544551763-46a013bb70d5?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
        'description' => 'Enjoy the beaches, water sports, and vibrant nightlife of Goa.'
    ],
    [
        'id' => 4,
        'name' => 'Mumbai City Lights',
        'duration' => '3 days',
        'cities' => ['Mumbai'],
        'price_per_person' => 18000,
        'meals' => 'Breakfast',
        'hotels' => '4-star',
        'travel_mode' => 'Flight',
        'image' => 'https://images.unsplash.com/photo-1502086223501-7ea6ecd79368?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
        'description' => 'Explore the vibrant city of Mumbai, Gateway of India, Marine Drive, Bollywood, and more.'
    ],
    [
        'id' => 5,
        'name' => 'Delhi Historical Tour',
        'duration' => '4 days',
        'cities' => ['Delhi'],
        'price_per_person' => 21000,
        'meals' => 'Breakfast & Dinner',
        'hotels' => '4-star',
        'travel_mode' => 'Flight + Cab',
        'image' => 'https://images.unsplash.com/photo-1524492412937-b28074a5d7da?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
        'description' => 'Visit Red Fort, Qutub Minar, India Gate, Lotus Temple, and more.'
    ],
    [
        'id' => 6,
        'name' => 'Udaipur Lake Retreat',
        'duration' => '3 days',
        'cities' => ['Udaipur'],
        'price_per_person' => 19500,
        'meals' => 'Breakfast',
        'hotels' => 'Lake View Hotel',
        'travel_mode' => 'Flight + Cab',
        'image' => 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
        'description' => 'Enjoy the City of Lakes, palaces, and romantic boat rides.'
    ],
    [
        'id' => 7,
        'name' => 'Varanasi Spiritual Experience',
        'duration' => '3 days',
        'cities' => ['Varanasi'],
        'price_per_person' => 17000,
        'meals' => 'Breakfast',
        'hotels' => '3-star',
        'travel_mode' => 'Train + Cab',
        'image' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
        'description' => 'Witness the Ganga Aarti, explore ancient temples, and ghats.'
    ],
    [
        'id' => 8,
        'name' => 'Shimla & Manali Hills',
        'duration' => '6 days',
        'cities' => ['Shimla', 'Manali'],
        'price_per_person' => 26000,
        'meals' => 'Breakfast & Dinner',
        'hotels' => '3-star',
        'travel_mode' => 'Train + Cab',
        'image' => 'https://images.unsplash.com/photo-1502086223501-7ea6ecd79368?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
        'description' => 'Snow-capped mountains, Mall Road, adventure sports, and more.'
    ],
    [
        'id' => 9,
        'name' => 'Ooty Green Escape',
        'duration' => '4 days',
        'cities' => ['Ooty'],
        'price_per_person' => 19000,
        'meals' => 'Breakfast',
        'hotels' => 'Hill Station Hotel',
        'travel_mode' => 'Train + Cab',
        'image' => 'https://images.unsplash.com/photo-1504609813440-554e64a8f005?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
        'description' => 'Tea gardens, botanical gardens, and cool weather.'
    ]
];

$internationalPackages = [
    [
        'id' => 10,
        'name' => 'Paris City Lights',
        'duration' => '5 days',
        'cities' => ['Paris'],
        'price_per_person' => 80000,
        'meals' => 'Breakfast',
        'hotels' => '4-star',
        'travel_mode' => 'Flight',
        'image' => 'https://images.unsplash.com/photo-1502602898657-3e91760cbb34?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
        'description' => 'Eiffel Tower, Louvre, Seine cruise, and more.'
    ],
    [
        'id' => 11,
        'name' => 'London Royal Tour',
        'duration' => '5 days',
        'cities' => ['London'],
        'price_per_person' => 85000,
        'meals' => 'Breakfast',
        'hotels' => '4-star',
        'travel_mode' => 'Flight',
        'image' => 'https://images.unsplash.com/photo-1464983953574-0892a716854b?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
        'description' => 'Big Ben, Buckingham Palace, London Eye, and more.'
    ],
    [
        'id' => 12,
        'name' => 'New York Explorer',
        'duration' => '6 days',
        'cities' => ['New York'],
        'price_per_person' => 90000,
        'meals' => 'Breakfast',
        'hotels' => '4-star',
        'travel_mode' => 'Flight',
        'image' => 'https://images.unsplash.com/photo-1469474968028-56623f02e42e?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
        'description' => 'Statue of Liberty, Times Square, Central Park, and more.'
    ],
    [
        'id' => 13,
        'name' => 'Tokyo Modern Marvels',
        'duration' => '5 days',
        'cities' => ['Tokyo'],
        'price_per_person' => 95000,
        'meals' => 'Breakfast',
        'hotels' => '4-star',
        'travel_mode' => 'Flight',
        'image' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
        'description' => 'Shibuya, Tokyo Tower, cherry blossoms, and more.'
    ],
    [
        'id' => 14,
        'name' => 'Dubai Luxury Escape',
        'duration' => '4 days',
        'cities' => ['Dubai'],
        'price_per_person' => 70000,
        'meals' => 'Breakfast',
        'hotels' => '5-star',
        'travel_mode' => 'Flight',
        'image' => 'https://www.gibsons.co.uk/wp-content/uploads/2023/12/Dubai-400x500.jpg',
        'description' => 'Burj Khalifa, desert safari, shopping, and more.'
    ],
    [
        'id' => 15,
        'name' => 'Singapore City Fun',
        'duration' => '5 days',
        'cities' => ['Singapore'],
        'price_per_person' => 82000,
        'meals' => 'Breakfast',
        'hotels' => '4-star',
        'travel_mode' => 'Flight',
        'image' => 'https://images.unsplash.com/photo-1512453979798-5ea266f8880c?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
        'description' => 'Marina Bay Sands, Gardens by the Bay, Universal Studios.'
    ],
    [
        'id' => 16,
        'name' => 'Bangkok & Pattaya',
        'duration' => '5 days',
        'cities' => ['Bangkok', 'Pattaya'],
        'price_per_person' => 65000,
        'meals' => 'Breakfast',
        'hotels' => '4-star',
        'travel_mode' => 'Flight',
        'image' => 'https://images.unsplash.com/photo-1509228468518-180dd4864904?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
        'description' => 'Temples, floating markets, nightlife, and beaches.'
    ],
    [
        'id' => 17,
        'name' => 'Bali Island Paradise',
        'duration' => '6 days',
        'cities' => ['Bali'],
        'price_per_person' => 78000,
        'meals' => 'Breakfast',
        'hotels' => '4-star',
        'travel_mode' => 'Flight',
        'image' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
        'description' => 'Beaches, temples, rice terraces, and spa experiences.'
    ]
];

// If a city is specified, filter packages by that city
if (isset($_GET['city'])) {
    $city = strtolower($_GET['city']);
    $filteredDomestic = array_filter($domesticPackages, function($pkg) use ($city) {
        return in_array($city, array_map('strtolower', $pkg['cities']));
    });
    $filteredInternational = array_filter($internationalPackages, function($pkg) use ($city) {
        return in_array($city, array_map('strtolower', $pkg['cities']));
    });
    $response = [
        'domestic' => array_values($filteredDomestic),
        'international' => array_values($filteredInternational)
    ];
} else {
    $response = [
        'domestic' => $domesticPackages,
        'international' => $internationalPackages
    ];
}

// Return the package data as JSON
echo json_encode($response); 