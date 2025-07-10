<?php
echo "<h2>Razorpay Key Setup Guide</h2>";

echo "<h3>Step 1: Get Your Test Keys</h3>";
echo "<ol>";
echo "<li>Go to <a href='https://dashboard.razorpay.com' target='_blank'>Razorpay Dashboard</a></li>";
echo "<li>Login to your account</li>";
echo "<li>Go to <strong>Settings → API Keys</strong></li>";
echo "<li>Click <strong>'Generate Key Pair'</strong></li>";
echo "<li>Select <strong>'Test Mode'</strong></li>";
echo "<li>Copy your <strong>Key ID</strong> and <strong>Key Secret</strong></li>";
echo "</ol>";

echo "<h3>Step 2: Update Your Configuration</h3>";
echo "<p>Once you have your keys, update these files:</p>";

echo "<h4>1. Update php/razorpay_config.php:</h4>";
echo "<pre style='background: #f4f4f4; padding: 10px; border-radius: 5px;'>";
echo "public function __construct() {
    // Replace with your actual test keys from Razorpay Dashboard
    \$keyId = 'rzp_test_YOUR_ACTUAL_KEY_ID'; // Your test Key ID
    \$keySecret = 'YOUR_ACTUAL_KEY_SECRET'; // Your test Key Secret
    
    \$this->api = new Api(\$keyId, \$keySecret);
}";
echo "</pre>";

echo "<h4>2. Update php/process_payment.php:</h4>";
echo "<pre style='background: #f4f4f4; padding: 10px; border-radius: 5px;'>";
echo "'key_id' => 'rzp_test_YOUR_ACTUAL_KEY_ID' // Your test key";
echo "</pre>";

echo "<h4>3. Update simple_payment_test.php:</h4>";
echo "<pre style='background: #f4f4f4; padding: 10px; border-radius: 5px;'>";
echo "key: 'rzp_test_YOUR_ACTUAL_KEY_ID', // Your test key";
echo "</pre>";

echo "<h3>Step 3: Test Keys Format</h3>";
echo "<p>Your keys should look like this:</p>";
echo "<ul>";
echo "<li><strong>Key ID:</strong> rzp_test_xxxxxxxxxxxxxxx (starts with 'rzp_test_')</li>";
echo "<li><strong>Key Secret:</strong> xxxxxxxxxxxxxxxxxxxxxx (alphanumeric, no spaces)</li>";
echo "</ul>";

echo "<h3>Step 4: Common Issues</h3>";
echo "<ul>";
echo "<li><strong>Wrong Key Format:</strong> Make sure Key ID starts with 'rzp_test_' for test mode</li>";
echo "<li><strong>Live Keys:</strong> Don't use live keys (rzp_live_) for testing</li>";
echo "<li><strong>Account Status:</strong> Make sure your Razorpay account is active</li>";
echo "<li><strong>Key Permissions:</strong> Ensure keys have payment permissions</li>";
echo "</ul>";

echo "<h3>Step 5: Alternative - Use Standard Test Keys</h3>";
echo "<p>If you can't get your own test keys, you can use these standard test keys:</p>";
echo "<pre style='background: #f4f4f4; padding: 10px; border-radius: 5px;'>";
echo "Key ID: rzp_test_1DP5mmOlF5G5ag
Key Secret: thisisatestkey";
echo "</pre>";

echo "<p><strong>Note:</strong> Standard test keys may not work with all features. It's better to use your own test keys.</p>";

echo "<h3>Step 6: Test After Updating</h3>";
echo "<p>After updating your keys:</p>";
echo "<ol>";
echo "<li>Login to your account</li>";
echo "<li>Visit: <a href='simple_payment_test.php'>simple_payment_test.php</a></li>";
echo "<li>Click 'Test Payment'</li>";
echo "<li>Use test card: 4111 1111 1111 1111</li>";
echo "</ol>";

echo "<hr>";
echo "<p><strong>Need Help?</strong> Contact Razorpay Support at support@razorpay.com</p>";
?> 