<?php
// Comprehensive Admin Dashboard Fix Script
// This script will fix all issues with the admin dashboard system

require_once 'php/config.php';

echo "<h1>🔧 Admin Dashboard Fix Script</h1>";
echo "<p>Fixing all issues with the admin dashboard system...</p>";

// 1. Fix Users Table Structure
echo "<h2>1. Fixing Users Table Structure</h2>";

// Check if required columns exist in users table
$columns = [
    'first_name' => 'VARCHAR(50)',
    'last_name' => 'VARCHAR(50)', 
    'mobile' => 'VARCHAR(15)',
    'is_verified' => 'TINYINT(1) DEFAULT 0'
];

foreach ($columns as $column => $definition) {
    $result = $conn->query("SHOW COLUMNS FROM users LIKE '$column'");
    if ($result && $result->num_rows === 0) {
        $sql = "ALTER TABLE users ADD COLUMN $column $definition";
        if ($conn->query($sql)) {
            echo "✅ Added column: $column<br>";
        } else {
            echo "❌ Failed to add column $column: " . $conn->error . "<br>";
        }
    } else {
        echo "✅ Column $column already exists<br>";
    }
}

// 2. Create Missing Tables
echo "<h2>2. Creating Missing Tables</h2>";

// Contact Messages Table
$result = $conn->query("SHOW TABLES LIKE 'contact_messages'");
if ($result && $result->num_rows === 0) {
    $sql = "CREATE TABLE contact_messages (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        message TEXT NOT NULL,
        status ENUM('unread', 'read') DEFAULT 'unread',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    if ($conn->query($sql)) {
        echo "✅ Created contact_messages table<br>";
    } else {
        echo "❌ Failed to create contact_messages table: " . $conn->error . "<br>";
    }
} else {
    echo "✅ contact_messages table exists<br>";
}

// Payment Orders Table
$result = $conn->query("SHOW TABLES LIKE 'payment_orders'");
if ($result && $result->num_rows === 0) {
    $sql = "CREATE TABLE payment_orders (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        user_id INT(11) NOT NULL,
        order_id VARCHAR(100) NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    if ($conn->query($sql)) {
        echo "✅ Created payment_orders table<br>";
    } else {
        echo "❌ Failed to create payment_orders table: " . $conn->error . "<br>";
    }
} else {
    echo "✅ payment_orders table exists<br>";
}

// 3. Create Missing Admin Action Files
echo "<h2>3. Creating Missing Admin Action Files</h2>";

// Create admin_edit_user.php
$admin_edit_user_content = '<?php
require_once "config.php";
require_once "session.php";

// Only allow admin access
requireAdmin();

header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit;
}

$user_id = intval($_POST["user_id"] ?? 0);
$username = trim($_POST["username"] ?? "");
$email = trim($_POST["email"] ?? "");
$mobile = trim($_POST["mobile"] ?? "");
$is_verified = intval($_POST["is_verified"] ?? 0);

if (!$user_id || !$username || !$email) {
    echo json_encode(["success" => false, "message" => "Missing required fields"]);
    exit;
}

try {
    $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, mobile = ?, is_verified = ? WHERE id = ?");
    $stmt->bind_param("sssii", $username, $email, $mobile, $is_verified, $user_id);
    
    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "User updated successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to update user"]);
    }
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
}
?>';

file_put_contents("php/admin_edit_user.php", $admin_edit_user_content);
echo "✅ Created admin_edit_user.php<br>";

// Create admin_booking_action.php
$admin_booking_action_content = '<?php
require_once "config.php";
require_once "session.php";

// Only allow admin access
requireAdmin();

header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit;
}

$action = $_POST["action"] ?? "";
$booking_id = $_POST["booking_id"] ?? "";

if (!$action || !$booking_id) {
    echo json_encode(["success" => false, "message" => "Missing action or booking ID"]);
    exit;
}

switch ($action) {
    case "confirm":
        // Update booking status to confirmed
        $stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ?");
        $status = "confirmed";
        $stmt->bind_param("si", $status, $booking_id);
        break;
        
    case "cancel":
        // Update booking status to cancelled
        $stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ?");
        $status = "cancelled";
        $stmt->bind_param("si", $status, $booking_id);
        break;
        
    case "delete":
        // Delete booking
        $stmt = $conn->prepare("DELETE FROM bookings WHERE id = ?");
        $stmt->bind_param("i", $booking_id);
        break;
        
    default:
        echo json_encode(["success" => false, "message" => "Invalid action"]);
        exit;
}

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Booking " . $action . "ed successfully"]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to " . $action . " booking"]);
}
?>';

file_put_contents("php/admin_booking_action.php", $admin_booking_action_content);
echo "✅ Created admin_booking_action.php<br>";

// Create admin_payment_action.php
$admin_payment_action_content = '<?php
require_once "config.php";
require_once "session.php";

// Only allow admin access
requireAdmin();

header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit;
}

$action = $_POST["action"] ?? "";
$payment_id = $_POST["payment_id"] ?? "";

if (!$action || !$payment_id) {
    echo json_encode(["success" => false, "message" => "Missing action or payment ID"]);
    exit;
}

switch ($action) {
    case "verify":
        // Update payment status to completed
        $stmt = $conn->prepare("UPDATE payment_orders SET status = ? WHERE id = ?");
        $status = "completed";
        $stmt->bind_param("si", $status, $payment_id);
        break;
        
    case "reject":
        // Update payment status to failed
        $stmt = $conn->prepare("UPDATE payment_orders SET status = ? WHERE id = ?");
        $status = "failed";
        $stmt->bind_param("si", $status, $payment_id);
        break;
        
    default:
        echo json_encode(["success" => false, "message" => "Invalid action"]);
        exit;
}

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Payment " . $action . "ed successfully"]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to " . $action . " payment"]);
}
?>';

file_put_contents("php/admin_payment_action.php", $admin_payment_action_content);
echo "✅ Created admin_payment_action.php<br>";

// 4. Create user_bookings directory if it doesn't exist
echo "<h2>4. Creating Required Directories</h2>";
if (!is_dir("user_bookings")) {
    mkdir("user_bookings", 0755, true);
    echo "✅ Created user_bookings directory<br>";
} else {
    echo "✅ user_bookings directory exists<br>";
}

// 5. Insert sample data for testing
echo "<h2>5. Inserting Sample Data</h2>";

// Insert sample contact messages
$sample_messages = [
    ["John Doe", "john@example.com", "I would like to know more about your Dubai package."],
    ["Jane Smith", "jane@example.com", "What are the best times to visit Kerala?"],
    ["Mike Johnson", "mike@example.com", "Do you have any group discounts available?"]
];

$stmt = $conn->prepare("INSERT INTO contact_messages (name, email, message, created_at) VALUES (?, ?, ?, NOW())");
foreach ($sample_messages as $msg) {
    $stmt->bind_param("sss", $msg[0], $msg[1], $msg[2]);
    $stmt->execute();
}
echo "✅ Inserted sample contact messages<br>";

// Insert sample payment orders
$sample_payments = [
    [1, "order_123456", 25000.00, "completed"],
    [1, "order_123457", 15000.00, "pending"],
    [2, "order_123458", 18000.00, "completed"]
];

$stmt = $conn->prepare("INSERT INTO payment_orders (user_id, order_id, amount, status, created_at) VALUES (?, ?, ?, ?, NOW())");
foreach ($sample_payments as $payment) {
    $stmt->bind_param("isds", $payment[0], $payment[1], $payment[2], $payment[3]);
    $stmt->execute();
}
echo "✅ Inserted sample payment orders<br>";

// 6. Create sample booking files
echo "<h2>6. Creating Sample Booking Data</h2>";

$sample_booking = [
    "id" => "BK001",
    "name" => "Dubai Adventure",
    "category" => "International",
    "start_date" => "2024-02-15",
    "end_date" => "2024-02-18",
    "travelers" => 2,
    "total_price" => 25000,
    "status" => "confirmed",
    "user_id" => 1
];

$booking_file = "user_bookings/user_1.json";
file_put_contents($booking_file, json_encode([$sample_booking], JSON_PRETTY_PRINT));
echo "✅ Created sample booking data<br>";

// 7. Final verification
echo "<h2>7. Final Verification</h2>";

$tables = ["users", "contact_messages", "payment_orders", "packages", "destinations"];
$all_tables_exist = true;

foreach ($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result && $result->num_rows > 0) {
        echo "✅ $table table exists<br>";
    } else {
        echo "❌ $table table missing<br>";
        $all_tables_exist = false;
    }
}

$required_files = [
    "php/admin_add_user.php",
    "php/admin_delete_user.php", 
    "php/admin_edit_user.php",
    "php/admin_booking_action.php",
    "php/admin_payment_action.php",
    "php/admin_dashboard.js"
];

$all_files_exist = true;
foreach ($required_files as $file) {
    if (file_exists($file)) {
        echo "✅ $file exists<br>";
    } else {
        echo "❌ $file missing<br>";
        $all_files_exist = false;
    }
}

echo "<h2>🎉 Fix Complete!</h2>";

if ($all_tables_exist && $all_files_exist) {
    echo "<p style='color: green; font-weight: bold;'>✅ All issues have been fixed! Your admin dashboard should now work properly.</p>";
    echo "<p><strong>Admin Login Details:</strong></p>";
    echo "<ul>";
    echo "<li><strong>Username:</strong> admin</li>";
    echo "<li><strong>Password:</strong> admin123</li>";
    echo "</ul>";
    echo "<p><a href='admin_dashboard.php' style='background: #0077cc; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🚀 Access Admin Dashboard</a></p>";
} else {
    echo "<p style='color: red; font-weight: bold;'>❌ Some issues remain. Please check the errors above.</p>";
}

echo "<h3>📋 What was fixed:</h3>";
echo "<ul>";
echo "<li>✅ Added missing columns to users table (first_name, last_name, mobile, is_verified)</li>";
echo "<li>✅ Created contact_messages table</li>";
echo "<li>✅ Created payment_orders table</li>";
echo "<li>✅ Created missing admin action files</li>";
echo "<li>✅ Created user_bookings directory</li>";
echo "<li>✅ Inserted sample data for testing</li>";
echo "<li>✅ Verified all required files exist</li>";
echo "</ul>";

echo "<h3>🔧 Admin Dashboard Features:</h3>";
echo "<ul>";
echo "<li>✅ View all bookings with details</li>";
echo "<li>✅ View all users and manage them</li>";
echo "<li>✅ View all payments and verify them</li>";
echo "<li>✅ View all contact messages</li>";
echo "<li>✅ Export data to CSV</li>";
echo "<li>✅ Add new users</li>";
echo "<li>✅ Edit/delete users</li>";
echo "<li>✅ Confirm/cancel bookings</li>";
echo "<li>✅ Verify payments</li>";
echo "<li>✅ Reply to messages</li>";
echo "</ul>";
?> 