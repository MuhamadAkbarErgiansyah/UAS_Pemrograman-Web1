<?php
header('Content-Type: application/json');

require '../config.php';

$slug = $_GET['slug'] ?? '';

if(empty($slug)) {
  echo json_encode(['success' => false, 'message' => 'Slug tidak ditemukan']);
  exit;
}

// Ambil data package dari database
$stmt = $pdo->prepare("SELECT * FROM items WHERE slug = ?");
$stmt->execute([$slug]);
$package = $stmt->fetch();

if(!$package) {
  echo json_encode(['success' => false, 'message' => 'Paket tidak ditemukan']);
  exit;
}

// Return JSON response
echo json_encode([
  'success' => true,
  'package' => [
    'id' => $package['id'],
    'title' => htmlspecialchars($package['title']),
    'slug' => htmlspecialchars($package['slug']),
    'summary' => htmlspecialchars($package['summary']),
    'content' => $package['content'], // Bisa contain HTML
    'image' => htmlspecialchars($package['image']),
    'price' => $package['price']
  ]
]);
?>
