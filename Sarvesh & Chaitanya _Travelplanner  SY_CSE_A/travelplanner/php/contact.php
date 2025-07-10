<?php
// php/contact.php: Handle contact form submissions with OTP verification
require 'config.php';
require_once 'otp_manager.php';

header('Content-Type: application/json');
// Suppress PHP errors from being output as HTML
ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? 'submit';
        
        if ($action === 'send_otp') {
            // Generate and send OTP
            $email = $_POST['email'] ?? '';
            
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo json_encode(['status' => 'error', 'msg' => 'Valid email is required']);
                exit;
            }
            
            // Initialize OTP Manager
            $otpManager = new OTPManager($conn);
            
            // Generate email OTP
            $emailOTP = $otpManager->generateOTP();
            $emailSent = $otpManager->sendEmailOTP($email, $emailOTP, 'contact form verification');
            
            // Store contact OTP
            $otpManager->storeContactOTP($email, $emailOTP);
            
            echo json_encode([
                'status' => 'success',
                'msg' => 'OTP sent to your email for verification',
                'email_sent' => $emailSent
            ]);
            
        } elseif ($action === 'submit') {
            // Submit contact form with OTP verification
            $name = $_POST['name'] ?? '';
            $email = $_POST['email'] ?? '';
            $phone = $_POST['phone'] ?? '';
            $subject = $_POST['subject'] ?? '';
            $message = $_POST['message'] ?? '';
            $emailOTP = $_POST['email_otp'] ?? '';

            if (empty($name) || empty($email) || empty($message)) {
                echo json_encode(['status' => 'error', 'msg' => 'Please fill in all fields']);
                exit;
            }

            // Validate email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo json_encode(['status' => 'error', 'msg' => 'Invalid email format']);
                exit;
            }
            
            // Verify OTP if provided
            if (!empty($emailOTP)) {
                $otpManager = new OTPManager($conn);
                $otpVerified = $otpManager->verifyContactOTP($email, $emailOTP);
                
                if (!$otpVerified) {
                    echo json_encode(['status' => 'error', 'msg' => 'Invalid or expired OTP']);
                    exit;
                }
            }

            // Check if contact_messages table exists
            $tableCheck = $conn->query("SHOW TABLES LIKE 'contact_messages'");
            if ($tableCheck->num_rows === 0) {
                echo json_encode(['status' => 'error', 'msg' => 'Contact form is not set up. Please contact admin.']);
                exit;
            }

            // Insert message into database
            $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)");
            if (!$stmt) {
                echo json_encode(['status' => 'error', 'msg' => 'Database error: ' . $conn->error]);
                exit;
            }
            $stmt->bind_param("sssss", $name, $email, $phone, $subject, $message);

            if ($stmt->execute()) {
                // Send email notification to admin (optional, suppress errors)
                $to = "admin@travelplanner.com";
                $subject = "New Contact Form Submission";
                $email_message = "Name: $name\n";
                $email_message .= "Email: $email\n";
                $email_message .= "Phone: $phone\n";
                $email_message .= "Subject: $subject\n\n";
                $email_message .= "Message:\n$message";
                $headers = "From: $email";
                @mail($to, $subject, $email_message, $headers);

                echo json_encode([
                    'status' => 'success',
                    'msg' => 'Thank you for your message! We will get back to you soon.'
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'msg' => 'Failed to send message. Please try again.'
                ]);
            }
        } else {
            echo json_encode(['status' => 'error', 'msg' => 'Invalid action']);
        }
    } else {
        echo json_encode(['status' => 'error', 'msg' => 'Invalid request method']);
    }
} catch (Throwable $e) {
    echo json_encode(['status' => 'error', 'msg' => 'Server error: ' . $e->getMessage()]);
}
?>
