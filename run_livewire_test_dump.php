<?php
// Boot Laravel
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Cooperation;
use App\Models\ActivityLog;
use App\Filament\Resources\ActivityLogResource;
use Livewire\Livewire;

// Setup test data
$cooperation = Cooperation::first() ?: Cooperation::create([
    'code' => 'TEST001',
    'name' => 'Test Cooperation',
    'address' => 'Test Address',
    'phone' => '08123456789',
    'email' => 'test@cooperation.com',
]);

$user = User::where('email', 'test@example.com')->first() ?: User::create([
    'name' => 'Test User',
    'email' => 'test@example.com',
    'password' => bcrypt('password'),
    'cooperation_id' => $cooperation->id,
]);

$log = ActivityLog::create([
    'user_id' => $user->id,
    'cooperation_id' => $cooperation->id,
    'action' => 'view',
    'module' => 'dashboard',
    'description' => 'Dashboard Access',
    'ip_address' => '127.0.0.1',
    'user_agent' => 'Test Agent',
]);

auth()->login($user);

try {
    $component = Livewire::test(ActivityLogResource\Pages\ViewActivityLog::class, ['record' => $log->id]);
    $html = $component->html();
    echo "HTML LENGTH: " . strlen($html) . "\n";
    file_put_contents('livewire_dump.html', $html);
    echo "SUCCESS: Saved HTML to livewire_dump.html\n";
    
    // Check if 'Dashboard Access' is in html
    if (str_contains($html, 'Dashboard Access')) {
        echo "FOUND: 'Dashboard Access' in HTML\n";
    } else {
        echo "NOT FOUND: 'Dashboard Access' in HTML\n";
    }
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
