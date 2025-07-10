<?php
// debug_email.php - Debug email configuration issues
require_once 'php/email_config.php';

echo "<h1>Email Configuration Debug</h1>";

try {
    $emailService = new EmailService();
    echo "✅ Email Service created successfully<br>";
    
    // Check configuration using reflection
    $reflection = new ReflectionClass($emailService);
    $mailerProperty = $reflection->getProperty('mailer');
    $mailerProperty->setAccessible(true);
    $mailer = $mailerProperty->getValue($emailService);
    
    echo "<h2>Current Configuration:</h2>";
    echo "<ul>";
    echo "<li><strong>Username:</strong> " . $mailer->Username . "</li>";
    echo "<li><strong>Password Length:</strong> " . strlen($mailer->Password) . " characters</li>";
    echo "<li><strong>Password Preview:</strong> " . substr($mailer->Password, 0, 4) . "..." . substr($mailer->Password, -4) . "</li>";
    echo "<li><strong>Host:</strong> " . $mailer->Host . "</li>";
    echo "<li><strong>Port:</strong> " . $mailer->Port . "</li>";
    echo "<li><strong>SMTP Auth:</strong> " . ($mailer->SMTPAuth ? 'Enabled' : 'Disabled') . "</li>";
    echo "</ul>";
    
    // Check if password looks like an app password
    if (strlen($mailer->Password) !== 16) {
        echo "<div style='background: #ffebee; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
        echo "<strong>⚠️ Issue Found:</strong> App password should be exactly 16 characters. Current length: " . strlen($mailer->Password);
        echo "</div>";
    }
    
    if (strpos($mailer->Password, '@') !== false || strpos($mailer->Password, '123') !== false) {
        echo "<div style='background: #ffebee; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
        echo "<strong>⚠️ Issue Found:</strong> This looks like a regular password, not an app password.";
        echo "</div>";
    }
    
    echo "<h2>Test SMTP Connection</h2>";
    
    // Test SMTP connection without sending email
    $mailer->SMTPDebug = 0; // Disable debug output
    $mailer->Timeout = 10; // Set timeout to 10 seconds
    
    try {
        // Just test the connection
        $mailer->smtpConnect();
        echo "✅ SMTP connection test successful<br>";
    } catch (Exception $e) {
        echo "❌ SMTP connection failed: " . $e->getMessage() . "<br>";
        
        // Provide specific troubleshooting based on error
        if (strpos($e->getMessage(), 'Authentication failed') !== false) {
            echo "<div style='background: #fff3cd; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
            echo "<strong>🔧 Solution:</strong> Authentication failed. Please:<br>";
            echo "1. Go to <a href='https://myaccount.google.com/' target='_blank'>Google Account Settings</a><br>";
            echo "2. Enable 2-Step Verification<br>";
            echo "3. Create an App Password for 'TravelPlanner'<br>";
            echo "4. Update the password in php/email_config.php<br>";
            echo "</div>";
        } elseif (strpos($e->getMessage(), 'Connection failed') !== false) {
            echo "<div style='background: #fff3cd; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
            echo "<strong>🔧 Solution:</strong> Connection failed. Please check:<br>";
            echo "1. Internet connection is working<br>";
            echo "2. Firewall is not blocking SMTP connections<br>";
            echo "3. Try again in a few minutes<br>";
            echo "</div>";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Email Service error: " . $e->getMessage() . "<br>";
}

echo "<h2>Next Steps</h2>";
echo "<ol>";
echo "<li>Follow the <a href='GMAIL_SETUP_GUIDE.md'>Gmail Setup Guide</a></li>";
echo "<li>Get a proper 16-character app password</li>";
echo "<li>Update php/email_config.php</li>";
echo "<li>Run this debug script again</li>";
echo "</ol>";

echo "<p><a href='test_email_config.php'>🧪 Test Email Again</a></p>";
?> 