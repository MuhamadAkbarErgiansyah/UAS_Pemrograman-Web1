<?php
require 'config.php';

echo "Fixing review_replies table...\n";

try {
    // Rename 'reply' to 'reply_text' if needed
    $pdo->exec("ALTER TABLE review_replies CHANGE reply reply_text TEXT NOT NULL");
    echo "Column renamed: reply -> reply_text\n";
} catch(Exception $e) {
    echo "Column rename skipped (may already be correct): " . $e->getMessage() . "\n";
}

try {
    // Add updated_at if not exists
    $pdo->exec("ALTER TABLE review_replies ADD updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
    echo "Column added: updated_at\n";
} catch(Exception $e) {
    echo "updated_at skipped (may already exist): " . $e->getMessage() . "\n";
}

echo "\nVerifying structure:\n";
$cols = $pdo->query("DESCRIBE review_replies")->fetchAll();
foreach($cols as $col) {
    echo "- {$col['Field']} ({$col['Type']})\n";
}

echo "\nDone!\n";
