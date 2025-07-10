<?php
// check_sms_status.php - Comprehensive SMS status checker
require_once 'php/config.php';
require_once 'php/sms_config.php';

echo "<h1>SMS OTP Status Checker</h1>";

// Check 1: SMS Service Configuration
echo "<h2>1. SMS Service Configuration</h2>";
try {
    $smsService = new SMSService();
    echo "✅ SMS Service created successfully<br>";
    
    // Check API key configuration
    $reflection = new ReflectionClass($smsService);
    $apiKeyProperty = $reflection->getProperty('apiKey');
    $apiKeyProperty->setAccessible(true);
    $apiKey = $apiKeyProperty->getValue($smsService);
    
    if ($apiKey === 'your-textlocal-api-key') {
        echo "<div style='background: #ffebee; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
        echo "<strong>❌ ISSUE FOUND:</strong> SMS API key not configured!<br>";
        echo "You need to update php/sms_config.php with your TextLocal API key<br>";
        echo "</div>";
        
        echo "<h3>How to Fix:</h3>";
        echo "<ol>";
        echo "<li>Go to <a href='https://www.textlocal.in/' target='_blank'>TextLocal</a></li>";
        echo "<li>Sign up and get your API key</li>";
        echo "<li>Update php/sms_config.php with your API key</li>";
        echo "</ol>";
        
        exit;
    } else {
        echo "✅ SMS API key configured<br>";
        echo "API Key: " . substr($apiKey, 0, 8) . "..." . substr($apiKey, -4) . "<br>";
    }
    
} catch (Exception $e) {
    echo "❌ SMS Service error: " . $e->getMessage() . "<br>";
    exit;
}

// Check 2: Database SMS Log
echo "<h2>2. SMS Log Analysis</h2>";
try {
    $result = $conn->query("SELECT * FROM sms_log ORDER BY created_at DESC LIMIT 5");
    
    if ($result->num_rows > 0) {
        echo "✅ SMS logs found. Recent SMS attempts:<br>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
        echo "<tr><th>Mobile</th><th>OTP</th><th>Success</th><th>Time</th><th>Response</th></tr>";
        
        while ($row = $result->fetch_assoc()) {
            $success = $row['success'] ? '✅ Yes' : '❌ No';
            $response = json_decode($row['api_response'], true);
            $responseText = isset($response['message']) ? $response['message'] : 'N/A';
            
            echo "<tr>";
            echo "<td>" . $row['mobile'] . "</td>";
            echo "<td>" . $row['otp'] . "</td>";
            echo "<td>" . $success . "</td>";
            echo "<td>" . $row['created_at'] . "</td>";
            echo "<td>" . substr($responseText, 0, 50) . "...</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "⚠️ No SMS logs found. No SMS has been sent yet.<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
}

// Check 3: Test SMS Sending
echo "<h2>3. Test SMS Sending</h2>";
echo "<form method='post' style='background: #f5f5f5; padding: 15px; border-radius: 5px;'>";
echo "<p><strong>Test SMS to your mobile:</strong></p>";
echo "<input type='text' name='test_mobile' placeholder='Enter your mobile number (e.g., 9876543210)' style='width: 250px; padding: 5px;'><br><br>";
echo "<input type='submit' value='Send Test SMS' style='background: #0077cc; color: white; padding: 8px 15px; border: none; border-radius: 3px; cursor: pointer;'>";
echo "</form>";

if ($_POST['test_mobile']) {
    $testMobile = $_POST['test_mobile'];
    $testOtp = '123456';
    
    echo "<h3>Test Results:</h3>";
    echo "Sending test SMS to: $testMobile<br>";
    
    $result = $smsService->sendOTP($testMobile, $testOtp, 'test');
    
    if ($result) {
        echo "<div style='background: #e8f5e8; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
        echo "✅ SMS sent successfully!<br>";
        echo "Check your phone for OTP: $testOtp<br>";
        echo "</div>";
    } else {
        echo "<div style='background: #ffebee; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
        echo "❌ SMS sending failed!<br>";
        echo "Check the SMS logs above for error details.<br>";
        echo "</div>";
    }
}

// Check 4: Common Issues
echo "<h2>4. Common Issues & Solutions</h2>";
echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px;'>";
echo "<h3>If SMS is not received:</h3>";
echo "<ul>";
echo "<li><strong>API Key:</strong> Make sure your TextLocal API key is correct</li>";
echo "<li><strong>Credits:</strong> Check if you have SMS credits in your TextLocal account</li>";
echo "<li><strong>Mobile Format:</strong> Use 10-digit number (e.g., 9876543210) - system will add +91</li>";
echo "<li><strong>Network:</strong> Check your mobile network and signal</li>";
echo "<li><strong>DND:</strong> Make sure your number is not in DND (Do Not Disturb) list</li>";
echo "<li><strong>Spam:</strong> Check spam/junk folder in your SMS app</li>";
echo "</ul>";
echo "</div>";

// Check 5: Alternative SMS Service
echo "<h2>5. Alternative SMS Service (MSG91)</h2>";
echo "<p>If TextLocal is not working, you can try MSG91:</p>";
echo "<ol>";
echo "<li>Sign up at <a href='https://msg91.com/' target='_blank'>MSG91</a></li>";
echo "<li>Get free credits and API key</li>";
echo "<li>Update the MSG91 section in php/sms_config.php</li>";
echo "</ol>";

echo "<h2>6. Next Steps</h2>";
echo "<ul>";
echo "<li><a href='test_sms_config.php'>🧪 Run SMS Configuration Test</a></li>";
echo "<li><a href='php/admin_otp_logs.php'>📊 View Detailed OTP Logs</a></li>";
echo "<li><a href='register.html'>🧪 Test Registration with SMS OTP</a></li>";
echo "</ul>";

echo "<p><strong>Note:</strong> Email OTP is working perfectly. You can use the system with email-only OTPs while fixing SMS.</p>";
?> 