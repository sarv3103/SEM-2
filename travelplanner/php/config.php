<?php
// php/config.php - Database connection settings
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'travelplanner');

$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "travelplanner";

// Connect to DB
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// Ensure admin user always exists with username 'admin' and password 'admin123'
$admin_username = 'admin';
$admin_password = 'admin123';
$admin_hash = password_hash($admin_password, PASSWORD_DEFAULT);
$check_admin = $conn->query("SELECT id FROM users WHERE username='admin'");
if ($check_admin && $check_admin->num_rows === 0) {
    // Create admin user with all required fields
    $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, mobile, username, email, password, is_admin, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
    $first_name = 'Admin';
    $last_name = 'User';
    $mobile = '0000000000';
    $email = 'admin@travelplanner.local';
    $is_admin = 1;
    $stmt->bind_param("ssssssi", $first_name, $last_name, $mobile, $admin_username, $email, $admin_hash, $is_admin);
    $stmt->execute();
} else if ($check_admin && $check_admin->num_rows > 0) {
    // Always update admin password to admin123 for safety
    $stmt = $conn->prepare("UPDATE users SET password = ?, is_admin = 1 WHERE username = ?");
    $stmt->bind_param("ss", $admin_hash, $admin_username);
    $stmt->execute();
}

// Add payment_id column to payment_orders table
// $conn->query("ALTER TABLE payment_orders ADD COLUMN payment_id VARCHAR(255) DEFAULT NULL");
?>
