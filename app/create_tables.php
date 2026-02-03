<?php
require 'config.php';

try {
    // CREATE REVIEWS TABLE
    $pdo->exec('CREATE TABLE IF NOT EXISTS reviews (
        id INT PRIMARY KEY AUTO_INCREMENT,
        item_id INT NOT NULL,
        user_id INT,
        rating INT,
        comment TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (item_id) REFERENCES items(id),
        FOREIGN KEY (user_id) REFERENCES users(id)
    )');

    // CREATE WISHLIST TABLE
    $pdo->exec('CREATE TABLE IF NOT EXISTS wishlists (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        item_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_wishlist (user_id, item_id),
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (item_id) REFERENCES items(id)
    )');

    // CREATE BOOKING_APPROVALS TABLE
    $pdo->exec('CREATE TABLE IF NOT EXISTS booking_approvals (
        id INT PRIMARY KEY AUTO_INCREMENT,
        reservation_id INT NOT NULL,
        status ENUM("pending", "approved", "rejected", "completed") DEFAULT "pending",
        approval_date TIMESTAMP NULL,
        approved_by INT,
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (reservation_id) REFERENCES reservations(id),
        FOREIGN KEY (approved_by) REFERENCES users(id)
    )');

    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 1rem; border-radius: 0.5rem; margin: 1rem;'>
            <h4 style='color: #155724;'>✅ Database Tables Berhasil Dibuat!</h4>
            <p style='color: #155724; margin-bottom: 0;'>
                <strong>Tables:</strong> reviews, wishlists, booking_approvals
            </p>
          </div>";

} catch (Exception $e) {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 1rem; border-radius: 0.5rem; margin: 1rem;'>
            <h4 style='color: #721c24;'>❌ Error:</h4>
            <p style='color: #721c24; margin-bottom: 0;'>" . $e->getMessage() . "</p>
          </div>";
}
?>
