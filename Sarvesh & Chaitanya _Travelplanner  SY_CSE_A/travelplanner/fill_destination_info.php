<?php
require_once 'php/config.php';

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Destination information data
    $destination_info = [
        'Mumbai' => [
            'why_visit' => 'Mumbai, the financial capital of India, offers a perfect blend of colonial heritage, modern architecture, and vibrant culture. From the iconic Gateway of India to the bustling Marine Drive, experience the city that never sleeps.',
            'popular_for' => 'Business hub, Bollywood, street food, colonial architecture, shopping, nightlife, and cultural diversity.',
            'local_sights' => json_encode(['Gateway of India', 'Marine Drive', 'Juhu Beach', 'Elephanta Caves', 'Colaba Causeway', 'Bandra-Worli Sea Link', 'Haji Ali Dargah', 'Chhatrapati Shivaji Terminus']),
            'popular_places' => json_encode(['Gateway of India', 'Marine Drive', 'Juhu Beach', 'Elephanta Caves', 'Colaba Causeway', 'Bandra-Worli Sea Link', 'Haji Ali Dargah', 'Chhatrapati Shivaji Terminus', 'Mumbai Film City', 'Sanjay Gandhi National Park']),
            'highlights' => json_encode(['Gateway of India', 'Marine Drive', 'Juhu Beach', 'Elephanta Caves', 'Colaba Causeway', 'Bandra-Worli Sea Link', 'Haji Ali Dargah', 'Chhatrapati Shivaji Terminus']),
            'features' => json_encode(['Business Hub', 'Bollywood', 'Street Food', 'Colonial Architecture', 'Shopping', 'Nightlife', 'Cultural Diversity'])
        ],
        'Delhi' => [
            'why_visit' => 'Delhi, the heart of India, is a city where ancient monuments stand alongside modern skyscrapers. Experience the rich history through Mughal architecture, explore bustling markets, and savor diverse cuisines.',
            'popular_for' => 'Historical monuments, Mughal architecture, street food, shopping, cultural heritage, and political significance.',
            'local_sights' => json_encode(['Red Fort', 'Qutub Minar', 'India Gate', 'Humayun\'s Tomb', 'Lotus Temple', 'Akshardham Temple', 'Jama Masjid', 'Connaught Place']),
            'popular_places' => json_encode(['Red Fort', 'Qutub Minar', 'India Gate', 'Humayun\'s Tomb', 'Lotus Temple', 'Akshardham Temple', 'Jama Masjid', 'Connaught Place', 'Chandni Chowk', 'Hauz Khas Village']),
            'highlights' => json_encode(['Red Fort', 'Qutub Minar', 'India Gate', 'Humayun\'s Tomb', 'Lotus Temple', 'Akshardham Temple', 'Jama Masjid', 'Connaught Place']),
            'features' => json_encode(['Historical Monuments', 'Mughal Architecture', 'Street Food', 'Shopping', 'Cultural Heritage', 'Political Significance'])
        ],
        'Bangalore' => [
            'why_visit' => 'Bangalore, the Silicon Valley of India, combines modern technology with rich cultural heritage. Enjoy pleasant weather, beautiful gardens, vibrant nightlife, and world-class dining experiences.',
            'popular_for' => 'IT hub, pleasant weather, gardens, nightlife, craft beer, shopping, and cosmopolitan culture.',
            'local_sights' => json_encode(['Lalbagh Botanical Garden', 'Cubbon Park', 'Bangalore Palace', 'Vidhana Soudha', 'ISKCON Temple', 'Tipu Sultan\'s Summer Palace', 'Nandi Hills', 'Commercial Street']),
            'popular_places' => json_encode(['Lalbagh Botanical Garden', 'Cubbon Park', 'Bangalore Palace', 'Vidhana Soudha', 'ISKCON Temple', 'Tipu Sultan\'s Summer Palace', 'Nandi Hills', 'Commercial Street', 'MG Road', 'Koramangala']),
            'highlights' => json_encode(['Lalbagh Botanical Garden', 'Cubbon Park', 'Bangalore Palace', 'Vidhana Soudha', 'ISKCON Temple', 'Tipu Sultan\'s Summer Palace', 'Nandi Hills', 'Commercial Street']),
            'features' => json_encode(['IT Hub', 'Pleasant Weather', 'Gardens', 'Nightlife', 'Craft Beer', 'Shopping', 'Cosmopolitan Culture'])
        ],
        'Chennai' => [
            'why_visit' => 'Chennai, the cultural capital of South India, offers a perfect blend of traditional heritage and modern development. Experience classical music, dance, beautiful beaches, and authentic South Indian cuisine.',
            'popular_for' => 'Classical music and dance, beaches, South Indian cuisine, temples, cultural heritage, and traditional arts.',
            'local_sights' => json_encode(['Marina Beach', 'Kapaleeshwarar Temple', 'Fort St. George', 'Vivekananda College', 'San Thome Basilica', 'Valluvar Kottam', 'Guindy National Park', 'MGR Memorial']),
            'popular_places' => json_encode(['Marina Beach', 'Kapaleeshwarar Temple', 'Fort St. George', 'Vivekananda College', 'San Thome Basilica', 'Valluvar Kottam', 'Guindy National Park', 'MGR Memorial', 'T Nagar', 'Pondy Bazaar']),
            'highlights' => json_encode(['Marina Beach', 'Kapaleeshwarar Temple', 'Fort St. George', 'Vivekananda College', 'San Thome Basilica', 'Valluvar Kottam', 'Guindy National Park', 'MGR Memorial']),
            'features' => json_encode(['Classical Music', 'Classical Dance', 'Beaches', 'South Indian Cuisine', 'Temples', 'Cultural Heritage', 'Traditional Arts'])
        ],
        'Pune' => [
            'why_visit' => 'Pune, the cultural capital of Maharashtra, is known for its vibrant student life, historical landmarks, and pleasant climate. The city offers a unique blend of tradition and modernity, with a thriving food scene and proximity to scenic hill stations.',
            'popular_for' => 'Education hub, historical forts, cultural festivals, IT industry, food, and pleasant weather.',
            'local_sights' => json_encode(['Shaniwar Wada', 'Aga Khan Palace', 'Sinhagad Fort', 'Pataleshwar Cave Temple', 'Osho Ashram', 'Raja Dinkar Kelkar Museum', 'Parvati Hill', 'Khadakwasla Dam']),
            'popular_places' => json_encode(['Shaniwar Wada', 'Aga Khan Palace', 'Sinhagad Fort', 'Pataleshwar Cave Temple', 'Osho Ashram', 'Raja Dinkar Kelkar Museum', 'Parvati Hill', 'Khadakwasla Dam', 'FC Road', 'MG Road']),
            'highlights' => json_encode(['Shaniwar Wada', 'Aga Khan Palace', 'Sinhagad Fort', 'Pataleshwar Cave Temple', 'Osho Ashram', 'Raja Dinkar Kelkar Museum', 'Parvati Hill', 'Khadakwasla Dam']),
            'features' => json_encode(['Education Hub', 'Historical Forts', 'Cultural Festivals', 'IT Industry', 'Food', 'Pleasant Weather'])
        ],
        'Kolkata' => [
            'why_visit' => 'Kolkata, the City of Joy, is famous for its colonial architecture, vibrant arts scene, and rich literary heritage. Experience Durga Puja, explore bustling markets, and savor delicious Bengali cuisine.',
            'popular_for' => 'Colonial architecture, literature, arts, Durga Puja, street food, and cultural diversity.',
            'local_sights' => json_encode(['Victoria Memorial', 'Howrah Bridge', 'Dakshineswar Kali Temple', 'Indian Museum', 'St. Paul\'s Cathedral', 'Marble Palace', 'Science City', 'Eden Gardens']),
            'popular_places' => json_encode(['Victoria Memorial', 'Howrah Bridge', 'Dakshineswar Kali Temple', 'Indian Museum', 'St. Paul\'s Cathedral', 'Marble Palace', 'Science City', 'Eden Gardens', 'Park Street', 'Kalighat Temple']),
            'highlights' => json_encode(['Victoria Memorial', 'Howrah Bridge', 'Dakshineswar Kali Temple', 'Indian Museum', 'St. Paul\'s Cathedral', 'Marble Palace', 'Science City', 'Eden Gardens']),
            'features' => json_encode(['Colonial Architecture', 'Literature', 'Arts', 'Durga Puja', 'Street Food', 'Cultural Diversity'])
        ],
        'Ahmedabad' => [
            'why_visit' => 'Ahmedabad, the first UNESCO World Heritage City in India, showcases rich Gujarati culture, stunning architecture, and vibrant traditions. Explore ancient monuments, indulge in delicious street food, and experience the warmth of Gujarati hospitality.',
            'popular_for' => 'UNESCO heritage sites, Gujarati culture, street food, textile industry, historical monuments, and traditional handicrafts.',
            'local_sights' => json_encode(['Sabarmati Ashram', 'Adalaj Stepwell', 'Sidi Saiyyed Mosque', 'Bhadra Fort', 'Jama Masjid', 'Calico Museum', 'Kankaria Lake', 'Law Garden']),
            'popular_places' => json_encode(['Sabarmati Ashram', 'Adalaj Stepwell', 'Sidi Saiyyed Mosque', 'Bhadra Fort', 'Jama Masjid', 'Calico Museum', 'Kankaria Lake', 'Law Garden', 'Manek Chowk', 'Navrangpura']),
            'highlights' => json_encode(['Sabarmati Ashram', 'Adalaj Stepwell', 'Sidi Saiyyed Mosque', 'Bhadra Fort', 'Jama Masjid', 'Calico Museum', 'Kankaria Lake', 'Law Garden']),
            'features' => json_encode(['UNESCO Heritage', 'Gujarati Culture', 'Street Food', 'Textile Industry', 'Historical Monuments', 'Traditional Handicrafts'])
        ]
    ];
    
    echo "Updating destination information for all cities...\n\n";
    
    foreach ($destination_info as $destination => $info) {
        $stmt = $pdo->prepare("UPDATE destinations SET 
            why_visit = ?, 
            popular_for = ?, 
            local_sights = ?, 
            popular_places = ?, 
            highlights = ?, 
            features = ? 
            WHERE name = ?");
        
        $stmt->execute([
            $info['why_visit'],
            $info['popular_for'],
            $info['local_sights'],
            $info['popular_places'],
            $info['highlights'],
            $info['features'],
            $destination
        ]);
        
        if ($stmt->rowCount() > 0) {
            echo "✅ Updated information for $destination\n";
        } else {
            echo "❌ Destination '$destination' not found\n";
        }
    }
    
    echo "\n✅ All destination information updated successfully!\n";
    echo "Now all destinations have complete and detailed information for the 'View Details' modal.\n";
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
}
?> 