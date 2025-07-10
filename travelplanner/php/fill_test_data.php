<?php
// fill_test_data.php - Insert multiple test users, bookings, payments, and messages for admin dashboard demo
$conn = new mysqli('localhost','root','','travelplanner');
if ($conn->connect_error) die('DB error');

// Insert multiple test users
$users = [
    [1001, 'demouser', 'demo@example.com', 'demo123', 0],
    [1002, 'alice', 'alice@example.com', 'alice123', 0],
    [1003, 'bob', 'bob@example.com', 'bob123', 0],
    [1004, 'charlie', 'charlie@example.com', 'charlie123', 0],
];
foreach ($users as $u) {
    $conn->query("INSERT IGNORE INTO users (id, username, email, password, is_admin, created_at, is_verified) VALUES ($u[0], '$u[1]', '$u[2]', '" . password_hash($u[3], PASSWORD_DEFAULT) . "', $u[4], NOW(), 1)");
}

// Insert bookings for each user
$bookings = [
    [1001, 'Demo Booking', 30, 'male', 'holiday', 'Mumbai', 'Goa', '2024-06-10', 2, 3000, 1500],
    [1002, 'Alice Trip', 28, 'female', 'adventure', 'Delhi', 'Kerala', '2024-07-15', 1, 5000, 5000],
    [1003, 'Bob Vacation', 35, 'male', 'family', 'Bangalore', 'Maldives', '2024-08-20', 4, 20000, 5000],
    [1004, 'Charlie Tour', 40, 'male', 'luxury', 'Chennai', 'Thailand', '2024-09-05', 2, 12000, 6000],
];
$booking_ids = [];
foreach ($bookings as $b) {
    $conn->query("INSERT INTO bookings (user_id, name, age, gender, type, source, destination, date, num_travelers, fare, per_person, created_at) VALUES ($b[0], '$b[1]', $b[2], '$b[3]', '$b[4]', '$b[5]', '$b[6]', '$b[7]', $b[8], $b[9], $b[10], NOW())");
    $booking_ids[] = $conn->insert_id;
}

// Insert payments for each booking
$payments = [
    [$booking_ids[0], 1001, 'ORDERDEMO123', 3000, 'pending'],
    [$booking_ids[1], 1002, 'ORDERALICE456', 5000, 'completed'],
    [$booking_ids[2], 1003, 'ORDERBOB789', 20000, 'pending'],
    [$booking_ids[3], 1004, 'ORDERCHARLIE321', 12000, 'completed'],
];
foreach ($payments as $p) {
    $conn->query("INSERT INTO payment_orders (booking_id, user_id, order_id, amount, status, created_at) VALUES ($p[0], $p[1], '$p[2]', $p[3], '$p[4]', NOW())");
}

// Insert contact messages
$messages = [
    ['Demo User', 'demo@example.com', 'This is a test message for the admin dashboard.'],
    ['Alice', 'alice@example.com', 'Can I get a discount for group booking?'],
    ['Bob', 'bob@example.com', 'What is the refund policy?'],
    ['Charlie', 'charlie@example.com', 'How do I change my travel dates?'],
];
foreach ($messages as $m) {
    $conn->query("INSERT INTO contact_messages (name, email, message, status, created_at) VALUES ('$m[0]', '$m[1]', '$m[2]', 'unread', NOW())");
}

$conn->close();
echo '✅ Multiple test data inserted.'; 