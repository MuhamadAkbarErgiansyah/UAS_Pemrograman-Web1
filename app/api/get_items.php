<?php
header('Content-Type: application/json');
require_once __DIR__.'/../config.php';
$stmt = $pdo->query("SELECT id,title,slug,summary,price,image,created_at FROM items ORDER BY created_at DESC");
$data = $stmt->fetchAll();
echo json_encode(['success'=>true,'data'=>$data]);
