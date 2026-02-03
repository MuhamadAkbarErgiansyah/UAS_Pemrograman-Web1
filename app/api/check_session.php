<?php
header('Content-Type: application/json');
require __DIR__ . '/../config.php';

// Check if user session is valid
if(isset($_SESSION['user_id'])){
  // Check if session still exists in database (optional)
  echo json_encode(['valid' => true, 'user_id' => $_SESSION['user_id']]);
} else {
  echo json_encode(['valid' => false]);
}
exit;
?>
