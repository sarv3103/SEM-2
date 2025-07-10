<?php
// php/logout.php - Destroy session and logout user
session_start();
$_SESSION = array();
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']
    );
}
session_destroy();
// If not an AJAX request, redirect to home page after logout
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    header('Location: ../index.html');
    exit;
}
header('Content-Type: application/json');
echo json_encode(['status' => 'success', 'msg' => 'Logged out successfully']);
