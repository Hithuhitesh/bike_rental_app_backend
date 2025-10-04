<?php
require_once __DIR__.'/env.php';

function db() {
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    $host = defined('DB_HOST') ? DB_HOST : '127.0.0.1';
    $name = defined('DB_NAME') ? DB_NAME : 'bikerental';
    $user = defined('DB_USER') ? DB_USER : 'root';
    $pass = defined('DB_PASS') ? DB_PASS : '';
    $port = defined('DB_PORT') ? DB_PORT : null; // leave null for 3306

    $dsn = 'mysql:host='.$host.($port ? ';port='.$port : '').';dbname='.$name.';charset=utf8mb4';
    try {
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_PERSISTENT => false,
            PDO::ATTR_TIMEOUT => 5
        ]);
        return $pdo;
    } catch (Throwable $e) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(['error'=>'DB connection failed','detail'=>$e->getMessage()]);
        exit;
    }
}

function json($data, $status=200) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}