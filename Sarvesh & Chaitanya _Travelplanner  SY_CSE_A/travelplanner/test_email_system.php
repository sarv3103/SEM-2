<?php
session_start();
require_once 'php/config.php';
require_once 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

echo "<h2>Email System Test</h2>";

if (!isset($_SESSION['user_id'])) {
    echo "<p>Please <a href='login.html'>login first</a> to test the email system.</p>";
    exit();
}

// Get user email
$userId = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo "<p>User not found.</p>";
    exit();
}

$userEmail = $user['email'];

echo "<h3>Testing Email System</h3>";
echo "<p>Testing with email: $userEmail</p>";

try {
    $mail = new PHPMailer(true);
    
    // Server settings
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'your-email@gmail.com'; // Replace with your Gmail
    $mail->Password = 'your-app-password'; // Replace with your app password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    
    // Recipients
    $mail->setFrom('your-email@gmail.com', 'TravelPlanner');
    $mail->addAddress($userEmail);
    
    // Content
    $mail->isHTML(true);
    $mail->Subject = 'TravelPlanner - Email System Test';
    $mail->Body = "
        <h2>Email System Test</h2>
        <p>This is a test email from your TravelPlanner system.</p>
        <p>If you received this email, your email system is working correctly!</p>
        <p>Test Time: " . date('Y-m-d H:i:s') . "</p>
    ";
    
    $mail->send();
    echo "<p style='color: green;'>✅ Test email sent successfully!</p>";
    echo "<p>Check your inbox at: $userEmail</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Email could not be sent. Error: {$mail->ErrorInfo}</p>";
    echo "<p><strong>Note:</strong> You need to configure your Gmail credentials in this file.</p>";
}

echo "<h3>Email Configuration Required</h3>";
echo "<p>To make emails work in production:</p>";
echo "<ol>";
echo "<li>Update Gmail credentials in this file</li>";
echo "<li>Use Gmail App Password (not regular password)</li>";
echo "<li>Enable 2-factor authentication on Gmail</li>";
echo "<li>Generate App Password for this application</li>";
echo "</ol>";

echo "<p><a href='test_complete_booking.php'>← Back to Complete Booking Test</a></p>";
?> 