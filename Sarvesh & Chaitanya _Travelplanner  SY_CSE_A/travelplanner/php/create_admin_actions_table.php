<?php
require_once 'config.php';

try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }

    // Create admin_actions table
    $createTableSQL = "
    CREATE TABLE IF NOT EXISTS admin_actions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        admin_id INT NOT NULL,
        action_type VARCHAR(50) NOT NULL,
        target_user_id INT,
        amount DECIMAL(10,2),
        reason VARCHAR(100),
        remarks TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_admin_id (admin_id),
        INDEX idx_action_type (action_type),
        INDEX idx_target_user (target_user_id),
        INDEX idx_created_at (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";

    if ($conn->query($createTableSQL) === TRUE) {
        echo "Admin actions table created successfully or already exists.\n";
    } else {
        echo "Error creating admin actions table: " . $conn->error . "\n";
    }

    // Create wallet table if it doesn't exist
    $createWalletSQL = "
    CREATE TABLE IF NOT EXISTS wallet (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL UNIQUE,
        balance DECIMAL(10,2) DEFAULT 0.00,
        last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_user_id (user_id),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";

    if ($conn->query($createWalletSQL) === TRUE) {
        echo "Wallet table created successfully or already exists.\n";
    } else {
        echo "Error creating wallet table: " . $conn->error . "\n";
    }

    // Add some sample admin actions for testing
    $sampleActions = [
        ['admin_id' => 1, 'action_type' => 'wallet_credit', 'target_user_id' => 2, 'amount' => 1000.00, 'reason' => 'admin_credit', 'remarks' => 'Initial credit'],
        ['admin_id' => 1, 'action_type' => 'manual_payment_verification', 'target_user_id' => 2, 'amount' => 500.00, 'reason' => 'manual_verification', 'remarks' => 'Payment verified manually'],
        ['admin_id' => 1, 'action_type' => 'payment_refund', 'target_user_id' => 2, 'amount' => 250.00, 'reason' => 'admin_refund', 'remarks' => 'Customer requested refund']
    ];

    foreach ($sampleActions as $action) {
        $insertStmt = $conn->prepare("
            INSERT IGNORE INTO admin_actions (admin_id, action_type, target_user_id, amount, reason, remarks, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        $insertStmt->bind_param("isidss", 
            $action['admin_id'], 
            $action['action_type'], 
            $action['target_user_id'], 
            $action['amount'], 
            $action['reason'], 
            $action['remarks']
        );
        $insertStmt->execute();
    }

    echo "Sample admin actions added successfully.\n";
    echo "Database setup completed!\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

if (isset($conn)) {
    $conn->close();
}
?> 