<?php
require 'config.php';

echo "=== Testing Analytics Queries ===\n\n";

// Test monthly bookings query
try {
    $monthlyBookings = $pdo->query("
        SELECT 
            DATE_FORMAT(r.created_at, '%Y-%m') as month,
            COUNT(*) as total_bookings,
            COALESCE(SUM(i.price), 0) as revenue
        FROM reservations r
        LEFT JOIN items i ON r.package_id = i.id
        WHERE r.created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
        GROUP BY DATE_FORMAT(r.created_at, '%Y-%m')
        ORDER BY month ASC
    ")->fetchAll();
    echo "Monthly Bookings: OK (" . count($monthlyBookings) . " records)\n";
} catch(Exception $e) {
    echo "Monthly Bookings ERROR: " . $e->getMessage() . "\n";
}

// Test top packages query
try {
    $topPackages = $pdo->query("
        SELECT i.title as name, COUNT(r.id) as bookings, COALESCE(SUM(i.price), 0) as revenue
        FROM items i
        LEFT JOIN reservations r ON i.id = r.package_id
        GROUP BY i.id
        ORDER BY bookings DESC
        LIMIT 5
    ")->fetchAll();
    echo "Top Packages: OK (" . count($topPackages) . " records)\n";
} catch(Exception $e) {
    echo "Top Packages ERROR: " . $e->getMessage() . "\n";
}

// Test revenue query
try {
    $totalRevenue = $pdo->query("
        SELECT COALESCE(SUM(i.price), 0) 
        FROM reservations r 
        LEFT JOIN items i ON r.package_id = i.id
        LEFT JOIN booking_approvals ba ON r.id = ba.reservation_id
        WHERE ba.status = 'approved'
    ")->fetchColumn();
    echo "Total Revenue: OK (Rp " . number_format($totalRevenue ?: 0, 0, ',', '.') . ")\n";
} catch(Exception $e) {
    echo "Total Revenue ERROR: " . $e->getMessage() . "\n";
}

echo "\nAll analytics queries tested!\n";
