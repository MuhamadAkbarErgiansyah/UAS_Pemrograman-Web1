<?php
require 'config.php';

echo "=== RESERVATIONS TABLE STRUCTURE ===\n\n";
$result = $pdo->query('DESCRIBE reservations')->fetchAll(PDO::FETCH_ASSOC);
foreach($result as $row) {
  echo $row['Field'] . " - " . $row['Type'] . "\n";
}

echo "\n=== ITEMS TABLE STRUCTURE ===\n\n";
$result = $pdo->query('DESCRIBE items')->fetchAll(PDO::FETCH_ASSOC);
foreach($result as $row) {
  echo $row['Field'] . " - " . $row['Type'] . "\n";
}
?>
