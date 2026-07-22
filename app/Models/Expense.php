<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'cooperation_id',
        'expense_category_id',
        'expense_number',
        'amount',
        'expense_date',
        'receipt_number',
        'recipient',
        'processed_by',
        'approved_by',
        'status',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date',
    ];

    protected static function booted()
    {
        static::creating(function ($expense) {
            if (empty($expense->expense_number)) {
                $expense->expense_number = 'EXP-' . Str::uuid();
            }
        });
    }

    public function cooperation(): BelongsTo
    {
        return $this->belongsTo(Cooperation::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
