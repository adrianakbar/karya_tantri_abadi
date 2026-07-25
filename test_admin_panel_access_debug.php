<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Cooperation;
use App\Models\Roles;
use App\Models\UserRole;

// Listen to all log messages
\Illuminate\Support\Facades\Event::listen(\Illuminate\Log\Events\MessageLogged::class, function ($event) {
    echo "LOG [{$event->level}]: {$event->message}\n";
    if (isset($event->context['exception'])) {
        $e = $event->context['exception'];
        echo "EXCEPTION class: " . get_class($e) . "\n";
        echo "EXCEPTION message: " . $e->getMessage() . "\n";
        echo "EXCEPTION file: " . $e->getFile() . ":" . $e->getLine() . "\n";
        echo "EXCEPTION TRACE:\n" . $e->getTraceAsString() . "\n";
    }
});

// Also register a custom exception renderer to dump the exception directly
$app->instance(
    \Illuminate\Contracts\Debug\ExceptionHandler::class,
    new class($app) extends \Illuminate\Foundation\Exceptions\Handler {
        public function render($request, \Throwable $e) {
            $msg = "RENDER EXCEPTION: " . get_class($e) . " - " . $e->getMessage() . "\n" .
                   "FILE: " . $e->getFile() . ":" . $e->getLine() . "\n" .
                   "TRACE:\n" . $e->getTraceAsString() . "\n";
            file_put_contents('/home/adrianakbar/Pribadi/Portofolio/skripsi/karya-tantri-abadi/debug_403_trace.txt', $msg);
            return parent::render($request, $e);
        }
    }
);

$cooperation = Cooperation::first() ?: Cooperation::create([
    'code' => 'TEST001',
    'name' => 'Test Cooperation',
    'address' => 'Test Address',
    'phone' => '08123456789',
    'email' => 'test@cooperation.com',
]);

$anggotaUser = User::where('email', 'anggota@test.com')->first() ?: User::create([
    'name' => 'Anggota User',
    'email' => 'anggota@test.com',
    'password' => bcrypt('password'),
    'cooperation_id' => $cooperation->id,
]);

$anggotaRole = Roles::where('name', 'anggota')->first() ?: Roles::create([
    'name' => 'anggota',
    'cooperation_id' => $cooperation->id,
]);

UserRole::firstOrCreate([
    'user_id' => $anggotaUser->id,
    'role_id' => $anggotaRole->id,
]);

auth()->login($anggotaUser);

$request = Illuminate\Http\Request::create('/admin', 'GET');
$request->setLaravelSession($app['session']->driver());

try {
    $response = $app->make(Illuminate\Contracts\Http\Kernel::class)->handle($request);
    echo "STATUS: " . $response->getStatusCode() . "\n";
} catch (\Exception $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n";
}
