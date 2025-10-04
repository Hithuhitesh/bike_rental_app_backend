<?php
require_once __DIR__.'/db.php';

try {
  $pdo = db();
  $pdo->query('SELECT 1');
  echo json_encode(['ok'=>true]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>$e->getMessage()]);
}