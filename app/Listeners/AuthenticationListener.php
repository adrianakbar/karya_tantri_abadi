<?php

namespace App\Listeners;

use App\Services\AuditTrailService;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;

class AuthenticationListener
{
    /**
     * Handle user login events.
     */
    public function handleLogin(Login $event): void
    {
        AuditTrailService::logAuth(
            'login',
            $event->user->id,
            $event->user->cooperation_id ?? null
        );

        AuditTrailService::logActivity(
            'login',
            'authentication',
            "User logged in: {$event->user->name} ({$event->user->email})",
            $event->user->id,
            $event->user->cooperation_id ?? null
        );
    }

    /**
     * Handle user logout events.
     */
    public function handleLogout(Logout $event): void
    {
        if ($event->user) {
            AuditTrailService::logAuth(
                'logout',
                $event->user->id,
                $event->user->cooperation_id ?? null
            );

            AuditTrailService::logActivity(
                'logout',
                'authentication',
                "User logged out: {$event->user->name} ({$event->user->email})",
                $event->user->id,
                $event->user->cooperation_id ?? null
            );
        }
    }

    /**
     * Handle failed login attempts.
     */
    public function handleFailed(Failed $event): void
    {
        AuditTrailService::logAuth(
            'failed_login',
            null,
            null
        );

        $email = $event->credentials['email'] ?? 'unknown';
        AuditTrailService::logActivity(
            'failed_login',
            'authentication',
            "Failed login attempt for: {$email}"
        );
    }

    /**
     * Handle account lockout events.
     */
    public function handleLockout(Lockout $event): void
    {
        $email = $event->request->input('email', 'unknown');
        AuditTrailService::logActivity(
            'lockout',
            'security',
            "Account locked out: {$email}"
        );
    }

    /**
     * Handle password reset events.
     */
    public function handlePasswordReset(PasswordReset $event): void
    {
        AuditTrailService::logActivity(
            'password_reset',
            'authentication',
            "Password reset for user: {$event->user->name} ({$event->user->email})",
            $event->user->id,
            $event->user->cooperation_id ?? null
        );
    }

    /**
     * Handle user registration events.
     */
    public function handleRegistered(Registered $event): void
    {
        AuditTrailService::logActivity(
            'user_registered',
            'user_management',
            "New user registered: {$event->user->name} ({$event->user->email})",
            $event->user->id,
            $event->user->cooperation_id ?? null
        );
    }

    /**
     * Handle email verification events.
     */
    public function handleVerified(Verified $event): void
    {
        AuditTrailService::logActivity(
            'email_verified',
            'authentication',
            "Email verified for user: {$event->user->name} ({$event->user->email})",
            $event->user->id,
            $event->user->cooperation_id ?? null
        );
    }

    /**
     * Register the listeners for the subscriber.
     */
    public function subscribe($events): array
    {
        return [
            Login::class => 'handleLogin',
            Logout::class => 'handleLogout',
            Failed::class => 'handleFailed',
            Lockout::class => 'handleLockout',
            PasswordReset::class => 'handlePasswordReset',
            Registered::class => 'handleRegistered',
            Verified::class => 'handleVerified',
        ];
    }
}
