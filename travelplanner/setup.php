<?php
// setup.php - Database setup and system check
echo "<h1>TravelPlanner Setup</h1>";

// Check PHP version
echo "<h2>System Requirements Check</h2>";
echo "<p>PHP Version: " . PHP_VERSION . " ";
if (version_compare(PHP_VERSION, '7.4.0', '>=')) {
    echo "✅ (OK)</p>";
} else {
    echo "❌ (Requires PHP 7.4 or higher)</p>";
}

// Check required extensions
$required_extensions = ['mysqli', 'json', 'session'];
echo "<h3>Required Extensions:</h3>";
foreach ($required_extensions as $ext) {
    echo "<p>$ext: ";
    if (extension_loaded($ext)) {
        echo "✅ Loaded</p>";
    } else {
        echo "❌ Not loaded</p>";
    }
}

// Database connection test
echo "<h2>Database Connection Test</h2>";
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "travelplanner";

// Create connection
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) === TRUE) {
    echo "Database '$dbname' created successfully or already exists<br>";
} else {
    echo "Error creating database: " . $conn->error . "<br>";
}

// Select the database
$conn->select_db($dbname);

// Create users table
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    is_admin TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Table 'users' created successfully or already exists<br>";
} else {
    echo "Error creating table: " . $conn->error . "<br>";
}

// Create bookings table
$sql = "CREATE TABLE IF NOT EXISTS bookings (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11),
    name VARCHAR(100) NOT NULL,
    age INT(3) NOT NULL,
    gender VARCHAR(10) NOT NULL,
    type VARCHAR(20) NOT NULL,
    source VARCHAR(100) NOT NULL,
    destination VARCHAR(100) NOT NULL,
    date DATE NOT NULL,
    num_travelers INT(3) NOT NULL,
    fare DECIMAL(10,2) NOT NULL,
    per_person DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
)";

if ($conn->query($sql) === TRUE) {
    echo "Table 'bookings' created successfully or already exists<br>";
} else {
    echo "Error creating table: " . $conn->error . "<br>";
}

// Ensure admin user exists
$admin_username = 'admin';
$admin_password = 'admin123';
$admin_hash = password_hash($admin_password, PASSWORD_DEFAULT);

$check_admin = $conn->query("SELECT id FROM users WHERE username='admin'");
if ($check_admin && $check_admin->num_rows === 0) {
    $sql = "INSERT INTO users (username, password, email, is_admin, created_at) VALUES ('admin', '$admin_hash', 'admin@travelplanner.local', 1, NOW())";
    if ($conn->query($sql) === TRUE) {
        echo "Admin user created successfully<br>";
        echo "Username: admin<br>";
        echo "Password: admin123<br>";
    } else {
        echo "Error creating admin user: " . $conn->error . "<br>";
    }
} else {
    // Update admin password to ensure it's correct
    $sql = "UPDATE users SET password='$admin_hash' WHERE username='admin'";
    $conn->query($sql);
    echo "Admin user already exists, password updated<br>";
    echo "Username: admin<br>";
    echo "Password: admin123<br>";
}

// Create a test user
$test_username = 'test';
$test_password = 'test123';
$test_hash = password_hash($test_password, PASSWORD_DEFAULT);

$check_test = $conn->query("SELECT id FROM users WHERE username='test'");
if ($check_test && $check_test->num_rows === 0) {
    $sql = "INSERT INTO users (username, password, email, created_at) VALUES ('test', '$test_hash', 'test@travelplanner.local', NOW())";
    if ($conn->query($sql) === TRUE) {
        echo "Test user created successfully<br>";
        echo "Username: test<br>";
        echo "Password: test123<br>";
    } else {
        echo "Error creating test user: " . $conn->error . "<br>";
    }
} else {
    echo "Test user already exists<br>";
}

echo "<br><strong>Setup completed successfully!</strong><br>";
echo "<a href='index.html'>Go to TravelPlanner</a><br>";
echo "<a href='login.html'>Go to Login</a><br>";

$conn->close();

// File permissions check
echo "<h2>File Permissions Check</h2>";
$directories = ['php', 'user_bookings'];
foreach ($directories as $dir) {
    if (is_dir($dir)) {
        if (is_writable($dir)) {
            echo "<p>✅ Directory '$dir' is writable</p>";
        } else {
            echo "<p>❌ Directory '$dir' is not writable</p>";
        }
    } else {
        echo "<p>❌ Directory '$dir' does not exist</p>";
    }
}

// Test admin login
echo "<h2>Admin Login Test</h2>";
echo "<p>Default admin credentials:</p>";
echo "<ul>";
echo "<li>Username: admin</li>";
echo "<li>Password: admin123</li>";
echo "</ul>";

echo "<h2>Setup Instructions</h2>";
echo "<ol>";
echo "<li>Make sure XAMPP is running (Apache and MySQL)</li>";
echo "<li>Import database.sql into phpMyAdmin</li>";
echo "<li>Access your website at: http://localhost/travelplanner/</li>";
echo "<li>Test the login functionality with admin/admin123</li>";
echo "<li>Test the booking and contact forms</li>";
echo "</ol>";

echo "<h2>Common Issues & Solutions</h2>";
echo "<h3>If packages don't display:</h3>";
echo "<ul>";
echo "<li>Check browser console for JavaScript errors</li>";
echo "<li>Make sure script.js is loading properly</li>";
echo "<li>Verify that the package containers exist in HTML</li>";
echo "</ul>";

echo "<h3>If login doesn't work:</h3>";
echo "<ul>";
echo "<li>Check if sessions are enabled</li>";
echo "<li>Verify database connection</li>";
echo "<li>Check if users table exists and has data</li>";
echo "</ul>";

echo "<h3>If booking doesn't work:</h3>";
echo "<ul>";
echo "<li>Check if bookings table exists</li>";
echo "<li>Verify user is logged in</li>";
echo "<li>Check PHP error logs</li>";
echo "</ul>";

echo "<p><strong>For support:</strong> Check the browser console (F12) for any JavaScript errors and the PHP error logs for server-side issues.</p>";
?> 