<?php
// php/sms_config.php - SMS configuration using MSG91 API (FREE - 500+ SMS credits)
class SMSService {
    private $apiKey;
    private $sender;
    private $service; // 'msg91' or 'textlocal'
    
    public function __construct() {
        // MSG91 Configuration (500+ free SMS credits) - PRIMARY SERVICE
        // ✅ AUTH KEY PROVIDED: 457111ARwKRzZTS26856eb4aP1
        $this->apiKey = '457111ARwKRzZTS26856eb4aP1'; // ✅ Your MSG91 Auth Token
        $this->sender = 'TRAVEL'; // ✅ 6 characters - matches DLT template
        $this->service = 'msg91'; // Use MSG91 as primary service (500+ free SMS)
        
        // Alternative: TextLocal Configuration (100 free SMS credits) - BACKUP
        // $this->apiKey = 'your-textlocal-api-key';
        // $this->sender = 'TRVLPL';
        // $this->service = 'textlocal';
    }
    
    public function sendOTP($mobile, $otp, $purpose = 'verification') {
        if ($this->service === 'msg91') {
            return $this->sendViaMSG91($mobile, $otp, $purpose);
        } else {
            return $this->sendViaTextLocal($mobile, $otp, $purpose);
        }
    }
    
    // MSG91 SMS Service (500+ free credits)
    private function sendViaMSG91($mobile, $otp, $purpose) {
        // Remove any non-numeric characters from mobile
        $mobile = preg_replace('/[^0-9]/', '', $mobile);
        
        // Add country code if not present (assuming India +91)
        if (strlen($mobile) === 10) {
            $mobile = '91' . $mobile;
        }
        
        $message = "Your TravelPlanner OTP is: ##OTP##. Valid for 10 minutes.";
        
        // MSG91 API URL for OTP
        $url = 'https://api.msg91.com/api/v5/flow/';
        
        // Prepare data
        $data = array(
            'flow_id' => '6856e627d6fc056126673292', // ✅ Your actual Template ID from MSG91
            'sender' => $this->sender, // ✅ Will use 'TRVLPL' (6 characters max)
            'mobiles' => $mobile,
            'VAR1' => $otp // This will replace ##OTP## in the template
        );
        
        // Send SMS using cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Authkey: ' . $this->apiKey
        ));
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        // Parse response
        $result = json_decode($response, true);
        
        if ($httpCode === 200 && isset($result['type']) && $result['type'] === 'success') {
            $this->logSMS($mobile, $message, $otp, true, $result);
            return true;
        } else {
            $this->logSMS($mobile, $message, $otp, false, $result);
            error_log("MSG91 SMS Error: " . json_encode($result));
            return false;
        }
    }
    
    // TextLocal SMS Service (100 free credits)
    private function sendViaTextLocal($mobile, $otp, $purpose) {
        // Remove any non-numeric characters from mobile
        $mobile = preg_replace('/[^0-9]/', '', $mobile);
        
        // Add country code if not present (assuming India +91)
        if (strlen($mobile) === 10) {
            $mobile = '91' . $mobile;
        }
        
        $message = "Your TravelPlanner OTP is: {{#var#}}. Valid for 10 minutes.";
        
        // TextLocal API URL
        $url = 'https://api.textlocal.in/send/';
        
        // Prepare data
        $data = array(
            'apikey' => $this->apiKey,
            'numbers' => $mobile,
            'sender' => $this->sender,
            'message' => $message
        );
        
        // Send SMS using cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        // Parse response
        $result = json_decode($response, true);
        
        if ($httpCode === 200 && isset($result['status']) && $result['status'] === 'success') {
            $this->logSMS($mobile, $message, $otp, true, $result);
            return true;
        } else {
            $this->logSMS($mobile, $message, $otp, false, $result);
            error_log("TextLocal SMS Error: " . json_encode($result));
            return false;
        }
    }
    
    private function logSMS($mobile, $message, $otp, $success, $apiResponse) {
        global $conn;
        
        // Create SMS log table if it doesn't exist
        $conn->query("CREATE TABLE IF NOT EXISTS sms_log (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            mobile VARCHAR(15) NOT NULL,
            message TEXT NOT NULL,
            otp VARCHAR(6) NOT NULL,
            success TINYINT(1) DEFAULT 0,
            api_response TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        $stmt = $conn->prepare("INSERT INTO sms_log (mobile, message, otp, success, api_response, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $apiResponseJson = json_encode($apiResponse);
        $stmt->bind_param("sssis", $mobile, $message, $otp, $success, $apiResponseJson);
        $stmt->execute();
    }
}
?> 