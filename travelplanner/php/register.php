<?php
// php/register.php - User registration with Email OTP verification only
require_once 'config.php';
require_once 'session.php';
require_once 'otp_manager.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validate all fields
    if (empty($first_name) || !preg_match('/^[A-Za-z]{2,}$/', $first_name)) {
        echo json_encode(['status' => 'error', 'msg' => 'First name should be at least 2 letters and only alphabets.']);
        exit;
    }
    if (empty($last_name) || !preg_match('/^[A-Za-z]{2,}$/', $last_name)) {
        echo json_encode(['status' => 'error', 'msg' => 'Last name should be at least 2 letters and only alphabets.']);
        exit;
    }
    if (empty($username) || strlen($username) < 4) {
        echo json_encode(['status' => 'error', 'msg' => 'Username should be at least 4 characters.']);
        exit;
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['status' => 'error', 'msg' => 'Invalid email format.']);
        exit;
    }
    if (empty($password) || strlen($password) < 6) {
        echo json_encode(['status' => 'error', 'msg' => 'Password should be at least 6 characters.']);
        exit;
    }

    // Check if username already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        echo json_encode(['status' => 'error', 'msg' => 'Username already exists']);
        exit;
    }

    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        echo json_encode(['status' => 'error', 'msg' => 'Email already registered']);
        exit;
    }

    // Initialize OTP Manager
    $otpManager = new OTPManager($conn);
    
    // Generate Email OTP
    $emailOTP = $otpManager->generateOTP();
    
    // Send Email OTP
    $emailSent = $otpManager->sendEmailOTP($email, $emailOTP, 'registration');
    
    if ($emailSent) {
        // Create a temporary registration record
        $temp_id = uniqid('reg_', true);
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert temporary user record
        $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, username, email, password, email_verified, status) VALUES (?, ?, ?, ?, ?, 0, 'pending')");
        $stmt->bind_param("sssss", $first_name, $last_name, $username, $email, $hashed_password);
        
        if ($stmt->execute()) {
            $userId = $conn->insert_id;
            
            // Store OTP in database
            $otpManager->storeOTP($userId, $email, null, $emailOTP, null);
            
            // Store registration data in session for OTP verification
            $_SESSION['pending_registration'] = [
                'user_id' => $userId,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'username' => $username,
                'email' => $email,
                'password' => $password,
                'email_otp' => $emailOTP
            ];
            
            echo json_encode([
                'status' => 'success',
                'msg' => 'Registration data validated! Please check your email for the OTP to complete registration.',
                'user_id' => $userId,
                'email_sent' => true
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'msg' => 'Failed to create temporary account. Please try again.'
            ]);
        }
    } else {
        echo json_encode([
            'status' => 'error',
            'msg' => 'Email OTP could not be sent. Please try again.'
        ]);
    }
} else {
    echo json_encode(['status' => 'error', 'msg' => 'Invalid request method']);
}
?>
