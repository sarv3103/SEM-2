// City plans data for both international and domestic destinations
const cityPlans = {
  // International destinations
  'paris': {
    sights: [
      { name: 'Eiffel Tower', cost: 25 },
      { name: 'Louvre Museum', cost: 17 },
      { name: 'Notre-Dame Cathedral', cost: 0 },
      { name: 'Montmartre', cost: 0 },
      { name: 'Seine River Cruise', cost: 15 },
      { name: 'Palace of Versailles', cost: 20 },
      { name: 'Sainte-Chapelle', cost: 10 }
    ],
    hotel: 12000,
    food: 2500,
    transport: 1000
  },
  'london': {
    sights: [
      { name: 'London Eye', cost: 30 },
      { name: 'British Museum', cost: 0 },
      { name: 'Tower of London', cost: 25 },
      { name: 'Buckingham Palace', cost: 0 },
      { name: 'Westminster Abbey', cost: 20 },
      { name: "St. Paul's Cathedral", cost: 18 },
      { name: 'Hyde Park', cost: 0 }
    ],
    hotel: 11000,
    food: 2400,
    transport: 900
  },
  'new_york': {
    sights: [
      { name: 'Statue of Liberty', cost: 20 },
      { name: 'Central Park', cost: 0 },
      { name: 'Empire State Building', cost: 38 },
      { name: 'Metropolitan Museum of Art', cost: 25 },
      { name: 'Times Square', cost: 0 },
      { name: 'Brooklyn Bridge', cost: 0 },
      { name: '9/11 Memorial & Museum', cost: 26 }
    ],
    hotel: 13000,
    food: 3000,
    transport: 1000
  },
  'tokyo': {
    sights: [
      { name: 'Tokyo Skytree', cost: 20 },
      { name: 'Senso-ji Temple', cost: 0 },
      { name: 'Meiji Shrine', cost: 0 },
      { name: 'Tokyo Tower', cost: 15 },
      { name: 'Ueno Zoo', cost: 6 },
      { name: 'Shinjuku Gyoen National Garden', cost: 5 },
      { name: 'Odaiba', cost: 0 }
    ],
    hotel: 10000,
    food: 2200,
    transport: 800
  },
  'dubai': {
    sights: [
      { name: 'Burj Khalifa', cost: 35 },
      { name: 'Dubai Mall', cost: 0 },
      { name: 'Dubai Fountain Show', cost: 0 },
      { name: 'Palm Jumeirah', cost: 0 },
      { name: 'Desert Safari', cost: 60 },
      { name: 'Dubai Marina', cost: 0 },
      { name: 'Dubai Museum', cost: 3 }
    ],
    hotel: 9000,
    food: 2000,
    transport: 700
  },
  'singapore': {
    sights: [
      { name: 'Marina Bay Sands SkyPark', cost: 20 },
      { name: 'Gardens by the Bay', cost: 15 },
      { name: 'Sentosa Island', cost: 0 },
      { name: 'Universal Studios Singapore', cost: 60 },
      { name: 'Singapore Zoo', cost: 35 },
      { name: 'Chinatown', cost: 0 },
      { name: 'Little India', cost: 0 }
    ],
    hotel: 8500,
    food: 1800,
    transport: 600
  },

  // Domestic destinations
  'mumbai': {
    name: 'Mumbai',
    description: 'City of dreams and opportunities',
    plans: [
      {
        name: 'City Explorer',
        duration: '3 days',
        price: 15000,
        includes: ['Marine Drive', 'Gateway of India', 'Elephanta Caves'],
        hotel: 'City Hotel',
        meals: 'Breakfast'
      },
      {
        name: 'Bollywood Experience',
        duration: '4 days',
        price: 25000,
        includes: ['Film city tour', 'Bollywood dance class', 'Movie premiere'],
        hotel: 'Boutique Hotel',
        meals: 'Breakfast & Dinner'
      },
      {
        name: 'Food & Shopping',
        duration: '5 days',
        price: 20000,
        includes: ['Food tours', 'Shopping spree', 'Nightlife'],
        hotel: 'Luxury Hotel',
        meals: 'All meals'
      }
    ],
    sights: ['Gateway of India', 'Marine Drive', 'Juhu Beach'],
    hotel: 4500,
    food: 1800,
    transport: 900
  },
  'delhi': {
    name: 'Delhi',
    description: 'Capital city with rich history',
    plans: [
      {
        name: 'Heritage Walk',
        duration: '3 days',
        price: 12000,
        includes: ['Red Fort', 'Qutub Minar', 'India Gate'],
        hotel: 'Heritage Hotel',
        meals: 'Breakfast'
      },
      {
        name: 'Food & Culture',
        duration: '4 days',
        price: 18000,
        includes: ['Food tours', 'Museum visits', 'Shopping'],
        hotel: 'City Center Hotel',
        meals: 'Breakfast & Dinner'
      },
      {
        name: 'Luxury Experience',
        duration: '5 days',
        price: 30000,
        includes: ['Private tours', 'Spa treatments', 'Fine dining'],
        hotel: '5-star Luxury Hotel',
        meals: 'All meals'
      }
    ],
    sights: ['Red Fort', 'India Gate', 'Lotus Temple'],
    hotel: 4000,
    food: 1500,
    transport: 800
  },
  'jaipur': {
    sights: [
      { name: 'Amber Fort', cost: 100 },
      { name: 'Hawa Mahal', cost: 50 },
      { name: 'City Palace', cost: 200 },
      { name: 'Jantar Mantar', cost: 50 },
      { name: 'Nahargarh Fort', cost: 100 },
      { name: 'Albert Hall Museum', cost: 40 },
      { name: 'Jaigarh Fort', cost: 70 }
    ],
    hotel: 2000,
    food: 600,
    transport: 350
  },
  'goa': {
    name: 'Goa',
    description: 'Beach paradise with Portuguese heritage',
    plans: [
      {
        name: 'Beach Bliss Package',
        duration: '3 days',
        price: 15000,
        includes: ['Beach hopping', 'Water sports', 'Nightlife tour'],
        hotel: 'Beachfront Resort',
        meals: 'Breakfast & Dinner'
      },
      {
        name: 'Heritage Explorer',
        duration: '4 days',
        price: 20000,
        includes: ['Old Goa churches', 'Spice plantation', 'Dudhsagar falls'],
        hotel: 'Heritage Villa',
        meals: 'All meals'
      },
      {
        name: 'Adventure Seeker',
        duration: '5 days',
        price: 25000,
        includes: ['Scuba diving', 'Parasailing', 'Trekking'],
        hotel: 'Adventure Resort',
        meals: 'All meals'
      }
    ],
    sights: ['Baga Beach', 'Fort Aguada', 'Basilica of Bom Jesus'],
    hotel: 5000,
    food: 2000,
    transport: 1000
  },
  'udaipur': {
    sights: [
      { name: 'City Palace', cost: 300 },
      { name: 'Lake Pichola Boat Ride', cost: 400 },
      { name: 'Jagdish Temple', cost: 0 },
      { name: 'Sajjangarh Palace', cost: 100 },
      { name: 'Fateh Sagar Lake', cost: 0 },
      { name: 'Bagore Ki Haveli', cost: 100 },
      { name: 'Saheliyon Ki Bari', cost: 50 }
    ],
    hotel: 2800,
    food: 700,
    transport: 400
  },
  'kerala': {
    name: 'Kerala',
    description: 'God\'s own country',
    plans: [
      {
        name: 'Backwater Bliss',
        duration: '4 days',
        price: 20000,
        includes: ['Houseboat stay', 'Ayurveda', 'Beach visit'],
        hotel: 'Houseboat & Resort',
        meals: 'All meals'
      },
      {
        name: 'Hill Station Escape',
        duration: '5 days',
        price: 25000,
        includes: ['Munnar visit', 'Tea plantation', 'Wildlife safari'],
        hotel: 'Hill Resort',
        meals: 'All meals'
      },
      {
        name: 'Cultural Experience',
        duration: '6 days',
        price: 30000,
        includes: ['Kathakali show', 'Spice tour', 'Beach activities'],
        hotel: 'Heritage Resort',
        meals: 'All meals'
      }
    ],
    sights: ['Backwaters', 'Munnar Hills', 'Kovalam Beach'],
    hotel: 5500,
    food: 2000,
    transport: 1200
  },
  'leh': {
    sights: [
      { name: 'Pangong Lake', cost: 0 },
      { name: 'Nubra Valley', cost: 0 },
      { name: 'Magnetic Hill', cost: 0 },
      { name: 'Leh Palace', cost: 100 },
      { name: 'Shanti Stupa', cost: 0 },
      { name: 'Hemis Monastery', cost: 50 },
      { name: 'Khardung La Pass', cost: 0 }
    ],
    hotel: 2500,
    food: 700,
    transport: 600
  },
  'varanasi': {
    sights: [
      { name: 'Kashi Vishwanath Temple', cost: 0 },
      { name: 'Dashashwamedh Ghat', cost: 0 },
      { name: 'Assi Ghat', cost: 0 },
      { name: 'Sarnath', cost: 5 },
      { name: 'Manikarnika Ghat', cost: 0 },
      { name: 'Bharat Kala Bhavan Museum', cost: 20 },
      { name: 'Ramnagar Fort', cost: 50 }
    ],
    hotel: 1500,
    food: 500,
    transport: 300
  },
  'amritsar': {
    sights: [
      { name: 'Golden Temple', cost: 0 },
      { name: 'Jallianwala Bagh', cost: 0 },
      { name: 'Wagah Border Ceremony', cost: 0 },
      { name: 'Partition Museum', cost: 10 },
      { name: 'Gobindgarh Fort', cost: 100 },
      { name: 'Durgiana Temple', cost: 0 },
      { name: 'Maharaja Ranjit Singh Museum', cost: 10 }
    ],
    hotel: 1800,
    food: 600,
    transport: 400
  },
  'rishikesh': {
    sights: [
      { name: 'Laxman Jhula', cost: 0 },
      { name: 'Ram Jhula', cost: 0 },
      { name: 'Triveni Ghat', cost: 0 },
      { name: 'Neelkanth Mahadev Temple', cost: 0 },
      { name: 'Parmarth Niketan Ashram', cost: 0 },
      { name: 'Beatles Ashram', cost: 150 },
      { name: 'Kunjapuri Devi Temple', cost: 0 }
    ],
    hotel: 1200,
    food: 500,
    transport: 300
  },
  'hampi': {
    sights: [
      { name: 'Virupaksha Temple', cost: 0 },
      { name: 'Vittala Temple', cost: 30 },
      { name: 'Lotus Mahal', cost: 10 },
      { name: 'Elephant Stables', cost: 10 },
      { name: 'Hampi Bazaar', cost: 0 },
      { name: 'Matanga Hill', cost: 0 },
      { name: "Queen's Bath", cost: 10 }
    ],
    hotel: 1000,
    food: 400,
    transport: 200
  },
  'jodhpur': {
    sights: [
      { name: 'Mehrangarh Fort', cost: 100 },
      { name: 'Umaid Bhawan Palace', cost: 30 },
      { name: 'Jaswant Thada', cost: 20 },
      { name: 'Clock Tower', cost: 0 },
      { name: 'Mandore Gardens', cost: 0 },
      { name: 'Rao Jodha Desert Rock Park', cost: 50 },
      { name: 'Toorji Ka Jhalra', cost: 0 }
    ],
    hotel: 2000,
    food: 600,
    transport: 300
  },
  'shimla': {
    sights: [
      { name: 'The Ridge', cost: 0 },
      { name: 'Jakhoo Temple', cost: 0 },
      { name: 'Christ Church', cost: 0 },
      { name: 'Kufri', cost: 0 },
      { name: 'Mall Road', cost: 0 },
      { name: 'Tara Devi Temple', cost: 0 },
      { name: 'Shimla State Museum', cost: 20 }
    ],
    hotel: 2500,
    food: 700,
    transport: 400
  },
  'ooty': {
    sights: [
      { name: 'Ooty Lake', cost: 10 },
      { name: 'Botanical Gardens', cost: 30 },
      { name: 'Doddabetta Peak', cost: 10 },
      { name: 'Rose Garden', cost: 30 },
      { name: 'Nilgiri Mountain Railway', cost: 25 },
      { name: 'Pykara Falls', cost: 0 },
      { name: 'Emerald Lake', cost: 0 }
    ],
    hotel: 2200,
    food: 600,
    transport: 300
  },
  'kodaikanal': {
    sights: [
      { name: 'Kodaikanal Lake', cost: 0 },
      { name: "Coaker's Walk", cost: 10 },
      { name: 'Bryant Park', cost: 30 },
      { name: 'Pillar Rocks', cost: 5 },
      { name: 'Silver Cascade Falls', cost: 0 },
      { name: 'Guna Caves', cost: 0 },
      { name: 'Berijam Lake', cost: 0 }
    ],
    hotel: 2000,
    food: 600,
    transport: 300
  },
  'gangtok': {
    sights: [
      { name: 'Tsomgo Lake', cost: 0 },
      { name: 'Nathula Pass', cost: 0 },
      { name: 'Rumtek Monastery', cost: 10 },
      { name: 'MG Road', cost: 0 },
      { name: 'Hanuman Tok', cost: 0 },
      { name: 'Banjhakri Falls', cost: 10 },
      { name: 'Enchey Monastery', cost: 0 }
    ],
    hotel: 1800,
    food: 600,
    transport: 400
  }
}; 