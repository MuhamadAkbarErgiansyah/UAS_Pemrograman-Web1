<?php
require 'config.php';

// Simple session test
echo "<h2>Session Persistence Test</h2>";
echo "<p>Current Page Load Time: " . date('Y-m-d H:i:s') . "</p>";

// Check if user data exists
if(isset($_SESSION['user_id'])) {
    echo "<div style='background-color: #90EE90; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
    echo "<strong>✓ SESSION ACTIVE</strong><br>";
    echo "User ID: " . $_SESSION['user_id'] . "<br>";
    echo "Username: " . $_SESSION['username'] . "<br>";
    echo "Email: " . $_SESSION['email'] . "<br>";
    echo "Role: " . $_SESSION['role'] . "<br>";
    echo "</div>";
} else {
    echo "<div style='background-color: #FFB6C6; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
    echo "<strong>✗ NO SESSION DATA</strong><br>";
    echo "User is NOT logged in or session was lost<br>";
    echo "</div>";
}

// Display all session variables
echo "<br><strong>All Session Variables:</strong><br>";
echo "<pre style='background-color: #f0f0f0; padding: 10px; border-radius: 5px;'>";
var_dump($_SESSION);
echo "</pre>";

// Display PHP session info
echo "<br><strong>PHP Session Info:</strong><br>";
echo "Session ID: " . session_id() . "<br>";
echo "Session Status: " . (session_status() == PHP_SESSION_ACTIVE ? "ACTIVE" : (session_status() == PHP_SESSION_NONE ? "NONE" : "DISABLED")) . "<br>";
echo "Session Save Path: " . session_save_path() . "<br>";

// Test navigation links
echo "<br><h3>Test Navigation:</h3>";
echo "<ul>";
echo "<li><a href='index.php'>Home</a></li>";
echo "<li><a href='paket.php'>Paket</a></li>";
echo "<li><a href='guide.php'>Guide</a></li>";
echo "<li><a href='galerry.php'>Galeri</a></li>";
echo "<li><a href='dashboard.php'>Dashboard</a></li>";
echo "</ul>";
echo "<p><strong>After clicking each link, check if you see 'SESSION ACTIVE' or 'NO SESSION DATA'</strong></p>";
?>
