<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'cooperation_id', 'loan_id', 'payment_number', 'installment_number', 'due_date',
        'payment_date', 'principal_amount', 'interest_amount', 'total_amount',
        'paid_amount', 'penalty_amount', 'processed_by', 'status', 'notes',
    ];

    protected $casts = [
        'due_date' => 'date', 'payment_date' => 'date', 'principal_amount' => 'decimal:2',
        'interest_amount' => 'decimal:2', 'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2', 'penalty_amount' => 'decimal:2',
    ];

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function cooperation(): BelongsTo
    {
        return $this->belongsTo(Cooperation::class);
    }

    /**
     * Mutator to handle incorrect 'amount' field usage.
     * Redirects to paid_amount and total_amount if someone tries to get/set 'amount'.
     */
    public function setAmountAttribute($value)
    {
        $this->attributes['paid_amount'] = $value;
        $this->attributes['total_amount'] = $value;
    }

    public function getAmountAttribute()
    {
        return $this->paid_amount;
    }
}
