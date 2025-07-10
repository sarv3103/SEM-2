<?php
// Test login system
require_once 'php/config.php';

echo "Testing login system...\n";

// Check if users table exists and has users
$result = $conn->query("SELECT id, username, email, is_admin FROM users LIMIT 5");
if ($result) {
    echo "Users in database:\n";
    while ($row = $result->fetch_assoc()) {
        echo "- ID: {$row['id']}, Username: {$row['username']}, Email: {$row['email']}, Admin: {$row['is_admin']}\n";
    }
} else {
    echo "Error checking users: " . $conn->error . "\n";
}

// Test login with admin user
echo "\nTesting admin login...\n";
$admin_username = 'admin';
$admin_password = 'admin123';

$stmt = $conn->prepare("SELECT id, username, email, password, is_admin FROM users WHERE username = ? LIMIT 1");
$stmt->bind_param("s", $admin_username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    if (password_verify($admin_password, $user['password'])) {
        echo "✓ Admin login successful\n";
        echo "  User ID: {$user['id']}\n";
        echo "  Username: {$user['username']}\n";
        echo "  Is Admin: {$user['is_admin']}\n";
    } else {
        echo "✗ Admin password verification failed\n";
    }
} else {
    echo "✗ Admin user not found\n";
}

// Create a test regular user if needed
echo "\nChecking for test user...\n";
$test_username = 'testuser';
$test_password = 'test123';
$test_email = 'test@example.com';

$stmt = $conn->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
$stmt->bind_param("s", $test_username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Creating test user...\n";
    $hashed_password = password_hash($test_password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, mobile, username, email, password, is_admin, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
    $first_name = 'Test';
    $last_name = 'User';
    $mobile = '1234567890';
    $is_admin = 0;
    $stmt->bind_param("ssssssi", $first_name, $last_name, $mobile, $test_username, $test_email, $hashed_password, $is_admin);
    
    if ($stmt->execute()) {
        echo "✓ Test user created successfully\n";
        echo "  Username: $test_username\n";
        echo "  Password: $test_password\n";
        echo "  Email: $test_email\n";
    } else {
        echo "✗ Failed to create test user: " . $stmt->error . "\n";
    }
} else {
    echo "✓ Test user already exists\n";
}

echo "\nLogin test complete.\n";
echo "\nTo test the booking system:\n";
echo "1. Go to http://localhost/travelplanner/login.html\n";
echo "2. Login with username: $test_username, password: $test_password\n";
echo "3. Or login with admin: username: admin, password: admin123\n";
echo "4. Then try the booking process again\n";
?> 