-- Drop and recreate the database
DROP DATABASE IF EXISTS travelplanner;
CREATE DATABASE travelplanner;
USE travelplanner;

-- Table for registered users
CREATE TABLE users (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    is_admin TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    wallet_balance DECIMAL(10,2) NOT NULL DEFAULT 0
);

-- Table for booking records
CREATE TABLE bookings (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    name VARCHAR(100) NOT NULL,
    age INT(3) NOT NULL,
    gender VARCHAR(10) NOT NULL,
    type VARCHAR(50),
    source VARCHAR(100),
    destination VARCHAR(100),
    date DATE,
    num_travelers INT(3),
    fare DECIMAL(10,2),
    per_person DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Table for trip plans
CREATE TABLE plans (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    city VARCHAR(100),
    start_date DATE,
    end_date DATE,
    travelers INT(3),
    travel_style VARCHAR(20),
    places TEXT,  -- JSON or comma-separated list of places
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Table for contact messages
CREATE TABLE contact_messages (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table for packages
CREATE TABLE packages (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    type ENUM('domestic', 'international') NOT NULL,
    days INT(3),
    nights INT(3),
    cities TEXT,
    price_per_person DECIMAL(10,2),
    meals VARCHAR(100),
    hotels TEXT,
    travel_modes TEXT,
    image VARCHAR(500),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table for destinations
CREATE TABLE destinations (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    location VARCHAR(100),
    category VARCHAR(50),
    description TEXT,
    image VARCHAR(500),
    features TEXT,
    price_range VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create wallet_transactions table
CREATE TABLE IF NOT EXISTS wallet_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    type ENUM('credit','debit') NOT NULL,
    description VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Insert default admin user
INSERT INTO users (username, email, password, is_admin) VALUES 
('admin', 'admin@travelplanner.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1);

-- Insert sample packages
INSERT INTO packages (name, type, days, nights, cities, price_per_person, meals, hotels, travel_modes, image, description) VALUES
('Goa Beach Getaway', 'domestic', 5, 4, '["Panaji", "Calangute", "Baga"]', 18999.00, 'Included', '["Taj Vivanta", "Beach Bay Resort"]', '["Flight", "Cab"]', 'https://media.istockphoto.com/id/157579910/photo/the-beach.jpg?s=612x612&w=0&k=20&c=aMk67AmzIVD_S1Nibww8ytUdyub2ck3HNQ3uTvuPWPI=', '5 days, 4 nights. Explore North & South Goa beaches, nightlife, and spice plantation tour.'),
('Himachal Adventure Escape', 'domestic', 7, 6, '["Shimla", "Manali", "Solang Valley"]', 21999.00, 'Included', '["Snow Valley Resort", "Apple Country Resort"]', '["Train", "Bus", "Cab"]', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTTeyWGGWqyUwuDE4tw2HBRyoH7aBldHGk8JQ&s', '7 days, 6 nights. Enjoy snow activities, scenic valleys, and local sightseeing.'),
('Kerala Backwaters Retreat', 'domestic', 6, 5, '["Kochi", "Munnar", "Alleppey"]', 20499.00, 'Included', '["Spice Jungle Resort", "Houseboat Stay"]', '["Flight", "Cab", "Boat"]', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTHrnyvcXzeXzyAeGYn9eWncSPM7G_st8yfRg&s', '6 days, 5 nights. Tea gardens, wildlife safari, houseboat cruise through backwaters.'),
('Maldives Explorer', 'international', 4, 3, '["Malé", "Maafushi", "Banana Reef"]', 24999.00, 'Included', '["Paradise Island Resort", "Maafushi Inn"]', '["Flight", "Boat"]', 'https://images.unsplash.com/photo-1544551763-46a013bb70d5?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80', '4 days, 3 nights. Maafushi, Banana Reef, city tour.'),
('Thailand Delight', 'international', 5, 4, '["Bangkok", "Pattaya"]', 28999.00, 'Included', '["The Berkeley Hotel", "Avani Pattaya"]', '["Flight", "Cab"]', 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80', '5 days, 4 nights. Coral island tour, shopping, nightlife.'),
('Dubai Luxury Tour', 'international', 4, 3, '["Dubai", "Abu Dhabi"]', 37999.00, 'Included', '["Burj Al Arab View Hotel", "City Max"]', '["Flight", "Cab"]', 'https://www.gibsons.co.uk/wp-content/uploads/2023/12/Dubai-400x500.jpg', '4 days, 3 nights. Burj Khalifa, desert safari, city tour.');

-- Insert sample destinations
INSERT INTO destinations (name, location, category, description, image, features, price_range) VALUES
('Goa', 'India', 'beach', 'Famous for its pristine beaches, vibrant nightlife, and Portuguese heritage. Perfect for beach lovers and party enthusiasts.', 'https://th.bing.com/th/id/OIP.OsdnDjdW74sn01vHghKvOwHaFj?w=221&h=180&c=7&r=0&o=7&dpr=1.3&pid=1.7&rm=3', '["Beaches", "Nightlife", "Heritage"]', 'From ₹15,000'),
('Kerala', 'India', 'culture', 'Gods Own Country with serene backwaters, lush tea gardens, and Ayurvedic wellness retreats.', 'https://th.bing.com/th/id/OIP.KX8jyV-N7KMbHfH6V64vwQHaDk?w=337&h=168&c=7&r=0&o=7&dpr=1.3&pid=1.7&rm=3', '["Backwaters", "Ayurveda", "Tea Gardens"]', 'From ₹18,000'),
('Ladakh', 'India', 'mountain', 'High-altitude desert with stunning landscapes, ancient monasteries, and adventure activities.', 'https://th.bing.com/th/id/OIP.kaN4VnvSSguxcYILikQ71wHaEj?w=280&h=180&c=7&r=0&o=7&dpr=1.3&pid=1.7&rm=3', '["Mountains", "Monasteries", "Adventure"]', 'From ₹25,000'),
('Rajasthan', 'India', 'culture', 'Land of kings with magnificent forts, palaces, and rich cultural heritage.', 'https://th.bing.com/th/id/OIP.tUcUdXBmUOKEZkHZiCgUagHaD2?w=346&h=180&c=7&r=0&o=7&dpr=1.3&pid=1.7&rm=3', '["Forts", "Palaces", "Heritage"]', 'From ₹20,000'),
('Maldives', 'International', 'beach', 'Tropical paradise with overwater bungalows and crystal clear turquoise waters.', 'https://th.bing.com/th/id/OIP.F2b2bVhPKuGDYf6lmViHwgHaFj?w=236&h=180&c=7&r=0&o=7&dpr=1.3&pid=1.7&rm=3', '["Overwater Bungalows", "Snorkeling", "Luxury"]', 'From ₹45,000'),
('Thailand', 'International', 'culture', 'Land of smiles with beautiful beaches, temples, and vibrant street markets.', 'https://th.bing.com/th/id/OIP.ZnddV6vHCb6xS2_o73GemAHaE7?w=245&h=180&c=7&r=0&o=7&dpr=1.3&pid=1.7&rm=3', '["Beaches", "Temples", "Street Food"]', 'From ₹35,000'),
('Dubai', 'International', 'city', 'Modern metropolis with skyscrapers, desert adventures, and luxury shopping.', 'https://www.gibsons.co.uk/wp-content/uploads/2023/12/Dubai-400x500.jpg', '["Skyscrapers", "Desert Safari", "Shopping"]', 'From ₹55,000');
