<?php
// Simple test to verify database and admin user
require_once 'php/config.php';

echo "<h2>Quick Database Test</h2>";

// Test database connection
if ($conn) {
    echo "✅ Database connected<br>";
    
    // Test admin user
    $stmt = $conn->prepare("SELECT id, username, email, is_admin FROM users WHERE username = 'admin'");
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $admin = $result->fetch_assoc();
        echo "✅ Admin user exists<br>";
        echo "Username: " . $admin['username'] . "<br>";
        echo "Email: " . $admin['email'] . "<br>";
        echo "Admin: " . ($admin['is_admin'] ? 'Yes' : 'No') . "<br>";
        
        // Test password
        $test_password = 'admin123';
        $stmt2 = $conn->prepare("SELECT password FROM users WHERE username = 'admin'");
        $stmt2->execute();
        $result2 = $stmt2->get_result();
        $admin_data = $result2->fetch_assoc();
        
        if (password_verify($test_password, $admin_data['password'])) {
            echo "✅ Admin password is correct (admin123)<br>";
        } else {
            echo "❌ Admin password needs reset<br>";
            // Reset password
            $new_hash = password_hash('admin123', PASSWORD_DEFAULT);
            $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = 'admin'");
            $update_stmt->bind_param("s", $new_hash);
            if ($update_stmt->execute()) {
                echo "✅ Admin password reset to 'admin123'<br>";
            }
        }
    } else {
        echo "❌ Admin user not found<br>";
    }
    
    // Test tables
    $tables = ['users', 'bookings', 'contact_messages'];
    foreach ($tables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result->num_rows > 0) {
            echo "✅ Table '$table' exists<br>";
        } else {
            echo "❌ Table '$table' missing<br>";
        }
    }
    
} else {
    echo "❌ Database connection failed<br>";
}

echo "<br><strong>Test completed!</strong>";
?> 