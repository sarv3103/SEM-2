<?php
// test_login.php - Test login system
require_once 'php/config.php';

echo "<h2>TravelPlanner Login System Test</h2>";

// Test 1: Database Connection
echo "<h3>1. Database Connection Test</h3>";
if ($conn && !$conn->connect_error) {
    echo "✅ Database connection successful<br>";
} else {
    echo "❌ Database connection failed: " . ($conn->connect_error ?? 'Unknown error') . "<br>";
    exit;
}

// Test 2: Users Table Structure
echo "<h3>2. Users Table Structure</h3>";
$result = $conn->query("DESCRIBE users");
if ($result) {
    echo "✅ Users table exists<br>";
    echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr><td>{$row['Field']}</td><td>{$row['Type']}</td><td>{$row['Null']}</td><td>{$row['Key']}</td><td>{$row['Default']}</td></tr>";
    }
    echo "</table>";
} else {
    echo "❌ Users table does not exist or cannot be accessed<br>";
}

// Test 3: Admin User Check
echo "<h3>3. Admin User Check</h3>";
$stmt = $conn->prepare("SELECT id, username, email, is_admin FROM users WHERE username = ?");
$admin_username = 'admin';
$stmt->bind_param("s", $admin_username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $admin = $result->fetch_assoc();
    echo "✅ Admin user exists<br>";
    echo "Username: {$admin['username']}<br>";
    echo "Email: {$admin['email']}<br>";
    echo "Is Admin: " . ($admin['is_admin'] ? 'Yes' : 'No') . "<br>";
} else {
    echo "❌ Admin user does not exist<br>";
}

// Test 4: All Users Count
echo "<h3>4. Total Users Count</h3>";
$result = $conn->query("SELECT COUNT(*) as count FROM users");
if ($result) {
    $count = $result->fetch_assoc()['count'];
    echo "✅ Total users in database: $count<br>";
} else {
    echo "❌ Cannot count users<br>";
}

// Test 5: Test Admin Login
echo "<h3>5. Admin Login Test</h3>";
$stmt = $conn->prepare("SELECT id, username, password, is_admin FROM users WHERE username = ?");
$stmt->bind_param("s", $admin_username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $admin = $result->fetch_assoc();
    $test_password = 'admin123';
    
    if (password_verify($test_password, $admin['password'])) {
        echo "✅ Admin password verification successful<br>";
        echo "Admin can login with: admin / admin123<br>";
    } else {
        echo "❌ Admin password verification failed<br>";
        echo "Current password hash: " . substr($admin['password'], 0, 20) . "...<br>";
    }
} else {
    echo "❌ Admin user not found for login test<br>";
}

echo "<h3>6. Test Instructions</h3>";
echo "<p>To test login:</p>";
echo "<ol>";
echo "<li>Go to <a href='index.html' target='_blank'>index.html</a> and try logging in with admin/admin123</li>";
echo "<li>Go to <a href='login.html' target='_blank'>login.html</a> and try logging in with admin/admin123</li>";
echo "<li>Register a new user and then try logging in with that user</li>";
echo "</ol>";

echo "<h3>7. Quick Fix Commands</h3>";
echo "<p>If you need to reset the admin user, run these SQL commands:</p>";
echo "<pre>";
echo "DELETE FROM users WHERE username = 'admin';";
echo "INSERT INTO users (first_name, last_name, mobile, username, email, password, is_admin) VALUES ";
echo "('Admin', 'User', '0000000000', 'admin', 'admin@travelplanner.local', '" . password_hash('admin123', PASSWORD_DEFAULT) . "', 1);";
echo "</pre>";
?> 