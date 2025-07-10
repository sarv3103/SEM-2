<?php
// php/create_destinations_table.php - Create destinations table
require_once 'config.php';

// Create destinations table
$sql = "CREATE TABLE IF NOT EXISTS `destinations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `image` varchar(500) NOT NULL,
  `category` enum('beach','mountain','city','culture','wildlife') NOT NULL,
  `features` text NOT NULL,
  `price` varchar(100) NOT NULL,
  `duration` varchar(50) DEFAULT NULL,
  `difficulty` enum('easy','moderate','challenging') DEFAULT 'easy',
  `best_time` varchar(100) DEFAULT NULL,
  `highlights` text DEFAULT NULL,
  `included` text DEFAULT NULL,
  `excluded` text DEFAULT NULL,
  `itinerary` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `category` (`category`),
  KEY `status` (`status`),
  KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

if ($conn->query($sql)) {
    echo "✅ Destinations table created successfully\n";
    
    // Insert sample destinations
    $sampleDestinations = [
        [
            'name' => 'Goa',
            'location' => 'India',
            'description' => 'Famous for its pristine beaches, vibrant nightlife, and Portuguese heritage. Perfect for beach lovers and party enthusiasts.',
            'image' => 'https://th.bing.com/th/id/OIP.OsdnDjdW74sn01vHghKvOwHaFj?w=221&h=180&c=7&r=0&o=7&dpr=1.3&pid=1.7&rm=3',
            'category' => 'beach',
            'features' => 'Beaches,Nightlife,Heritage',
            'price' => 'From ₹15,000',
            'duration' => '5-7 days',
            'difficulty' => 'easy',
            'best_time' => 'November to March',
            'highlights' => 'Calangute Beach, Basilica of Bom Jesus, Fort Aguada, Anjuna Flea Market',
            'included' => 'Hotel accommodation, Breakfast, Airport transfers, Local sightseeing',
            'excluded' => 'Airfare, Lunch & Dinner, Personal expenses, Optional activities',
            'itinerary' => 'Day 1: Arrival & Beach visit, Day 2: Old Goa churches, Day 3: Fort Aguada & cruise, Day 4: Spice plantation, Day 5: Shopping & departure'
        ],
        [
            'name' => 'Kerala',
            'location' => 'India',
            'description' => 'God\'s Own Country with serene backwaters, lush tea gardens, and Ayurvedic wellness retreats.',
            'image' => 'https://th.bing.com/th/id/OIP.KX8jyV-N7KMbHfH6V64vwQHaDk?w=337&h=168&c=7&r=0&o=7&dpr=1.3&pid=1.7&rm=3',
            'category' => 'culture',
            'features' => 'Backwaters,Ayurveda,Tea Gardens',
            'price' => 'From ₹18,000',
            'duration' => '6-8 days',
            'difficulty' => 'easy',
            'best_time' => 'September to March',
            'highlights' => 'Alleppey Backwaters, Munnar Tea Gardens, Kumarakom Bird Sanctuary, Ayurvedic treatments',
            'included' => 'Houseboat stay, Tea estate visit, Ayurvedic massage, Local guide',
            'excluded' => 'Airfare, Meals, Personal expenses, Optional activities',
            'itinerary' => 'Day 1: Kochi arrival, Day 2: Munnar tea gardens, Day 3: Thekkady wildlife, Day 4-5: Alleppey backwaters, Day 6: Kumarakom, Day 7: Departure'
        ],
        [
            'name' => 'Ladakh',
            'location' => 'India',
            'description' => 'High-altitude desert with stunning landscapes, ancient monasteries, and adventure activities.',
            'image' => 'https://th.bing.com/th/id/OIP.kaN4VnvSSguxcYILikQ71wHaEj?w=280&h=180&c=7&r=0&o=7&dpr=1.3&pid=1.7&rm=3',
            'category' => 'mountain',
            'features' => 'Mountains,Monasteries,Adventure',
            'price' => 'From ₹25,000',
            'duration' => '7-10 days',
            'difficulty' => 'moderate',
            'best_time' => 'June to September',
            'highlights' => 'Pangong Lake, Khardungla Pass, Thiksey Monastery, Nubra Valley',
            'included' => 'Hotel accommodation, All meals, Oxygen support, Local guide',
            'excluded' => 'Airfare, Personal expenses, Optional activities, Medical insurance',
            'itinerary' => 'Day 1: Leh arrival & acclimatization, Day 2: Local monasteries, Day 3: Khardungla Pass, Day 4-5: Nubra Valley, Day 6-7: Pangong Lake, Day 8: Departure'
        ],
        [
            'name' => 'Rajasthan',
            'location' => 'India',
            'description' => 'Land of kings with magnificent forts, palaces, and rich cultural heritage.',
            'image' => 'https://th.bing.com/th/id/OIP.tUcUdXBmUOKEZkHZiCgUagHaD2?w=346&h=180&c=7&r=0&o=7&dpr=1.3&pid=1.7&rm=3',
            'category' => 'culture',
            'features' => 'Forts,Palaces,Heritage',
            'price' => 'From ₹20,000',
            'duration' => '8-12 days',
            'difficulty' => 'easy',
            'best_time' => 'October to March',
            'highlights' => 'Amber Fort, City Palace Udaipur, Jaisalmer Fort, Pushkar Lake',
            'included' => 'Hotel accommodation, Breakfast & Dinner, Local guide, Cultural shows',
            'excluded' => 'Airfare, Lunch, Personal expenses, Optional activities',
            'itinerary' => 'Day 1: Jaipur arrival, Day 2-3: Jaipur sightseeing, Day 4-5: Jodhpur, Day 6-7: Udaipur, Day 8-9: Jaisalmer, Day 10: Pushkar, Day 11: Departure'
        ],
        [
            'name' => 'Andaman Islands',
            'location' => 'India',
            'description' => 'Tropical paradise with crystal clear waters, coral reefs, and pristine beaches.',
            'image' => 'https://th.bing.com/th/id/OIP.Sj6RcXpyntkb6oQsIw7duAHaFd?w=265&h=195&c=7&r=0&o=7&dpr=1.3&pid=1.7&rm=3',
            'category' => 'beach',
            'features' => 'Beaches,Water Sports,Islands',
            'price' => 'From ₹30,000',
            'duration' => '6-8 days',
            'difficulty' => 'easy',
            'best_time' => 'October to May',
            'highlights' => 'Radhanagar Beach, Cellular Jail, Ross Island, Scuba diving',
            'included' => 'Hotel accommodation, Breakfast, Ferry tickets, Water sports',
            'excluded' => 'Airfare, Lunch & Dinner, Personal expenses, Optional activities',
            'itinerary' => 'Day 1: Port Blair arrival, Day 2: Cellular Jail, Day 3: Ross Island, Day 4-5: Havelock Island, Day 6: Neil Island, Day 7: Return to Port Blair, Day 8: Departure'
        ]
    ];
    
    $stmt = $conn->prepare("INSERT INTO destinations (name, location, description, image, category, features, price, duration, difficulty, best_time, highlights, included, excluded, itinerary) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    foreach ($sampleDestinations as $dest) {
        $stmt->bind_param("ssssssssssssss", 
            $dest['name'], $dest['location'], $dest['description'], $dest['image'], 
            $dest['category'], $dest['features'], $dest['price'], $dest['duration'], 
            $dest['difficulty'], $dest['best_time'], $dest['highlights'], 
            $dest['included'], $dest['excluded'], $dest['itinerary']
        );
        
        if ($stmt->execute()) {
            echo "✅ Added destination: {$dest['name']}\n";
        } else {
            echo "❌ Failed to add destination: {$dest['name']} - " . $stmt->error . "\n";
        }
    }
    
} else {
    echo "❌ Error creating destinations table: " . $conn->error . "\n";
}

echo "\n🎯 Destinations table setup complete!\n";
?> 