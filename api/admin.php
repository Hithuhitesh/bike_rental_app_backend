<?php
require_once __DIR__.'/db.php';

$pdo = db();
$body = json_decode(file_get_contents('php://input'), true) ?: [];
$key  = $body['key'] ?? ($_GET['key'] ?? '');
if ($key !== 'dev') json(['error'=>'forbidden'],403);

// roles
$pdo->exec("CREATE TABLE IF NOT EXISTS roles (
  id VARCHAR(64) PRIMARY KEY,
  name VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// users
$pdo->exec("CREATE TABLE IF NOT EXISTS users (
  id VARCHAR(64) PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  phone VARCHAR(30) DEFAULT '',
  password_hash VARCHAR(255) NOT NULL,
  role_id VARCHAR(64) DEFAULT 'user',
  created_at DATETIME NOT NULL,
  FOREIGN KEY (role_id) REFERENCES roles(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// admins (simplified, no wrong columns)
$pdo->exec("CREATE TABLE IF NOT EXISTS admins (
  user_id VARCHAR(255) PRIMARY KEY,
  email VARCHAR(255) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  is_super TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// seed roles
$pdo->exec("INSERT IGNORE INTO roles (id,name) VALUES ('admin','Admin'),('user','User')");

// create default admin user
$adminId = bin2hex(random_bytes(8));
$st  = $pdo->prepare('INSERT INTO users (id,name,email,phone,password_hash,role_id,created_at) VALUES (?,?,?,?,?,?,NOW())');
$st->execute([
    $adminId,
    'Administrator',
    'admin@example.com',
    '',
    password_hash('admin123', PASSWORD_BCRYPT),
    'admin'
]);

// insert into admins
$st2 = $pdo->prepare('INSERT IGNORE INTO admins (user_id,email,password_hash,is_super) VALUES (?,?,?,1)');
$st2->execute([
    $adminId,
    'admin@example.com',
    password_hash('admin123', PASSWORD_BCRYPT)
]);


json([
    'ok'      => true,
    'admin_id'=> $adminId,
    'email'   => 'admin@example.com',
    'password'=> 'admin123'
]);
