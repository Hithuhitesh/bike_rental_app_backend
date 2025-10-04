<?php
header('Content-Type: application/json');
require_once __DIR__.'/db.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $body = json_decode(file_get_contents('php://input'), true) ?: [];
    $email = $body['email'] ?? '';
    $password = $body['password'] ?? '';
    
    if (!$email) {
        json(['error'=>'Email required'],400);
    }
    
    try {
        $pdo = db();
        
        // Check if user exists
        $st = $pdo->prepare('SELECT * FROM users WHERE email = ?');
        $st->execute([$email]);
        $user = $st->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            // Auto-register new user
            $userId = bin2hex(random_bytes(8));
            $name = $body['name'] ?? explode('@', $email)[0];
            $phone = $body['phone'] ?? '';
            $hashedPassword = password_hash($password ?: 'default123', PASSWORD_BCRYPT);
            
            $st = $pdo->prepare('INSERT INTO users (id, name, email, phone, password_hash, role_id, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())');
            $st->execute([$userId, $name, $email, $phone, $hashedPassword, 'user']);
            
            $user = [
                'id' => $userId,
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'role_id' => 'user'
            ];
        } else {
            // Verify password if provided
            if ($password && !password_verify($password, $user['password_hash'])) {
                json(['error'=>'Invalid credentials'],401);
            }
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
        
    } catch (Exception $e) {
        json(['error' => 'Server error: ' . $e->getMessage()], 500);
    }
}

// GET /auth/me - get current user info
if ($method === 'GET') {
    $token = $_SERVER['HTTP_AUTHORIZATION'] ?? $_GET['token'] ?? '';
    $token = str_replace('Bearer ', '', $token);
    
    if (!$token) json(['error'=>'Token required'],401);
    
    try {
        $pdo = db();
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
    } catch (Exception $e) {
        json(['error' => 'Server error: ' . $e->getMessage()], 500);
    }
}

json(['error' => 'Method not allowed'], 405);
?>




