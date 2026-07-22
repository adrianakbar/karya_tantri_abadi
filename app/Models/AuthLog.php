<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class AuthLog extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'cooperation_id',
        'user_id',
        'ip_address',
        'user_agent',
        'action',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cooperation(): BelongsTo
    {
        return $this->belongsTo(Cooperation::class);
    }

    /**
     * Scope to filter by cooperation
     */
    public function scopeForCooperation(Builder $query, int $cooperationId): Builder
    {
        return $query->where('cooperation_id', $cooperationId);
    }

    /**
     * Scope to filter by date range
     */
    public function scopeDateRange(Builder $query, $startDate, $endDate): Builder
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope to filter successful logins
     */
    public function scopeSuccessfulLogins(Builder $query): Builder
    {
        return $query->whereIn('action', ['login', 'logout']);
    }

    /**
     * Scope to filter failed logins
     */
    public function scopeFailedLogins(Builder $query): Builder
    {
        return $query->where('action', 'failed_login');
    }

    /**
     * Get formatted action name
     */
    public function getFormattedActionAttribute(): string
    {
        return match($this->action) {
            'login' => 'Login',
            'logout' => 'Logout',
            'failed_login' => 'Failed Login',
            default => ucwords(str_replace('_', ' ', $this->action))
        };
    }

    /**
     * Get browser info from user agent
     */
    public function getBrowserInfoAttribute(): string
    {
        if (empty($this->user_agent)) {
            return 'Unknown';
        }

        if (str_contains($this->user_agent, 'Chrome')) {
            return 'Chrome';
        } elseif (str_contains($this->user_agent, 'Firefox')) {
            return 'Firefox';
        } elseif (str_contains($this->user_agent, 'Safari')) {
            return 'Safari';
        } elseif (str_contains($this->user_agent, 'Edge')) {
            return 'Edge';
        } elseif (str_contains($this->user_agent, 'Opera')) {
            return 'Opera';
        }

        return 'Other';
    }

    /**
     * Check if this is a suspicious login
     */
    public function isSuspicious(): bool
    {
        // Check for multiple failed attempts from same IP
        if ($this->action === 'failed_login') {
            $recentFailures = static::where('ip_address', $this->ip_address)
                ->where('action', 'failed_login')
                ->where('created_at', '>=', now()->subMinutes(15))
                ->count();
            
            return $recentFailures >= 5;
        }

        return false;
    }
}
