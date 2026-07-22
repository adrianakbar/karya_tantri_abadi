<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Loan extends Model
{
    use HasFactory;

    protected $fillable = [
        'cooperation_id',
        'user_id',
        'loan_type_id',
        'loan_number',
        'principal_amount',
        'admin_fee',
        'utj_fee',
        'installment_fee',
        'net_disbursement',
        'interest_rate',
        'tenor_months',
        'payment_frequency',
        'installment_count',
        'monthly_payment',
        'total_payment',
        'remaining_balance',
        'application_date',
        'approved_date',
        'disbursement_date',
        'due_date',
        'approved_by',
        'purpose',
        'status',
        'notes',
    ];

    protected $casts = [
        'principal_amount' => 'decimal:2',
        'admin_fee' => 'decimal:2',
        'utj_fee' => 'decimal:2',
        'installment_fee' => 'decimal:2',
        'net_disbursement' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'monthly_payment' => 'decimal:2',
        'total_payment' => 'decimal:2',
        'remaining_balance' => 'decimal:2',
        'application_date' => 'date',
        'approved_date' => 'date',
        'disbursement_date' => 'date',
        'due_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function loanType(): BelongsTo
    {
        return $this->belongsTo(LoanType::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(LoanPayment::class);
    }

    public function setAmountAttribute($value): void
    {
        $this->attributes['principal_amount'] = $value;
    }

    public function getAmountAttribute()
    {
        return $this->principal_amount;
    }

    public function setTermMonthsAttribute($value): void
    {
        $this->attributes['tenor_months'] = $value;
    }

    public function getTermMonthsAttribute()
    {
        return $this->tenor_months;
    }

    /** Label frekuensi angsuran */
    public function getFrequencyLabelAttribute(): string
    {
        return $this->payment_frequency === 'monthly' ? 'Bulanan' : 'Mingguan';
    }
}
