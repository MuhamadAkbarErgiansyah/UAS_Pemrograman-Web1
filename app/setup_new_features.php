<?php
require 'config.php';

// Create all necessary tables for new features

try {
    // 1. Chat Messages Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        sender_id INT NOT NULL,
        receiver_id INT NOT NULL,
        message TEXT NOT NULL,
        is_read TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_sender (sender_id),
        INDEX idx_receiver (receiver_id)
    )");
    echo "✅ Table 'messages' created\n";

    // 2. Vouchers Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS vouchers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(50) UNIQUE NOT NULL,
        discount_type ENUM('percentage', 'fixed') DEFAULT 'percentage',
        discount_value DECIMAL(10,2) NOT NULL,
        min_order DECIMAL(10,2) DEFAULT 0,
        max_uses INT DEFAULT NULL,
        used_count INT DEFAULT 0,
        valid_from DATE,
        valid_until DATE,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "✅ Table 'vouchers' created\n";

    // 3. Notifications Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        type VARCHAR(50) NOT NULL,
        title VARCHAR(255) NOT NULL,
        message TEXT,
        link VARCHAR(255),
        is_read TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_user (user_id)
    )");
    echo "✅ Table 'notifications' created\n";

    // 4. Review Replies Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS review_replies (
        id INT AUTO_INCREMENT PRIMARY KEY,
        review_id INT NOT NULL,
        admin_id INT NOT NULL,
        reply TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_reply (review_id)
    )");
    echo "✅ Table 'review_replies' created\n";

    // Insert sample vouchers
    $pdo->exec("INSERT IGNORE INTO vouchers (code, discount_type, discount_value, min_order, max_uses, valid_from, valid_until) VALUES 
        ('WELCOME10', 'percentage', 10, 100000, 100, '2026-01-01', '2026-12-31'),
        ('HEMAT50K', 'fixed', 50000, 200000, 50, '2026-01-01', '2026-06-30'),
        ('BANDUNG20', 'percentage', 20, 150000, NULL, '2026-01-01', '2026-03-31')
    ");
    echo "✅ Sample vouchers inserted\n";

    echo "\n🎉 All tables created successfully!\n";

} catch(Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
