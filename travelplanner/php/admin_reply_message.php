<?php
require_once 'config.php';
require_once 'smtp_config.php';
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/SMTP.php';
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/Exception.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

// Check if SMTP is properly configured
if (!isSmtpConfigured()) {
    echo json_encode([
        'success' => false, 
        'error' => getSmtpConfigError()
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success'=>false, 'error'=>'Invalid request method']);
    exit;
}

$id = intval($_POST['message_id'] ?? 0);
$to = $_POST['to_email'] ?? '';
$subject = $_POST['subject'] ?? '';
$body = $_POST['body'] ?? '';

if (!$id || !$to || !$subject || !$body) {
    echo json_encode(['success'=>false, 'error'=>'Missing required fields']);
    exit;
}

// Get SMTP configuration
$smtpConfig = getSmtpConfig();

// Professional HTML email body
$htmlBody = "<html><body style='font-family: Arial, sans-serif; color: #222;'>"
    . "<div style='background:" . EMAIL_BG_COLOR . ";padding:20px;border-radius:8px;'>"
    . "<h2 style='color:" . EMAIL_HEADER_COLOR . ";'>TravelPlanner Admin Reply</h2>"
    . "<p>Dear Customer,</p>"
    . "<div style='margin:20px 0;padding:15px;background:#fff;border-radius:6px;border:1px solid " . EMAIL_BORDER_COLOR . ";'>"
    . nl2br(htmlspecialchars($body)) . "</div>"
    . "<p>Best regards,<br><strong>TravelPlanner Team</strong><br><a href='mailto:" . SMTP_FROM_EMAIL . "'>" . SMTP_FROM_EMAIL . "</a></p>"
    . "<hr style='margin:30px 0 10px 0;'>"
    . "<small style='color:#888;'>This is an automated message from TravelPlanner Admin Dashboard.</small>"
    . "</div></body></html>";

$mail = new PHPMailer(true);
try {
    // Enable debug output for troubleshooting (set to 0 for production)
    $mail->SMTPDebug = 0;
    $mail->Debugoutput = 'error_log';
    
    $mail->isSMTP();
    $mail->Host = $smtpConfig['host'];
    $mail->SMTPAuth = true;
    $mail->Username = $smtpConfig['username'];
    $mail->Password = $smtpConfig['password'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = $smtpConfig['port'];
    
    // Additional SMTP settings for better compatibility
    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );
    
    $mail->setFrom($smtpConfig['from_email'], $smtpConfig['from_name']);
    $mail->addAddress($to);
    $mail->addReplyTo($smtpConfig['from_email'], $smtpConfig['from_name']);
    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body = $htmlBody;
    $mail->AltBody = strip_tags($body);
    
    $mail->send();
    
    // Mark as replied
    $stmt = $conn->prepare("UPDATE contact_messages SET status='Replied' WHERE id=?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    
    echo json_encode(['success'=>true]);
    
} catch (Exception $e) {
    error_log('PHPMailer error: ' . $mail->ErrorInfo . ' | Exception: ' . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'error' => 'Failed to send email: ' . $mail->ErrorInfo . '. Please check your SMTP configuration.'
    ]);
} 