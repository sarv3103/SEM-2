<?php
/**
 * SMTP Configuration File
 * Centralized email settings for the TravelPlanner application
 */

// Gmail SMTP Configuration
// IMPORTANT: Replace 'YOUR_APP_PASSWORD' with your actual Gmail App Password
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USERNAME', 'sarveshtravelplanner@gmail.com');
define('SMTP_PASSWORD', 'afwweunzgitgfjmh'); // <-- Set your Gmail App Password here
define('SMTP_FROM_EMAIL', 'sarveshtravelplanner@gmail.com');
define('SMTP_FROM_NAME', 'TravelPlanner');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');

// Email Templates
define('EMAIL_HEADER_COLOR', '#0077cc');
define('EMAIL_BG_COLOR', '#f8f9fa');
define('EMAIL_BORDER_COLOR', '#e0eafc');

/**
 * Get SMTP configuration array
 * @return array SMTP settings
 */
function getSmtpConfig() {
    return [
        'host' => SMTP_HOST,
        'username' => SMTP_USERNAME,
        'password' => SMTP_PASSWORD,
        'from_email' => SMTP_FROM_EMAIL,
        'from_name' => SMTP_FROM_NAME,
        'port' => SMTP_PORT,
        'secure' => SMTP_SECURE
    ];
}

/**
 * Check if SMTP is properly configured
 * @return bool True if configured, false otherwise
 */
function isSmtpConfigured() {
    return SMTP_PASSWORD !== 'YOUR_APP_PASSWORD';
}

/**
 * Get configuration error message
 * @return string Error message if not configured
 */
function getSmtpConfigError() {
    if (!isSmtpConfigured()) {
        return 'SMTP credentials not configured. Please update SMTP_PASSWORD in php/smtp_config.php with your Gmail App Password.';
    }
    return '';
}
?> 