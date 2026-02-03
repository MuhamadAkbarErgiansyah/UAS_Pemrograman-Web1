<?php
header('Content-Type: application/json');
require_once __DIR__.'/../config.php';
$input = json_decode(file_get_contents('php://input'), true);
if(!$input) { echo json_encode(['success'=>false,'message'=>'Invalid JSON']); exit; }
$title = $input['title'] ?? '';
$slug = $input['slug'] ?? '';
$summary = $input['summary'] ?? '';
$content = $input['content'] ?? '';
$image = $input['image'] ?? '';
$price = $input['price'] ?? '';

if(!$title || !$slug){
  echo json_encode(['success'=>false,'message'=>'title and slug required']); exit;
}
try {
  $stmt = $pdo->prepare("INSERT INTO items (title,slug,summary,content,image,price) VALUES (?,?,?,?,?,?)");
  $stmt->execute([$title,$slug,$summary,$content,$image,$price]);
  echo json_encode(['success'=>true,'id'=>$pdo->lastInsertId()]);
} catch (Exception $e){
  echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}
