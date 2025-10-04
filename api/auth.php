<?php
$pdo = db();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
  $body = json_decode(file_get_contents('php://input'), true) ?: [];
  $email = $body['email'] ?? '';
  $password = $body['password'] ?? '';
  
  if (!$email || !$password) json(['error'=>'Email and password required'],400);
  
  // Check if user exists
  $st = $pdo->prepare('SELECT * FROM users WHERE email = ?');
  $st->execute([$email]);
  $user = $st->fetch(PDO::FETCH_ASSOC);
  
  if (!$user) {
    json(['error'=>'User not found'],401);
  }
  
  // Verify password
  if (!password_verify($password, $user['password_hash'])) {
    json(['error'=>'Invalid credentials'],401);
  }
  
  // Generate session token
  $token = base64_encode(hash_hmac('sha256', $user['id'].'|'.time(), 'dev-secret', true));
  
  // Store session
  $sessionId = bin2hex(random_bytes(16));
  $st = $pdo->prepare('INSERT INTO sessions (id, user_id, token, created_at, expires_at) VALUES (?, ?, ?, NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY))');
  $st->execute([$sessionId, $user['id'], $token]);
  
  json([
    'success' => true,
    'token' => $token,
    'user' => [
      'id' => $user['id'],
      'name' => $user['name'],
      'email' => $user['email'],
      'phone' => $user['phone'] ?? '',
      'role' => $user['role_id']
    ]
  ]);
}

// GET /auth/me - get current user info
if ($method === 'GET') {
  $token = $_SERVER['HTTP_AUTHORIZATION'] ?? $_GET['token'] ?? '';
  $token = str_replace('Bearer ', '', $token);
  
  if (!$token) json(['error'=>'Token required'],401);
  
  $st = $pdo->prepare('SELECT u.* FROM users u JOIN sessions s ON u.id = s.user_id WHERE s.token = ? AND s.expires_at > NOW()');
  $st->execute([$token]);
  $user = $st->fetch(PDO::FETCH_ASSOC);
  
  if (!$user) json(['error'=>'Invalid token'],401);
  
  json([
    'success' => true,
    'user' => [
      'id' => $user['id'],
      'name' => $user['name'],
      'email' => $user['email'],
      'phone' => $user['phone'] ?? '',
      'role' => $user['role_id']
    ]
  ]);
}

// DELETE /auth/delete - delete user account
if ($method === 'DELETE') {
  $token = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
  $token = str_replace('Bearer ', '', $token);
  
  if (!$token) json(['error'=>'Token required'],401);
  
  // Get user from token
  $st = $pdo->prepare('SELECT u.* FROM users u JOIN sessions s ON u.id = s.user_id WHERE s.token = ? AND s.expires_at > NOW()');
  $st->execute([$token]);
  $user = $st->fetch(PDO::FETCH_ASSOC);
  
  if (!$user) json(['error'=>'Invalid token'],401);
  
  try {
    $pdo->beginTransaction();
    
    // Delete user's sessions
    $st = $pdo->prepare('DELETE FROM sessions WHERE user_id = ?');
    $st->execute([$user['id']]);
    
    // Cancel any active bookings
    $st = $pdo->prepare('UPDATE bookings SET status = "cancelled" WHERE user_id = ? AND status IN ("pending", "confirmed")');
    $st->execute([$user['id']]);
    
    // Delete user's bikes (if they own any)
    $st = $pdo->prepare('DELETE FROM bikes WHERE owner_id = ?');
    $st->execute([$user['id']]);
    
    // Delete user's bookings
    $st = $pdo->prepare('DELETE FROM bookings WHERE user_id = ?');
    $st->execute([$user['id']]);
    
    // Delete user account
    $st = $pdo->prepare('DELETE FROM users WHERE id = ?');
    $st->execute([$user['id']]);
    
    $pdo->commit();
    
    json([
      'success' => true,
      'message' => 'Account deleted successfully'
    ]);
    
  } catch (Exception $e) {
    $pdo->rollBack();
    json(['error'=>'Failed to delete account: '.$e->getMessage()],500);
  }
}
?>