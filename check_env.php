<?php
// Boot Laravel
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Laravel Environment: " . app()->environment() . "\n";
echo "DB Connection: " . config('database.default') . "\n";
echo "DB Host: " . config('database.connections.mysql.host') . "\n";
echo "DB Port: " . config('database.connections.mysql.port') . "\n";
echo "DB Database: " . config('database.connections.mysql.database') . "\n";
echo "DB Username: " . config('database.connections.mysql.username') . "\n";
echo "DB Password: " . config('database.connections.mysql.password') . "\n";
