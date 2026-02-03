<?php
// config.php

// ===== CONFIGURE SESSION SETTINGS BEFORE START =====
// Ensure secure session handling
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
ini_set('session.use_strict_mode', 1);

// ===== CEK SESSION =====
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// ===== SESSION PERSISTENCE CHECK =====
// Jika session sudah ada sebelumnya, pastikan masih valid
if(isset($_SESSION['user_id'])) {
    // Session is active and valid
}

$host = 'localhost';
$db   = 'uts_web';
$user = 'root';
$pass = ''; // ganti jika Anda punya password MySQL
$charset = 'utf8mb4';
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (Exception $e) {
    die('Database connection failed: '.$e->getMessage());
}
?>