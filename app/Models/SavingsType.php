<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavingsType extends Model
{
    use HasFactory;

    protected $fillable = [
        'cooperation_id',
        'name',
        'code',
        'amount',
        'is_mandatory',
        'description',
        'is_active',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_mandatory' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function cooperation(): BelongsTo
    {
        return $this->belongsTo(Cooperation::class);
    }
}
