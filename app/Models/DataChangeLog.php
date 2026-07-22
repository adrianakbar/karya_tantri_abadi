<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class DataChangeLog extends Model
{
    use HasFactory;

    const CREATED_AT = 'changed_at';
    const UPDATED_AT = null;

    protected $fillable = [
        'cooperation_id',
        'user_id',
        'table_name',
        'record_id',
        'action',
        'old_values',
        'new_values',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'changed_at' => 'datetime',
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
        return $query->whereBetween('changed_at', [$startDate, $endDate]);
    }

    /**
     * Scope to filter by table
     */
    public function scopeByTable(Builder $query, string $tableName): Builder
    {
        return $query->where('table_name', $tableName);
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
        return match($this->action) {
            'create' => 'Created',
            'update' => 'Updated',
            'delete' => 'Deleted',
            default => ucwords(str_replace('_', ' ', $this->action))
        };
    }

    /**
     * Get formatted table name
     */
    public function getFormattedTableNameAttribute(): string
    {
        return ucwords(str_replace('_', ' ', $this->table_name));
    }

    /**
     * Get changes summary
     */
    public function getChangesSummaryAttribute(): string
    {
        if ($this->action === 'create') {
            return 'Record baru dibuat';
        } elseif ($this->action === 'delete') {
            return 'Record dihapus';
        } elseif ($this->action === 'update' && $this->new_values) {
            $newValues = is_array($this->new_values) ? $this->new_values : json_decode($this->new_values, true);
            if (is_array($newValues)) {
                $changedFields = array_keys($newValues);
                return 'Perubahan: ' . implode(', ', array_map(function($field) {
                    return ucwords(str_replace('_', ' ', $field));
                }, $changedFields));
            }
        }
        return 'Tidak ada perubahan';
    }

    /**
     * Get changed fields count
     */
    public function getChangedFieldsCountAttribute(): int
    {
        if ($this->action === 'update' && $this->new_values) {
            return count($this->new_values);
        }
        return 0;
    }

    /**
     * Check if field was changed
     */
    public function wasFieldChanged(string $field): bool
    {
        return isset($this->new_values[$field]);
    }

    /**
     * Get old value for field
     */
    public function getOldValue(string $field)
    {
        return $this->old_values[$field] ?? null;
    }

    /**
     * Get new value for field
     */
    public function getNewValue(string $field)
    {
        return $this->new_values[$field] ?? null;
    }

    /**
     * Get all changed fields with old and new values
     */
    public function getChangedFields(): array
    {
        if ($this->action !== 'update' || !$this->new_values) {
            return [];
        }

        $changes = [];
        foreach ($this->new_values as $field => $newValue) {
            $changes[$field] = [
                'old' => $this->old_values[$field] ?? null,
                'new' => $newValue,
            ];
        }

        return $changes;
    }
}
