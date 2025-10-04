<?php
$method = $_SERVER['REQUEST_METHOD'];
$path   = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$base = '/myproject/api';
$rel  = '/'.trim(preg_replace('#^'.preg_quote($base,'#').'#','',$path),'/');
if ($rel === '/') $rel = '/health';

echo "Method: $method\n";
echo "Path: $path\n";
echo "Base: $base\n";
echo "Relative: $rel\n";

echo "\nTesting auth/login match:\n";
echo "rel === '/auth/login': " . ($rel === '/auth/login' ? 'true' : 'false') . "\n";
echo "method === 'POST': " . ($method === 'POST' ? 'true' : 'false') . "\n";
echo "Combined: " . (($rel === '/auth/login' && $method === 'POST') ? 'true' : 'false') . "\n";
?>
