<?php
/**
 * Include Verification Script - Check all page include order
 * This ensures proper session initialization across all pages
 */

require 'config.php';

$pages = [
    'index.php',
    'paket.php',
    'guide.php',
    'galerry.php',
    'detail.php',
    'pemesanan.php',
    'riwayat.php',
    'dashboard.php',
    'wishlist.php',
    'login.php',
    'logout.php'
];

echo "<h2>Include Order Verification</h2>";
echo "<table border='1' style='width:100%; border-collapse:collapse;'>";
echo "<tr><th>Page</th><th>Session Status</th><th>$_SESSION['user_id']</th><th>Status</th></tr>";

foreach($pages as $page) {
    $status = file_exists($page) ? "✓ EXISTS" : "✗ MISSING";
    $userSession = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : "NOT SET";
    echo "<tr>";
    echo "<td>$page</td>";
    echo "<td>" . (session_status() == PHP_SESSION_ACTIVE ? "ACTIVE" : "INACTIVE") . "</td>";
    echo "<td>$userSession</td>";
    echo "<td>$status</td>";
    echo "</tr>";
}

echo "</table>";
echo "<br><br>";
echo "<strong>Current Session:</strong><br>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";
?>
