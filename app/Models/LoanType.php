<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanType extends Model
{
    use HasFactory;

    protected $fillable = [
        'cooperation_id',
        'name',
        'max_amount',
        'interest_rate',
        'max_tenor_months',
        'description',
        'is_active',
    ];

    protected $casts = [
        'max_amount' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function cooperation(): BelongsTo
    {
        return $this->belongsTo(Cooperation::class);
    }
}
