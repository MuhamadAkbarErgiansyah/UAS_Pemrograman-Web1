<?php
// File ini di-redirect ke add_item.php dengan parameter id
$id = $_GET['id'] ?? null;
if($id){
  header("Location: add_item.php?id=" . urlencode($id));
} else {
  header("Location: add_item.php");
}
exit;
?>
