<?php
// php/session.php - Session management
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
    session_start();
}

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Function to check if user is admin
function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
}

// Function to get current user ID
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

// Function to get current username
function getCurrentUsername() {
    return $_SESSION['username'] ?? null;
}

// Function to logout user
function logout() {
    session_unset();
    session_destroy();
    session_start();
}

// Function to force redirect to login page
function getBasePath() {
    $scriptName = $_SERVER['SCRIPT_NAME'];
    $dir = rtrim(dirname($scriptName), '/\\');
    // If we're in a /php subfolder, go up one level
    if (basename($dir) === 'php') {
        $dir = dirname($dir);
    }
    return $dir === '' ? '/' : $dir . '/';
}

function forceLoginRedirect() {
    session_unset();
    session_destroy();
    if (isAjaxRequest()) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
        exit;
    }
    $base = getBasePath();
    header('Location: ' . $base . 'index.html#auth');
    exit;
}

// Function to require login
function requireLogin() {
    if (!isLoggedIn()) {
        forceLoginRedirect();
    }
}

// Function to require admin
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        forceLoginRedirect();
    }
}

// Set session timeout to 30 minutes
$timeout = 1800; // 30 minutes in seconds
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
    logout();
    forceLoginRedirect();
}
$_SESSION['last_activity'] = time();

function getCurrentUser() {
    if (isLoggedIn()) {
        return [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'is_admin' => $_SESSION['is_admin']
        ];
    }
    return null;
}

// Check if this is an AJAX request
function isAjaxRequest() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

// Check session status and return JSON response for AJAX requests
function checkSessionStatus() {
    if (isAjaxRequest()) {
        header('Content-Type: application/json');
        echo json_encode([
            'logged_in' => isLoggedIn(),
            'is_admin' => isAdmin(),
            'user' => getCurrentUser()
        ]);
        exit;
    }
} 