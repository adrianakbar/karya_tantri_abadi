<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class ActivityLog extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'cooperation_id',
        'user_id',
        'action',
        'module',
        'description',
        'ip_address',
        'user_agent',
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
     * Scope to filter by module
     */
    public function scopeByModule(Builder $query, string $module): Builder
    {
        return $query->where('module', $module);
    }

    /**
     * Scope to filter by action
     */
    public function scopeByAction(Builder $query, string $action): Builder
    {
        return $query->where('action', $action);
    }

    /**
     * Get formatted action name
     */
    public function getFormattedActionAttribute(): string
    {
        return ucwords(str_replace('_', ' ', $this->action));
    }

    /**
     * Get formatted module name
     */
    public function getFormattedModuleAttribute(): string
    {
        return ucwords(str_replace('_', ' ', $this->module));
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
}
