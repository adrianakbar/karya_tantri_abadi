<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Cooperation;
use App\Models\Roles;
use App\Models\UserRole;

$cooperation = Cooperation::first() ?: Cooperation::create([
    'code' => 'TEST001',
    'name' => 'Test Cooperation',
    'address' => 'Test Address',
    'phone' => '08123456789',
    'email' => 'test@cooperation.com',
]);

$adminUser = User::where('email', 'admin@test.com')->first() ?: User::create([
    'name' => 'Admin User',
    'email' => 'admin@test.com',
    'password' => bcrypt('password'),
    'cooperation_id' => $cooperation->id,
]);

$adminRole = Roles::where('name', 'admin')->first() ?: Roles::create([
    'name' => 'admin',
    'cooperation_id' => $cooperation->id,
]);

UserRole::firstOrCreate([
    'user_id' => $adminUser->id,
    'role_id' => $adminRole->id,
]);

auth()->login($adminUser);

// Bind the authenticated user to the request session
$request = Illuminate\Http\Request::create('/admin', 'GET');
$request->setLaravelSession($app['session']->driver());

try {
    $response = $app->make(Illuminate\Contracts\Http\Kernel::class)->handle($request);
    echo "STATUS: " . $response->getStatusCode() . "\n";
    if ($response->getStatusCode() === 403) {
        echo "403 DETAILS: " . substr($response->getContent(), 0, 1000) . "\n";
    }
} catch (\Exception $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n";
}
