<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /** Role name -> panel path. Mirrors User::canAccessPanel(). */
    private const ROLE_PANEL = [
        'admin' => '/admin',
        'manager' => '/admin',
        'anggota' => '/anggota',
        'petugas' => '/petugas',
        'kasir' => '/kasir',
        'cashier' => '/kasir',
        'bendahara' => '/kasir',
        'spv' => '/spv',
        'kepalayayasan' => '/spv',
        'kepala_yayasan' => '/spv',
    ];

    public function show()
    {
        if (Auth::check()) {
            return $this->redirectForRole(Auth::user()) ?? redirect('/admin');
        }

        return view('login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withInput($request->only('email', 'remember'))
                ->withErrors(['email' => 'Email atau password salah.']);
        }

        $request->session()->regenerate();

        $target = $this->redirectForRole(Auth::user());

        if (! $target) {
            // Authenticated but no role maps to a panel — deny.
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Akun ini tidak memiliki akses ke panel manapun.']);
        }

        return $target;
    }

    /** @return \Illuminate\Http\RedirectResponse|null */
    private function redirectForRole($user)
    {
        foreach ($user->roles as $role) {
            if (isset(self::ROLE_PANEL[$role->name])) {
                return redirect()->intended(self::ROLE_PANEL[$role->name]);
            }
        }

        return null;
    }
}
