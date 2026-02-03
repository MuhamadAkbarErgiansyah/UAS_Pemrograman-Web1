<?php
require 'config.php';

echo "=== Checking Database Tables ===\n\n";

$result = $pdo->query("SHOW TABLES");
$tables = $result->fetchAll(PDO::FETCH_COLUMN);
echo "Existing tables: " . implode(", ", $tables) . "\n\n";

// Create review_replies if not exists
$pdo->exec("CREATE TABLE IF NOT EXISTS review_replies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    review_id INT NOT NULL,
    admin_id INT NOT NULL,
    reply_text TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)");
echo "review_replies - OK\n";

// Create messages if not exists
$pdo->exec("CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");
echo "messages - OK\n";

// Create notifications if not exists
$pdo->exec("CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type VARCHAR(50) DEFAULT 'info',
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");
echo "notifications - OK\n";

// Create vouchers if not exists
$pdo->exec("CREATE TABLE IF NOT EXISTS vouchers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    discount_type ENUM('percent', 'fixed') DEFAULT 'percent',
    discount_value DECIMAL(10,2) NOT NULL,
    min_order DECIMAL(10,2) DEFAULT 0,
    max_uses INT DEFAULT NULL,
    used_count INT DEFAULT 0,
    valid_from DATE DEFAULT NULL,
    valid_until DATE DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");
echo "vouchers - OK\n";

// Insert sample vouchers
$pdo->exec("INSERT IGNORE INTO vouchers (code, discount_type, discount_value, min_order) VALUES 
    ('WELCOME10', 'percent', 10, 100000),
    ('HEMAT50K', 'fixed', 50000, 200000),
    ('BANDUNG20', 'percent', 20, 150000)");
echo "Sample vouchers - OK\n";

echo "\nDone! All tables ready.\n";
