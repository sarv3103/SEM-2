<?php
// php/otp_manager.php - Comprehensive OTP Management System with Real Email & SMS
require_once 'config.php';
require_once 'email_config.php';
require_once 'sms_config.php';

class OTPManager {
    private $conn;
    private $emailService;
    private $smsService;
    
    public function __construct($conn) {
        $this->conn = $conn;
        $this->emailService = new EmailService();
        $this->smsService = new SMSService();
    }
    
    // Generate 6-digit OTP
    public function generateOTP($length = 6) {
        return str_pad(rand(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
    }
    
    // Send email OTP using Gmail SMTP
    public function sendEmailOTP($email, $otp, $purpose = 'verification') {
        try {
            return $this->emailService->sendOTP($email, $otp, $purpose);
        } catch (Exception $e) {
            error_log("Email OTP Error: " . $e->getMessage());
            return false;
        }
    }
    
    // Send SMS OTP using TextLocal API
    public function sendSMSOTP($mobile, $otp, $purpose = 'verification') {
        try {
            return $this->smsService->sendOTP($mobile, $otp, $purpose);
        } catch (Exception $e) {
            error_log("SMS OTP Error: " . $e->getMessage());
            return false;
        }
    }
    
    // Store OTP in database
    public function storeOTP($userId, $email, $mobile, $emailOTP = null, $mobileOTP = null) {
        $emailExpires = $emailOTP ? date('Y-m-d H:i:s', strtotime('+10 minutes')) : null;
        $mobileExpires = $mobileOTP ? date('Y-m-d H:i:s', strtotime('+10 minutes')) : null;
        
        $stmt = $this->conn->prepare("UPDATE users SET email_otp = ?, mobile_otp = ?, email_otp_expires = ?, mobile_otp_expires = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $emailOTP, $mobileOTP, $emailExpires, $mobileExpires, $userId);
        return $stmt->execute();
    }
    
    // Verify OTP
    public function verifyOTP($userId, $emailOTP = null, $mobileOTP = null) {
        $user = $this->getUser($userId);
        if (!$user) return false;
        
        $verified = true;
        
        if ($emailOTP) {
            if ($user['email_otp'] !== $emailOTP || strtotime($user['email_otp_expires']) < time()) {
                $verified = false;
            } else {
                // Mark email as verified
                $stmt = $this->conn->prepare("UPDATE users SET email_verified = 1, email_otp = NULL, email_otp_expires = NULL WHERE id = ?");
                $stmt->bind_param("i", $userId);
                $stmt->execute();
            }
        }
        
        if ($mobileOTP) {
            if ($user['mobile_otp'] !== $mobileOTP || strtotime($user['mobile_otp_expires']) < time()) {
                $verified = false;
            } else {
                // Mark mobile as verified
                $stmt = $this->conn->prepare("UPDATE users SET mobile_verified = 1, mobile_otp = NULL, mobile_otp_expires = NULL WHERE id = ?");
                $stmt->bind_param("i", $userId);
                $stmt->execute();
            }
        }
        
        return $verified;
    }
    
    // Get user data
    private function getUser($userId) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    // Store booking OTP
    public function storeBookingOTP($bookingId, $userId, $emailOTP = null, $mobileOTP = null) {
        $emailExpires = $emailOTP ? date('Y-m-d H:i:s', strtotime('+10 minutes')) : null;
        $mobileExpires = $mobileOTP ? date('Y-m-d H:i:s', strtotime('+10 minutes')) : null;
        
        $stmt = $this->conn->prepare("INSERT INTO booking_otp (booking_id, user_id, email_otp, mobile_otp, email_otp_expires, mobile_otp_expires) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sissss", $bookingId, $userId, $emailOTP, $mobileOTP, $emailExpires, $mobileExpires);
        return $stmt->execute();
    }
    
    // Store payment OTP
    public function storePaymentOTP($bookingId, $userId, $mobileOTP) {
        $mobileExpires = date('Y-m-d H:i:s', strtotime('+10 minutes'));
        
        $stmt = $this->conn->prepare("INSERT INTO payment_otp (booking_id, user_id, mobile_otp, mobile_otp_expires) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("siss", $bookingId, $userId, $mobileOTP, $mobileExpires);
        return $stmt->execute();
    }
    
    // Store contact OTP
    public function storeContactOTP($email, $emailOTP) {
        $emailExpires = date('Y-m-d H:i:s', strtotime('+10 minutes'));
        
        $stmt = $this->conn->prepare("INSERT INTO contact_otp (email, email_otp, email_otp_expires) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $email, $emailOTP, $emailExpires);
        return $stmt->execute();
    }
    
    // Verify booking OTP
    public function verifyBookingOTP($bookingId, $emailOTP = null, $mobileOTP = null) {
        $stmt = $this->conn->prepare("SELECT * FROM booking_otp WHERE booking_id = ? ORDER BY created_at DESC LIMIT 1");
        $stmt->bind_param("s", $bookingId);
        $stmt->execute();
        $result = $stmt->get_result();
        $bookingOTP = $result->fetch_assoc();
        
        if (!$bookingOTP) return false;
        
        $verified = true;
        
        if ($emailOTP) {
            if ($bookingOTP['email_otp'] !== $emailOTP || strtotime($bookingOTP['email_otp_expires']) < time()) {
                $verified = false;
            } else {
                $stmt = $this->conn->prepare("UPDATE booking_otp SET email_verified = 1 WHERE id = ?");
                $stmt->bind_param("i", $bookingOTP['id']);
                $stmt->execute();
            }
        }
        
        if ($mobileOTP) {
            if ($bookingOTP['mobile_otp'] !== $mobileOTP || strtotime($bookingOTP['mobile_otp_expires']) < time()) {
                $verified = false;
            } else {
                $stmt = $this->conn->prepare("UPDATE booking_otp SET mobile_verified = 1 WHERE id = ?");
                $stmt->bind_param("i", $bookingOTP['id']);
                $stmt->execute();
            }
        }
        
        return $verified;
    }
    
    // Verify payment OTP
    public function verifyPaymentOTP($bookingId, $mobileOTP) {
        $stmt = $this->conn->prepare("SELECT * FROM payment_otp WHERE booking_id = ? ORDER BY created_at DESC LIMIT 1");
        $stmt->bind_param("s", $bookingId);
        $stmt->execute();
        $result = $stmt->get_result();
        $paymentOTP = $result->fetch_assoc();
        
        if (!$paymentOTP) return false;
        
        if ($paymentOTP['mobile_otp'] !== $mobileOTP || strtotime($paymentOTP['mobile_otp_expires']) < time()) {
            return false;
        }
        
        $stmt = $this->conn->prepare("UPDATE payment_otp SET verified = 1 WHERE id = ?");
        $stmt->bind_param("i", $paymentOTP['id']);
        return $stmt->execute();
    }
    
    // Verify contact OTP
    public function verifyContactOTP($email, $emailOTP) {
        $stmt = $this->conn->prepare("SELECT * FROM contact_otp WHERE email = ? ORDER BY created_at DESC LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $contactOTP = $result->fetch_assoc();
        
        if (!$contactOTP) return false;
        
        if ($contactOTP['email_otp'] !== $emailOTP || strtotime($contactOTP['email_otp_expires']) < time()) {
            return false;
        }
        
        $stmt = $this->conn->prepare("UPDATE contact_otp SET verified = 1 WHERE id = ?");
        $stmt->bind_param("i", $contactOTP['id']);
        return $stmt->execute();
    }
}
?> 