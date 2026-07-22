<?php

namespace App\Http\Responses;

use Filament\Http\Responses\Auth\Contracts\LogoutResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Session;
use Livewire\Features\SupportRedirects\Redirector;

class CustomLogoutResponse implements LogoutResponse
{
    public function toResponse($request): RedirectResponse|Redirector
    {
        Session::flush();
        Session::regenerate();

        return redirect()->to('/');
    }
}
