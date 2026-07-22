<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavingsTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'cooperation_id',
        'user_id',
        'savings_type_id',
        'transaction_number',
        'amount',
        'transaction_date',
        'notes',
        'receipt_number',
        'processed_by',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function savingsType(): BelongsTo
    {
        return $this->belongsTo(SavingsType::class);
    }

    public function cooperation(): BelongsTo
    {
        return $this->belongsTo(Cooperation::class);
    }
}
