<?php
// php/login.php - User login
require_once 'config.php';
require_once 'session.php';

header('Content-Type: application/json');

error_reporting(E_ALL);
ini_set('display_errors', 1);

$debug = true; // Set to true to include extra debug info in error responses

function getUserByLogin($conn, $login) {
    $stmt = $conn->prepare("SELECT id, username, password, is_admin FROM users WHERE username = ? OR email = ? LIMIT 1");
    $stmt->bind_param("ss", $login, $login);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    return null;
}

function verifyUserPassword($password, $hashed_password) {
    return password_verify($password, $hashed_password);
}

function loginUser($id, $username) {
    $_SESSION['user_id'] = $id;
    $_SESSION['username'] = $username;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($login) || empty($password)) {
        $msg = 'Please fill in all fields';
        echo json_encode(['status' => 'error', 'msg' => $msg, 'debug' => $debug ? 'Missing username or password' : null]);
        exit;
    }

    try {
        // Check if database connection is working
        if (!$conn || $conn->connect_error) {
            $msg = 'Database connection failed. Please try again.';
            echo json_encode(['status' => 'error', 'msg' => $msg, 'debug' => $debug ? $conn->connect_error : null]);
            exit;
        }

        // Get user from database
        $stmt = $conn->prepare("SELECT id, username, email, password, is_admin FROM users WHERE username = ? OR email = ? LIMIT 1");
        if (!$stmt) {
            $msg = 'Database error: ' . $conn->error;
            echo json_encode(['status' => 'error', 'msg' => $msg, 'debug' => $debug ? $conn->error : null]);
            exit;
        }
        
        $stmt->bind_param("ss", $login, $login);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $msg = 'Invalid username or password';
            echo json_encode(['status' => 'error', 'msg' => $msg, 'debug' => $debug ? 'No user found for: ' . $login : null]);
            exit;
        }

        $user = $result->fetch_assoc();
        
        // Verify password
        if (!password_verify($password, $user['password'])) {
            $msg = 'Invalid username or password';
            echo json_encode(['status' => 'error', 'msg' => $msg, 'debug' => $debug ? 'Password verification failed for user: ' . $login : null]);
            exit;
        }

        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['is_admin'] = $user['is_admin'] ?? 0;
        $_SESSION['last_activity'] = time();

        // Return success response
        $base = getBasePath();
        echo json_encode([
            'status' => 'success',
            'username' => $user['username'],
            'is_admin' => $user['is_admin'] ?? 0,
            'redirect' => $base . (($user['is_admin'] ?? 0) ? 'admin_dashboard.php' : 'index.html')
        ]);
        
    } catch (Exception $e) {
        error_log("Login error: " . $e->getMessage());
        $msg = 'Login failed. Please try again.';
        echo json_encode(['status' => 'error', 'msg' => $msg, 'debug' => $debug ? $e->getMessage() : null]);
    }
} else {
    echo json_encode(['status' => 'error', 'msg' => 'Invalid request method', 'debug' => $debug ? 'Request method: ' . $_SERVER['REQUEST_METHOD'] : null]);
}
?>
