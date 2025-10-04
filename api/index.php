<?php
require_once __DIR__.'/db.php';

$method = $_SERVER['REQUEST_METHOD'];
$path   = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$base = '/myproject/api';
$rel  = '/'.trim(preg_replace('#^'.preg_quote($base,'#').'#','',$path),'/');
if ($rel === '/') $rel = '/health';

switch (true) {
  case $rel === '/health':
    json(['ok'=>true,'ts'=>time()]);

  case ($rel === '/auth/login' && $method === 'POST') || ($rel === '/auth/me' && $method === 'GET') || ($rel === '/auth/delete' && $method === 'DELETE'):
    require __DIR__.'/auth.php';
    break;

  case $rel === '/bikes' && in_array($method, ['GET','POST'], true):
    require __DIR__.'/bikes.php';
    break;

  case preg_match('#^/bikes/([\\w-]+)$#', $rel, $m) && $method === 'GET':
    $_GET['id'] = $m[1];
    require __DIR__.'/bikes.php';
    break;

  case preg_match('#^/bikes/([\\w-]+)$#', $rel, $m) && in_array($method, ['PUT','PATCH'], true):
    $_GET['id'] = $m[1];
    require __DIR__.'/bikes.php';
    break;

  case preg_match('#^/bikes/([\\w-]+)$#', $rel, $m) && $method === 'DELETE':
    $_GET['id'] = $m[1];
    require __DIR__.'/bikes.php';
    break;

  case preg_match('#^/bikes/([\\w-]+)/bookings$#', $rel, $m) && $method === 'GET':
    $_GET['bike_id'] = $m[1];
    require __DIR__.'/bookings.php';
    break;

  case $rel === '/bookings' && in_array($method, ['GET','POST'], true):
    require __DIR__.'/bookings.php';
    break;

  case preg_match('#^/bookings/([\\w-]+)/cancel$#', $rel, $m) && in_array($method, ['PUT','PATCH'], true):
    $_GET['id'] = $m[1];
    require __DIR__.'/bookings.php';
    break;

  case $rel === '/admin/seed' && $method === 'POST':
    require __DIR__.'/admin.php';
    break;

  case $rel === '/privacy-policy' && $method === 'GET':
    json([
      'title' => 'Privacy Policy',
      'last_updated' => '2024-12-01',
      'content' => [
        'data_collection' => 'We collect information you provide directly to us, such as when you create an account, book a bike, or contact us for support.',
        'data_usage' => 'We use the information we collect to provide and improve our bike rental services, process bookings and payments, and ensure platform safety.',
        'data_sharing' => 'We do not sell, trade, or otherwise transfer your personal information to third parties without your consent.',
        'data_security' => 'We implement appropriate security measures to protect your personal information against unauthorized access.',
        'user_rights' => 'You have the right to access, correct, delete your personal information, and withdraw consent for data processing.',
        'contact' => 'If you have any questions about this Privacy Policy, please contact us at privacy@metroride.com'
      ]
    ]);
    break;

  default:
    json(['error'=>'Not Found','path'=>$rel],404);
}
?>