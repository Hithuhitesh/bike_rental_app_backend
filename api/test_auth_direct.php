<?php
// Simulate POST request to auth.php
$_SERVER['REQUEST_METHOD'] = 'POST';

// Simulate POST body
$postData = json_encode([
    'email' => 'test@example.com',
    'password' => 'test123',
    'name' => 'Test User'
]);

// Mock php://input
file_put_contents('php://temp', $postData);

// Include the auth script
require_once __DIR__.'/api/auth.php';
?>
