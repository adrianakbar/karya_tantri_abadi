<?php

use App\Http\Controllers\MemberCardController;
use App\Http\Controllers\SavingsReceiptController;
// use App\Http\Controllers\SalePrintController;
use App\Http\Controllers\BackupDownloadController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Models\UserRole;
use App\Models\Roles;

// API route untuk mengecek role dan redirect
Route::get('/check-auth-redirect', function () {
    if (!Auth::check()) {
        return response()->json(['redirect' => '/login']);
    }
    
    $user = Auth::user();
    $userRole = UserRole::where('user_id', $user->id)->first();
    
    if ($userRole) {
        $role = Roles::find($userRole->role_id);
        
        if ($role) {
            $redirectUrl = match ($role->name) {
                'admin', 'manager' => '/admin',
                'anggota' => '/anggota',
                'kasir', 'cashier', 'bendahara' => '/kasir',
                'spv', 'kepalayayasan', 'kepala_yayasan' => '/spv',
                default => '/auth/login',
            };
            
            return response()->json(['redirect' => $redirectUrl]);
        }
    }
    
    return response()->json(['redirect' => '/anggota']);
});

// Redirect root URL to role dashboard or login
Route::get('/', function () {
    if (Auth::check()) {
        $user = Auth::user();
        $userRole = UserRole::where('user_id', $user->id)->first();
        if ($userRole) {
            $role = Roles::find($userRole->role_id);
            if ($role) {
                return match ($role->name) {
                    'admin', 'manager' => redirect('/admin'),
                    'anggota' => redirect('/anggota'),
                    'kasir', 'cashier', 'bendahara' => redirect('/kasir'),
                    'spv', 'kepalayayasan', 'kepala_yayasan' => redirect('/spv'),
                    default => redirect('/auth/login'),
                };
            }
        }
        return redirect('/auth/login');
    }
    return redirect('/auth/login');
});

// Root route hanya untuk redirect user yang sudah login
Route::get('/home', function () {
    // If user is authenticated, redirect based on role
    if (Auth::check()) {
        $user = Auth::user();
        
        // Get user's role using the UserRole model
        $userRole = UserRole::where('user_id', $user->id)->first();
        
        if ($userRole) {
            $role = Roles::find($userRole->role_id);
            
            if ($role) {
                return match ($role->name) {
                    'admin', 'manager' => redirect('/admin'),
                    'anggota' => redirect('/anggota'),
                    'kasir', 'cashier', 'bendahara' => redirect('/kasir'),
                    'spv', 'kepalayayasan', 'kepala_yayasan' => redirect('/spv'),
                    default => redirect('/auth/login'),
                };
            }
        }
        return redirect('/auth/login');
    }
    // If not authenticated, show login page
    return redirect('/auth/login');
});

// Login ditangani oleh Filament LoginPanelProvider (path: /auth)
// Route /login sebagai fallback untuk POST jika Livewire gagal load
Route::get('/login', function () {
    return redirect('/auth/login');
})->name('login');

Route::post('/login', function () {
    if (Auth::check()) {
        return redirect('/home');
    }
    session()->flash('error', 'Gagal memproses login. Silakan coba lagi.');
    return redirect('/auth/login');
})->name('login.fallback');

// Compatibility route for testing /admin/login form submission
Route::post('/admin/login', function (\Illuminate\Http\Request $request) {
    $credentials = $request->only('email', 'password');
    if (Auth::attempt($credentials)) {
        return app(\App\Http\Responses\CustomLoginResponse::class)->toResponse($request);
    }
    return redirect('/auth/login')
        ->withErrors(['email' => 'Email atau password salah.'])
        ->withInput();
});


Route::middleware('auth')->group(function () {
    Route::get('/member/{user}/card/print', [MemberCardController::class, 'printSingle'])
        ->name('member.card.print');
    
    Route::get('/member/cards/print', [MemberCardController::class, 'printMultiple'])
        ->name('member.cards.print');

    Route::get('/savings/{transaction}/print', [SavingsReceiptController::class, 'print'])
        ->name('savings.print');

    // Modul penjualan/inventaris dinonaktifkan untuk Karya Tantri Abadi
    // Route::get('/sales/{sale}/print', [SalePrintController::class, 'print'])
    //     ->name('sales.print');


    // Debug route
    Route::get('/debug-permissions', function () {
        $user = Auth::user();
        if (!$user) {
            return 'Not logged in';
        }

        $debug = [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email
            ],
            'roles' => $user->roles->map(fn($role) => [
                'name' => $role->name,
                'permissions' => $role->permissions->pluck('name')
            ])
        ];

        return response()->json($debug);
    });
});

// Debug route for testing login redirect
Route::get('/test-login-redirect/{email}', function ($email) {
    $user = \App\Models\User::where('email', $email)->first();
    
    if (!$user) {
        return response()->json(['error' => 'User not found'], 404);
    }
    
    // Simulate login
    Auth::login($user);
    
    // Create instance of CustomLoginResponse
    $loginResponse = new \App\Http\Responses\CustomLoginResponse();
    $request = request();
    
    // Get the response
    $response = $loginResponse->toResponse($request);
    
    return response()->json([
        'user' => $user->email,
        'response_type' => get_class($response),
        'redirect_url' => $response instanceof \Illuminate\Http\RedirectResponse ? $response->getTargetUrl() : 'N/A',
        'logs' => 'Check storage/logs/laravel.log for detailed logs'
    ]);
});

// Route untuk download backup dengan validasi session
Route::get('/download-backup/{fileName}', [BackupDownloadController::class, 'download'])
    ->middleware('auth')
    ->name('backup.download');
