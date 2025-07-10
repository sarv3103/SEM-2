<?php
// Test Razorpay Setup
require_once 'vendor/autoload.php';

use Razorpay\Api\Api;

echo "<h2>Razorpay Setup Test</h2>";

// Test 1: Check if Razorpay SDK is installed
echo "<h3>1. Checking Razorpay SDK Installation</h3>";
if (class_exists('Razorpay\Api\Api')) {
    echo "✅ Razorpay SDK is installed<br>";
} else {
    echo "❌ Razorpay SDK is not installed<br>";
    echo "Run: composer require razorpay/razorpay<br>";
    exit();
}

// Test 2: Check configuration file
echo "<h3>2. Checking Configuration File</h3>";
if (file_exists('php/razorpay_config.php')) {
    echo "✅ Razorpay config file exists<br>";
    
    // Check if keys are set
    $configContent = file_get_contents('php/razorpay_config.php');
    if (strpos($configContent, 'rzp_test_YOUR_KEY_ID') !== false) {
        echo "❌ Please update your Razorpay API keys in php/razorpay_config.php<br>";
    } else {
        echo "✅ API keys appear to be configured<br>";
    }
} else {
    echo "❌ Razorpay config file not found<br>";
}

// Test 3: Check database table
echo "<h3>3. Checking Database Table</h3>";
require_once 'php/config.php';

$stmt = $conn->prepare("SHOW TABLES LIKE 'payment_orders'");
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "✅ Payment orders table exists<br>";
} else {
    echo "❌ Payment orders table not found<br>";
    echo "Run the SQL script: create_payment_orders_table.sql<br>";
}

// Test 4: Check PHP extensions
echo "<h3>4. Checking PHP Extensions</h3>";
$requiredExtensions = ['curl', 'json', 'openssl'];
foreach ($requiredExtensions as $ext) {
    if (extension_loaded($ext)) {
        echo "✅ $ext extension is loaded<br>";
    } else {
        echo "❌ $ext extension is not loaded<br>";
    }
}

// Test 5: Check file permissions
echo "<h3>5. Checking File Permissions</h3>";
$filesToCheck = [
    'php/process_payment.php',
    'php/verify_payment.php',
    'php/razorpay_config.php'
];

foreach ($filesToCheck as $file) {
    if (file_exists($file)) {
        if (is_readable($file)) {
            echo "✅ $file is readable<br>";
        } else {
            echo "❌ $file is not readable<br>";
        }
    } else {
        echo "❌ $file does not exist<br>";
    }
}

echo "<h3>6. Next Steps</h3>";
echo "1. Install Razorpay SDK: composer require razorpay/razorpay<br>";
echo "2. Get your API keys from Razorpay Dashboard<br>";
echo "3. Update php/razorpay_config.php with your keys<br>";
echo "4. Create payment_orders table if not exists<br>";
echo "5. Test with a small amount first<br>";

echo "<h3>7. Test Payment Flow</h3>";
echo "<a href='test_payment_flow.php'>Test Payment Flow</a>";
?> 