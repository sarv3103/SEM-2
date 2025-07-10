<?php
// php/email_config.php - Email configuration using PHPMailer with Gmail SMTP
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class EmailService {
    protected $mailer;
    
    public function __construct() {
        $this->mailer = new PHPMailer(true);
        
        // Gmail SMTP Configuration (FREE)
        $this->mailer->isSMTP();
        $this->mailer->Host = 'smtp.gmail.com';
        $this->mailer->SMTPAuth = true;
        
        // ⚠️ REPLACE THESE WITH YOUR ACTUAL GMAIL CREDENTIALS ⚠️
        // Step 1: Go to https://myaccount.google.com/
        // Step 2: Enable 2-Step Verification
        // Step 3: Create App Password for "TravelPlanner"
        // Step 4: Replace the values below:
        $this->mailer->Username = 'sarveshtravelplanner@gmail.com'; // Your Gmail address
        $this->mailer->Password = 'pinm lcxd vbhe dwbl'; // ⚠️ REPLACE: Copy the 16-character app password from Google (remove spaces)
        
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port = 587;
        
        // Set sender (use same email as Username)
        $this->mailer->setFrom('sarveshtravelplanner@gmail.com', 'TravelPlanner');
        $this->mailer->addReplyTo('sarvesh.travelplanner@gmail.com', 'TravelPlanner Support');
    }
    
    public function sendOTP($to, $otp, $purpose = 'verification') {
        try {
            $this->mailer->addAddress($to);
            $this->mailer->isHTML(true);
            $this->mailer->Subject = "TravelPlanner OTP Verification";
            $this->mailer->Body = $this->getEmailTemplate($otp, $purpose);
            
            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            error_log("Email Error: " . $e->getMessage());
            return false;
        }
    }
    
    private function getEmailTemplate($otp, $purpose) {
        $purposeText = ucfirst($purpose);
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #0077cc; color: white; padding: 20px; text-align: center; }
                .content { background: #f9f9f9; padding: 20px; }
                .otp-box { background: #0077cc; color: white; padding: 15px; text-align: center; font-size: 24px; font-weight: bold; margin: 20px 0; }
                .footer { background: #333; color: white; padding: 15px; text-align: center; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>TravelPlanner</h1>
                    <h2>OTP Verification</h2>
                </div>
                <div class='content'>
                    <p>Hello!</p>
                    <p>Your OTP for $purposeText is:</p>
                    <div class='otp-box'>$otp</div>
                    <p>This OTP is valid for 30 minutes.</p>
                    <p>If you didn't request this OTP, please ignore this email.</p>
                </div>
                <div class='footer'>
                    <p>&copy; 2024 TravelPlanner. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>";
    }
}
?> 