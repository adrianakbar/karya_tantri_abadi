<?php

namespace App\Providers;

use App\Listeners\AuthenticationListener;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Login::class => [
            AuthenticationListener::class . '@handleLogin',
        ],
        Logout::class => [
            AuthenticationListener::class . '@handleLogout',
        ],
        Failed::class => [
            AuthenticationListener::class . '@handleFailed',
        ],
        Lockout::class => [
            AuthenticationListener::class . '@handleLockout',
        ],
        PasswordReset::class => [
            AuthenticationListener::class . '@handlePasswordReset',
        ],
        Registered::class => [
            AuthenticationListener::class . '@handleRegistered',
        ],
        Verified::class => [
            AuthenticationListener::class . '@handleVerified',
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
