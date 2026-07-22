<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CashFlow extends Model
{
    use HasFactory;

    const UPDATED_AT = null;

    protected $fillable = [
        'cooperation_id',
        'transaction_date',
        'reference_type',
        'reference_id',
        'type',
        'amount',
        'description',
        'category',
        'balance_before',
        'balance_after',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public function cooperation(): BelongsTo
    {
        return $this->belongsTo(Cooperation::class);
    }
}
