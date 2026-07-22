<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShuMemberShare extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'shu_distribution_id',
        'user_id',
        'savings_contribution',
        'transaction_contribution',
        'shu_amount',
        'status',
        'paid_at',
    ];

    protected $casts = [
        'savings_contribution' => 'decimal:2',
        'transaction_contribution' => 'decimal:2',
        'shu_amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function distribution(): BelongsTo
    {
        return $this->belongsTo(ShuDistribution::class, 'shu_distribution_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
