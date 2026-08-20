<?php

namespace App\Http\Responses;

use Filament\Http\Responses\Auth\Contracts\LogoutResponse as Contract;
use Illuminate\Http\RedirectResponse;

class LogoutResponse implements Contract
{
    public function toResponse($request): RedirectResponse
    {
        return redirect()->route('login');
    }
}
