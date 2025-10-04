<?php
$pdo = db();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
  if (!empty($_GET['id'])) {
    $st = $pdo->prepare('SELECT * FROM bikes WHERE id=?');
    $st->execute([$_GET['id']]);
    json($st->fetch(PDO::FETCH_ASSOC) ?: []);
  }
  $q      = $_GET['q'] ?? '';
  $loc    = $_GET['location'] ?? '';
  $status = $_GET['status'] ?? '';
  $verify = $_GET['verify'] ?? '';
  $limit  = (int)($_GET['limit'] ?? 0);

  $sql = 'SELECT * FROM bikes WHERE 1=1'; $p = [];
  if ($q !== '')      { $sql .= ' AND (name LIKE ? OR model LIKE ?)'; $p[] = "%$q%";   $p[] = "%$q%"; }
  if ($loc !== '')    { $sql .= ' AND (location LIKE ? OR city LIKE ?)'; $p[] = "%$loc%"; $p[] = "%$loc%"; }
  if ($status !== '') { $sql .= ' AND availability_status=?'; $p[] = $status; }
  if ($verify !== '') { $sql .= ' AND verification_status=?'; $p[] = $verify; }
  $sql .= ' ORDER BY created_at DESC';
  if ($limit > 0) $sql .= ' LIMIT '.(int)$limit;

  $st = $pdo->prepare($sql);
  $st->execute($p);
  json($st->fetchAll(PDO::FETCH_ASSOC));
}

if ($method === 'POST') {
  $b = json_decode(file_get_contents('php://input'), true) ?: [];
  $b += ['availability_status'=>'Inactive','verification_status'=>'Pending'];
  $st = $pdo->prepare('INSERT INTO bikes (id,owner_id,name,type,location,price_hour,price_day,registration_number,availability_status,verification_status,image_url,created_at,model,city) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
  $id = $b['id'] ?? bin2hex(random_bytes(8));
  $st->execute([
    $id, $b['owner_id']??'', $b['name']??'', $b['type']??'', $b['location']??'',
    $b['price_hour']??0, $b['price_day']??0, $b['registration_number']??'',
    $b['availability_status'], $b['verification_status'], $b['image_url']??'',
    date('Y-m-d H:i:s'), $b['model']??'', $b['city']??''
  ]);
  json(['success'=>true,'id'=>$id,'bike_id'=>$id],201);
}

if (in_array($method, ['PUT','PATCH'], true)) {
  parse_str(file_get_contents('php://input'), $b);
  $id = $_GET['id'] ?? '';
  if (!$id) json(['error'=>'id required'],400);

  $fields = ['name','type','location','price_hour','price_day','registration_number','availability_status','verification_status','image_url','model','city'];
  $set = []; $p = [];
  foreach ($fields as $f) {
    if (isset($b[$f])) { $set[] = "$f=?"; $p[] = $b[$f]; }
  }
  if (!$set) json(['error'=>'nothing to update'],400);

  $p[] = $id;
  $sql = 'UPDATE bikes SET '.implode(',', $set).' WHERE id=?';
  $st = $pdo->prepare($sql);
  $st->execute($p);
  json(['success'=>true]);
}

if ($method === 'DELETE') {
  $id = $_GET['id'] ?? '';
  if (!$id) json(['error'=>'id required'],400);
  $st = $pdo->prepare('DELETE FROM bikes WHERE id=?');
  $st->execute([$id]);
  json(['success'=>true]);
}
?>