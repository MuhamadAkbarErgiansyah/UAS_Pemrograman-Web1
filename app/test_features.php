<?php
require 'config.php';

echo "=== Testing Tables ===\n";
$r = $pdo->query('SELECT COUNT(*) FROM notifications');
echo "Notifications count: " . $r->fetchColumn() . "\n";

$r = $pdo->query('SELECT COUNT(*) FROM messages');
echo "Messages count: " . $r->fetchColumn() . "\n";

$r = $pdo->query('SELECT COUNT(*) FROM vouchers');
echo "Vouchers count: " . $r->fetchColumn() . "\n";

$r = $pdo->query('SELECT COUNT(*) FROM review_replies');
echo "Review Replies count: " . $r->fetchColumn() . "\n";

echo "\n=== review_replies Table Structure ===\n";
$cols = $pdo->query("DESCRIBE review_replies")->fetchAll();
foreach($cols as $col) {
    echo "{$col['Field']} - {$col['Type']}\n";
}

echo "\nAll tests passed!\n";
