<?php
// php/admin_otp_logs.php - Admin panel to view OTP logs
require_once 'config.php';
require_once 'session.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: ../login.html');
    exit;
}

// Get OTP logs
$smsLogs = [];
$bookingOTPs = [];
$paymentOTPs = [];
$contactOTPs = [];

// Get SMS logs
$smsResult = $conn->query("SELECT * FROM sms_log ORDER BY created_at DESC LIMIT 50");
if ($smsResult) {
    while ($row = $smsResult->fetch_assoc()) {
        $smsLogs[] = $row;
    }
}

// Get booking OTPs
$bookingResult = $conn->query("SELECT bo.*, u.username, u.email FROM booking_otp bo LEFT JOIN users u ON bo.user_id = u.id ORDER BY bo.created_at DESC LIMIT 50");
if ($bookingResult) {
    while ($row = $bookingResult->fetch_assoc()) {
        $bookingOTPs[] = $row;
    }
}

// Get payment OTPs
$paymentResult = $conn->query("SELECT po.*, u.username FROM payment_otp po LEFT JOIN users u ON po.user_id = u.id ORDER BY po.created_at DESC LIMIT 50");
if ($paymentResult) {
    while ($row = $paymentResult->fetch_assoc()) {
        $paymentOTPs[] = $row;
    }
}

// Get contact OTPs
$contactResult = $conn->query("SELECT * FROM contact_otp ORDER BY created_at DESC LIMIT 50");
if ($contactResult) {
    while ($row = $contactResult->fetch_assoc()) {
        $contactOTPs[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Logs - Admin Panel</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        .otp-logs {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
        }
        .log-section {
            margin-bottom: 30px;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .log-section h2 {
            color: #0077cc;
            margin-bottom: 15px;
        }
        .log-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .log-table th, .log-table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .log-table th {
            background: #f5f5f5;
            font-weight: bold;
        }
        .otp-code {
            font-family: monospace;
            background: #f0f0f0;
            padding: 2px 6px;
            border-radius: 3px;
        }
        .status-verified {
            color: green;
            font-weight: bold;
        }
        .status-pending {
            color: orange;
        }
        .back-btn {
            background: #0077cc;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="otp-logs">
        <a href="admin_dashboard.php" class="back-btn">← Back to Admin Dashboard</a>
        
        <h1>OTP Verification Logs</h1>
        
        <!-- SMS Logs -->
        <div class="log-section">
            <h2>SMS OTP Logs</h2>
            <table class="log-table">
                <thead>
                    <tr>
                        <th>Mobile</th>
                        <th>OTP</th>
                        <th>Message</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($smsLogs as $log): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($log['mobile']); ?></td>
                        <td><span class="otp-code"><?php echo htmlspecialchars($log['otp']); ?></span></td>
                        <td><?php echo htmlspecialchars($log['message']); ?></td>
                        <td><?php echo $log['created_at']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Booking OTPs -->
        <div class="log-section">
            <h2>Booking OTP Verification</h2>
            <table class="log-table">
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>User</th>
                        <th>Email OTP</th>
                        <th>Mobile OTP</th>
                        <th>Email Verified</th>
                        <th>Mobile Verified</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookingOTPs as $otp): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($otp['booking_id']); ?></td>
                        <td><?php echo htmlspecialchars($otp['username'] ?? 'N/A'); ?></td>
                        <td><span class="otp-code"><?php echo htmlspecialchars($otp['email_otp'] ?? 'N/A'); ?></span></td>
                        <td><span class="otp-code"><?php echo htmlspecialchars($otp['mobile_otp'] ?? 'N/A'); ?></span></td>
                        <td class="<?php echo $otp['email_verified'] ? 'status-verified' : 'status-pending'; ?>">
                            <?php echo $otp['email_verified'] ? 'Yes' : 'No'; ?>
                        </td>
                        <td class="<?php echo $otp['mobile_verified'] ? 'status-verified' : 'status-pending'; ?>">
                            <?php echo $otp['mobile_verified'] ? 'Yes' : 'No'; ?>
                        </td>
                        <td><?php echo $otp['created_at']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Payment OTPs -->
        <div class="log-section">
            <h2>Payment OTP Verification</h2>
            <table class="log-table">
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>User</th>
                        <th>Mobile OTP</th>
                        <th>Verified</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($paymentOTPs as $otp): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($otp['booking_id']); ?></td>
                        <td><?php echo htmlspecialchars($otp['username'] ?? 'N/A'); ?></td>
                        <td><span class="otp-code"><?php echo htmlspecialchars($otp['mobile_otp']); ?></span></td>
                        <td class="<?php echo $otp['verified'] ? 'status-verified' : 'status-pending'; ?>">
                            <?php echo $otp['verified'] ? 'Yes' : 'No'; ?>
                        </td>
                        <td><?php echo $otp['created_at']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Contact OTPs -->
        <div class="log-section">
            <h2>Contact Form OTP Verification</h2>
            <table class="log-table">
                <thead>
                    <tr>
                        <th>Email</th>
                        <th>OTP</th>
                        <th>Verified</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($contactOTPs as $otp): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($otp['email']); ?></td>
                        <td><span class="otp-code"><?php echo htmlspecialchars($otp['email_otp']); ?></span></td>
                        <td class="<?php echo $otp['verified'] ? 'status-verified' : 'status-pending'; ?>">
                            <?php echo $otp['verified'] ? 'Yes' : 'No'; ?>
                        </td>
                        <td><?php echo $otp['created_at']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html> 