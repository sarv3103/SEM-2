-- Database updates for destination/package booking system

USE travelplanner;

-- Add new columns to bookings table
ALTER TABLE bookings 
ADD COLUMN start_date DATE AFTER date,
ADD COLUMN end_date DATE AFTER start_date,
ADD COLUMN duration INT(3) AFTER end_date,
ADD COLUMN contact_mobile VARCHAR(15) AFTER duration,
ADD COLUMN contact_email VARCHAR(100) AFTER contact_mobile,
ADD COLUMN special_requirements TEXT AFTER contact_email,
ADD COLUMN booking_type ENUM('destination', 'package') AFTER special_requirements,
ADD COLUMN destination_name VARCHAR(200) AFTER booking_type;

-- Create traveler_details table
CREATE TABLE traveler_details (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    booking_id INT(11) NOT NULL,
    traveler_number INT(3) NOT NULL,
    name VARCHAR(100) NOT NULL,
    age INT(3) NOT NULL,
    gender VARCHAR(10) NOT NULL,
    passport_number VARCHAR(50),
    nationality VARCHAR(50) DEFAULT 'Indian',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
);

-- Create destination_packages table for enhanced booking system
CREATE TABLE destination_packages (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    booking_id INT(11) NOT NULL,
    package_type ENUM('destination', 'package') NOT NULL,
    destination_name VARCHAR(200) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    duration INT(3) NOT NULL,
    num_travelers INT(3) NOT NULL,
    travel_style ENUM('budget', 'standard', 'luxury') NOT NULL,
    transport_mode VARCHAR(50) NOT NULL,
    transport_cost DECIMAL(10,2) NOT NULL,
    base_price DECIMAL(10,2) NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    contact_mobile VARCHAR(15) NOT NULL,
    contact_email VARCHAR(100) NOT NULL,
    special_requirements TEXT,
    booking_status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
);

-- Add indexes for better performance
CREATE INDEX idx_booking_type ON bookings(booking_type);
CREATE INDEX idx_destination_name ON bookings(destination_name);
CREATE INDEX idx_traveler_booking_id ON traveler_details(booking_id);
CREATE INDEX idx_destination_packages_booking_id ON destination_packages(booking_id);
CREATE INDEX idx_destination_packages_status ON destination_packages(booking_status);

-- Update existing destinations with enhanced information
UPDATE destinations SET 
    local_sights = '["Local attractions", "Historical sites", "Cultural landmarks"]',
    popular_places = '["Popular tourist spots", "Must-visit locations", "Famous landmarks"]',
    why_visit = 'This destination offers unique experiences and beautiful landscapes.',
    popular_for = 'Tourism, sightseeing, and cultural experiences.',
    best_time = 'October to March',
    duration = '5-7 days',
    difficulty = 'Easy',
    highlights = '["Scenic beauty", "Cultural heritage", "Adventure activities"]'
WHERE local_sights IS NULL;

-- Update specific destinations with detailed information
UPDATE destinations SET 
    local_sights = '["Beaches", "Fort Aguada", "Basilica of Bom Jesus", "Dudhsagar Falls", "Spice Plantations"]',
    popular_places = '["Calangute Beach", "Baga Beach", "Anjuna Beach", "Panaji", "Old Goa"]',
    why_visit = 'Goa is famous for its pristine beaches, vibrant nightlife, Portuguese heritage, and delicious seafood.',
    popular_for = 'Beach holidays, water sports, nightlife, heritage tours, and relaxation.',
    best_time = 'November to March',
    duration = '5-7 days',
    difficulty = 'Easy',
    highlights = '["Beach activities", "Water sports", "Nightlife", "Heritage sites", "Seafood"]'
WHERE name = 'Goa';

UPDATE destinations SET 
    local_sights = '["Backwaters", "Tea Gardens", "Ayurvedic Centers", "Wildlife Sanctuaries", "Beaches"]',
    popular_places = '["Alleppey Backwaters", "Munnar Tea Gardens", "Kochi", "Kumarakom", "Varkala Beach"]',
    why_visit = 'Kerala offers serene backwaters, lush tea gardens, Ayurvedic wellness, and rich cultural heritage.',
    popular_for = 'Backwater cruises, Ayurvedic treatments, tea garden visits, and nature tourism.',
    best_time = 'September to March',
    duration = '6-8 days',
    difficulty = 'Easy',
    highlights = '["Houseboat cruises", "Tea gardens", "Ayurveda", "Wildlife", "Beaches"]'
WHERE name = 'Kerala';

UPDATE destinations SET 
    local_sights = '["Monasteries", "Lakes", "Mountain Passes", "Valleys", "Ancient Forts"]',
    popular_places = ["Pangong Lake", "Nubra Valley", "Khardungla Pass", "Thiksey Monastery", "Shanti Stupa"],
    why_visit = 'Ladakh offers stunning high-altitude landscapes, ancient monasteries, and adventure activities.',
    popular_for = 'Mountain adventures, monastery visits, photography, and spiritual experiences.',
    best_time = 'June to September',
    duration = '7-10 days',
    difficulty = 'Moderate',
    highlights = '["High-altitude landscapes", "Monasteries", "Adventure sports", "Photography", "Spiritual retreats"]'
WHERE name = 'Ladakh';

UPDATE destinations SET 
    local_sights = '["Forts", "Palaces", "Temples", "Deserts", "Wildlife Sanctuaries"]',
    popular_places = '["Amber Fort", "City Palace", "Hawa Mahal", "Jantar Mantar", "Thar Desert"]',
    why_visit = 'Rajasthan is the land of kings with magnificent forts, palaces, and rich cultural heritage.',
    popular_for = 'Heritage tours, palace stays, desert safaris, and cultural experiences.',
    best_time = 'October to March',
    duration = '8-12 days',
    difficulty = 'Easy',
    highlights = '["Forts and palaces", "Desert safaris", "Cultural heritage", "Luxury stays", "Traditional arts"]'
WHERE name = 'Rajasthan';

UPDATE destinations SET 
    local_sights = '["Overwater Bungalows", "Coral Reefs", "Islands", "Beaches", "Water Sports"]',
    popular_places = '["Male", "Maafushi", "Banana Reef", "Hulhumale", "Artificial Beach"]',
    why_visit = 'Maldives offers pristine beaches, crystal clear waters, and luxurious overwater accommodations.',
    popular_for = 'Luxury beach holidays, water sports, snorkeling, diving, and romantic getaways.',
    best_time = 'November to April',
    duration = '4-7 days',
    difficulty = 'Easy',
    highlights = '["Overwater bungalows", "Snorkeling", "Diving", "Beach relaxation", "Water sports"]'
WHERE name = 'Maldives';

UPDATE destinations SET 
    local_sights = '["Temples", "Beaches", "Markets", "Islands", "Nightlife"]',
    popular_places = '["Bangkok", "Pattaya", "Phuket", "Koh Samui", "Ayutthaya"]',
    why_visit = 'Thailand offers beautiful beaches, ancient temples, vibrant street markets, and delicious cuisine.',
    popular_for = 'Beach holidays, temple visits, shopping, street food, and nightlife.',
    best_time = 'November to April',
    duration = '6-10 days',
    difficulty = 'Easy',
    highlights = '["Temples", "Beaches", "Street food", "Shopping", "Nightlife"]'
WHERE name = 'Thailand';

UPDATE destinations SET 
    local_sights = '["Skyscrapers", "Desert Safari", "Shopping Malls", "Theme Parks", "Beaches"]',
    popular_places = '["Burj Khalifa", "Palm Jumeirah", "Dubai Mall", "Desert Safari", "Dubai Marina"]',
    why_visit = 'Dubai offers modern architecture, luxury shopping, desert adventures, and world-class attractions.',
    popular_for = 'Luxury shopping, desert safaris, modern architecture, and entertainment.',
    best_time = 'November to March',
    duration = '4-6 days',
    difficulty = 'Easy',
    highlights = '["Skyscrapers", "Desert safari", "Shopping", "Entertainment", "Luxury experiences"]'
WHERE name = 'Dubai';

-- Insert sample data for testing
INSERT INTO destination_packages (booking_id, package_type, destination_name, start_date, end_date, duration, num_travelers, travel_style, transport_mode, transport_cost, base_price, total_amount, contact_mobile, contact_email, booking_status) VALUES
(1, 'destination', 'Goa', '2024-02-15', '2024-02-20', 6, 2, 'standard', 'flight', 3000.00, 15000.00, 36000.00, '9876543210', 'test@example.com', 'confirmed'),
(2, 'package', 'Goa Beach Getaway', '2024-03-01', '2024-03-05', 5, 3, 'luxury', 'flight', 3000.00, 18999.00, 65997.00, '9876543211', 'test2@example.com', 'confirmed');

-- Insert sample traveler details
INSERT INTO traveler_details (booking_id, traveler_number, name, age, gender, passport_number, nationality) VALUES
(1, 1, 'John Doe', 30, 'male', '', 'Indian'),
(1, 2, 'Jane Doe', 28, 'female', '', 'Indian'),
(2, 1, 'Alice Smith', 35, 'female', 'A12345678', 'Indian'),
(2, 2, 'Bob Smith', 32, 'male', 'B87654321', 'Indian'),
(2, 3, 'Charlie Smith', 8, 'male', 'C11111111', 'Indian'); 