<?php
session_start();
require_once __DIR__ . '/php/config.php';
require_once __DIR__ . '/php/session.php';

// Only allow admin access
requireAdmin();

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}

// Fetch all user wallet balances from users table
$wallets = [];
$walletsResult = $conn->query("SELECT id as user_id, username, email, wallet_balance as balance FROM users WHERE is_admin = 0 ORDER BY wallet_balance DESC, username");
if ($walletsResult) {
    while ($row = $walletsResult->fetch_assoc()) {
        $wallets[] = $row;
    }
}

// Fetch all wallet transactions
$transactions = [];
$txResult = $conn->query("SELECT wt.*, u.username, u.email FROM wallet_transactions wt LEFT JOIN users u ON wt.user_id = u.id ORDER BY wt.created_at DESC");
if ($txResult) {
    while ($row = $txResult->fetch_assoc()) {
        $transactions[] = $row;
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Wallet Management</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { background: #f8f9fa; }
        .container { max-width: 1100px; margin: 40px auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px #0001; }
        h1 { margin-bottom: 30px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        th, td { padding: 10px 12px; border: 1px solid #e0e0e0; }
        th { background: #f1f1f1; }
        .btn { display: inline-block; padding: 8px 18px; background: #007bff; color: #fff; border-radius: 4px; text-decoration: none; margin-bottom: 20px; }
        .btn:hover { background: #0056b3; }
    </style>
</head>
<body>
<div class="container">
    <a href="admin_dashboard.php" class="btn">&larr; Back to Admin Dashboard</a>
    <h1>Wallet Management (Admin Only)</h1>
    <h2>User Wallet Balances</h2>
    <table>
        <thead>
            <tr>
                <th>User</th>
                <th>Email</th>
                <th>Balance</th>
                <th>Last Updated</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($wallets as $w): ?>
            <tr>
                <td><?= htmlspecialchars($w['username']) ?></td>
                <td><?= htmlspecialchars($w['email']) ?></td>
                <td>₹<?= number_format($w['balance'], 2) ?></td>
                <td>-</td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <h2>Wallet Transactions</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>User</th>
                <th>Email</th>
                <th>Type</th>
                <th>Amount</th>
                <th>Description</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($transactions as $tx): ?>
            <tr>
                <td><?= $tx['id'] ?></td>
                <td><?= htmlspecialchars($tx['username']) ?></td>
                <td><?= htmlspecialchars($tx['email']) ?></td>
                <td><?= htmlspecialchars($tx['type']) ?></td>
                <td>₹<?= number_format($tx['amount'], 2) ?></td>
                <td><?= htmlspecialchars($tx['description']) ?></td>
                <td><?= htmlspecialchars($tx['created_at']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html> 