<?php
// Test file to show your current IP address
echo "<h2>Your Current IP Address</h2>";
echo "<p><strong>Public IP:</strong> " . $_SERVER['REMOTE_ADDR'] . "</p>";

// Get external IP
$external_ip = file_get_contents('https://api.ipify.org');
echo "<p><strong>External IP:</strong> " . $external_ip . "</p>";

echo "<h3>For SMS Provider Whitelisting:</h3>";
echo "<p>Use this IP address: <strong>" . $external_ip . "</strong></p>";

echo "<h3>For Local Testing:</h3>";
echo "<p>Also whitelist these localhost IPs:</p>";
echo "<ul>";
echo "<li>127.0.0.1</li>";
echo "<li>::1</li>";
echo "</ul>";

echo "<h3>Next Steps:</h3>";
echo "<ol>";
echo "<li>Go to your SMS provider dashboard (MSG91 or TextLocal)</li>";
echo "<li>Navigate to IP Whitelist settings</li>";
echo "<li>Add your IP: <strong>" . $external_ip . "</strong></li>";
echo "<li>Also add localhost IPs for testing</li>";
echo "</ol>";
?> 