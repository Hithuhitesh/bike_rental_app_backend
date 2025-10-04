<?php
require_once __DIR__.'/api/db.php';

// Test data
$testData = [
    'email' => 'test@example.com',
    'password' => 'test123',
    'name' => 'Test User'
];

echo "Testing authentication...\n";

try {
    $pdo = db();
    echo "Database connection: OK\n";
    
    // Check if user exists
    $st = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $st->execute([$testData['email']]);
    $user = $st->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo "User not found, creating new user...\n";
        
        // Create new user
        $userId = bin2hex(random_bytes(8));
        $hashedPassword = password_hash($testData['password'], PASSWORD_BCRYPT);
        
        $st = $pdo->prepare('INSERT INTO users (id, name, email, phone, password_hash, role_id, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())');
        $st->execute([$userId, $testData['name'], $testData['email'], '', $hashedPassword, 'user']);
        
        echo "User created with ID: $userId\n";
        
        // Fetch the created user
        $st = $pdo->prepare('SELECT * FROM users WHERE id = ?');
        $st->execute([$userId]);
        $user = $st->fetch(PDO::FETCH_ASSOC);
    } else {
        echo "User found: " . $user['name'] . "\n";
    }
    
    // Generate token
    $token = base64_encode(hash_hmac('sha256', $user['id'].'|'.time(), 'dev-secret', true));
    echo "Generated token: $token\n";
    
    // Store session
    $sessionId = bin2hex(random_bytes(16));
    $st = $pdo->prepare('INSERT INTO sessions (id, user_id, token, created_at, expires_at) VALUES (?, ?, ?, NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY))');
    $st->execute([$sessionId, $user['id'], $token]);
    
    echo "Session created with ID: $sessionId\n";
    
    // Return success response
    $response = [
        'success' => true,
        'token' => $token,
        'user' => [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'phone' => $user['phone'] ?? '',
            'role' => $user['role_id']
        ]
    ];
    
    echo "Response: " . json_encode($response, JSON_PRETTY_PRINT) . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
?>
