<?php
require 'config.php';

echo "<h2>Session Debug Info</h2>";
echo "<pre>";
echo "Session Status: " . (session_status() == PHP_SESSION_ACTIVE ? "ACTIVE" : "INACTIVE") . "\n";
echo "Session ID: " . session_id() . "\n\n";
echo "SESSION Array Contents:\n";
print_r($_SESSION);
echo "\n\nCOOKIES:\n";
print_r($_COOKIE);
?>
