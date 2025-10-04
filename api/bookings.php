<?php
$pdo = db();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
  if (isset($_GET['bike_id'])) {
    $st = $pdo->prepare('SELECT * FROM bookings WHERE bike_id=? ORDER BY start_time DESC');
    $st->execute([$_GET['bike_id']]);
    json($st->fetchAll(PDO::FETCH_ASSOC));
  }
  
  // Get all bookings, optionally filtered by user_id
  $userId = $_GET['user_id'] ?? '';
  $sql = 'SELECT b.*, bk.name as bike_name, bk.model, bk.location, bk.image_url, bk.owner_id FROM bookings b LEFT JOIN bikes bk ON b.bike_id = bk.id WHERE 1=1';
  $p = [];
  
  if ($userId !== '') {
    $sql .= ' AND b.user_id = ?';
    $p[] = $userId;
  }
  
  $sql .= ' ORDER BY b.created_at DESC';
  $st = $pdo->prepare($sql);
  $st->execute($p);
  json($st->fetchAll(PDO::FETCH_ASSOC));
}

if ($method === 'POST') {
  $b = json_decode(file_get_contents('php://input'), true) ?: [];
  $st = $pdo->prepare('INSERT INTO bookings (id,user_id,bike_id,start_time,end_time,pricing_mode,total_amount,status,created_at) VALUES (?,?,?,?,?,?,?,?,NOW())');
  $id = $b['id'] ?? bin2hex(random_bytes(8));
  $st->execute([
    $id,
    $b['user_id']??'', 
    $b['bike_id']??'', 
    $b['start_time']??'', 
    $b['end_time']??'', 
    $b['pricing_mode']??'hourly', 
    $b['total_amount']??0, 
    'Active'
  ]);
  json(['success'=>true,'id'=>$id,'booking_id'=>$id],201);
}

if (in_array($method, ['PUT','PATCH'], true) && isset($_GET['id'])) {
  $body = json_decode(file_get_contents('php://input'), true) ?: [];
  $status = $body['status'] ?? 'Cancelled';
  $st = $pdo->prepare('UPDATE bookings SET status=? WHERE id=?');
  $st->execute([$status, $_GET['id']]);
  json(['success'=>true]);
}
?>