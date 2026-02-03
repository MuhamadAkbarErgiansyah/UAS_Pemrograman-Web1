<?php
header('Content-Type: application/json');
require_once __DIR__.'/../config.php';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if(!$id) { echo json_encode(['success'=>false,'message'=>'id missing']); exit; }
$stmt = $pdo->prepare("SELECT * FROM items WHERE id = ?");
$stmt->execute([$id]);
$item = $stmt->fetch();
if($item) echo json_encode(['success'=>true,'data'=>$item]);
else echo json_encode(['success'=>false,'message'=>'Not found']);
