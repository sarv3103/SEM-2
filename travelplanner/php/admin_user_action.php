<?php
// php/admin_user_action.php - Admin user management actions (edit username/email, reset password)
session_start();
require_once __DIR__ . '/config.php';
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username']) || $_SESSION['username'] !== 'admin') {
    die('Access denied.');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'], $_POST['action'])) {
    $user_id = intval($_POST['user_id']);
    if ($_POST['action'] === 'edit') {
        // Show edit form
        $res = $conn->query("SELECT id, username, email FROM users WHERE id = $user_id");
        $user = $res->fetch_assoc();
        echo '<form method="post" style="margin:1em 0;">'
            .'<input type="hidden" name="user_id" value="'.htmlspecialchars($user['id']).'">'
            .'Username: <input name="username" value="'.htmlspecialchars($user['username']).'" required> '
            .'Email: <input name="email" value="'.htmlspecialchars($user['email']).'" type="email" required> '
            .'<button type="submit" name="action" value="save_edit" style="background:#28a745;color:#fff;padding:0.4em 1em;border:none;border-radius:4px;">Save</button>'
            .'<a href="../admin_dashboard.php" style="margin-left:1em;">Cancel</a>'
            .'</form>';
        exit();
    }
    if ($_POST['action'] === 'save_edit' && isset($_POST['username'], $_POST['email'])) {
        $username = $conn->real_escape_string($_POST['username']);
        $email = $conn->real_escape_string($_POST['email']);
        $conn->query("UPDATE users SET username='$username', email='$email' WHERE id=$user_id");
        echo '<div style="color:green;font-weight:600;">User updated.</div> <a href="../admin_dashboard.php">Back to dashboard</a>';
        exit();
    }
    if ($_POST['action'] === 'resetpw') {
        // Show reset password form
        echo '<form method="post" style="margin:1em 0;">'
            .'<input type="hidden" name="user_id" value="'.htmlspecialchars($user_id).'">'
            .'New Password: <input name="new_password" type="password" required> '
            .'<button type="submit" name="action" value="do_resetpw" style="background:#e67e22;color:#fff;padding:0.4em 1em;border:none;border-radius:4px;">Reset</button>'
            .'<a href="../admin_dashboard.php" style="margin-left:1em;">Cancel</a>'
            .'</form>';
        exit();
    }
    if ($_POST['action'] === 'do_resetpw' && isset($_POST['new_password'])) {
        $hash = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        $conn->query("UPDATE users SET password='$hash' WHERE id=$user_id");
        echo '<div style="color:green;font-weight:600;">Password reset.</div> <a href="../admin_dashboard.php">Back to dashboard</a>';
        exit();
    }
    // If a temp password was granted, force user to change it on next login
    if ($_POST['action'] === 'grant_reset' && isset($_POST['reset_file'], $_POST['username'], $_POST['email'])) {
        $resetDir = __DIR__ . '/../reset_requests/';
        $resetFile = $resetDir . basename($_POST['reset_file']);
        $username = $conn->real_escape_string($_POST['username']);
        $email = $conn->real_escape_string($_POST['email']);
        $tempPassword = substr(bin2hex(random_bytes(4)),0,8);
        $hash = password_hash($tempPassword, PASSWORD_DEFAULT);
        $conn->query("UPDATE users SET password='$hash', must_change_pw=1 WHERE username='$username' AND email='$email'");
        if (file_exists($resetFile)) unlink($resetFile);
        echo '<div style="color:green;font-weight:600;">Temporary password for <b>'.htmlspecialchars($username).'</b>: <span style="font-family:monospace;">'.htmlspecialchars($tempPassword).'</span><br>Share this with the user. They must log in and change their password.</div> <a href="../admin_dashboard.php">Back to dashboard</a>';
        exit();
    }
}
echo 'Invalid request.';
