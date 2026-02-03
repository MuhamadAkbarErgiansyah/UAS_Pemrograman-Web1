<?php
header('Content-Type: application/json');
require_once __DIR__.'/../config.php';
$input = json_decode(file_get_contents('php://input'), true);
$id = $input['id'] ?? 0;
if(!$id){ echo json_encode(['success'=>false,'message'=>'id required']); exit; }
try {
  $stmt = $pdo->prepare("UPDATE items SET title=?, slug=?, summary=?, content=?, image=?, price=? WHERE id=?");
  $stmt->execute([$input['title'],$input['slug'],$input['summary'],$input['content'],$input['image'],$input['price'],$id]);
  echo json_encode(['success'=>true]);
} catch(Exception $e){
  echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}
