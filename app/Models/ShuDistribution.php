<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShuDistribution extends Model
{
    use HasFactory;

    protected $fillable = [
        'cooperation_id',
        'year',
        'total_revenue',
        'total_expenses',
        'total_shu',
        'distribution_date',
        'status',
        'calculated_by',
        'distributed_by',
        'notes',
    ];

    protected $casts = [
        'total_revenue' => 'decimal:2',
        'total_expenses' => 'decimal:2',
        'total_shu' => 'decimal:2',
        'distribution_date' => 'date',
    ];

    public function calculator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'calculated_by');
    }

    public function distributor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'distributed_by');
    }

    public function memberShares(): HasMany
    {
        return $this->hasMany(ShuMemberShare::class);
    }
}
