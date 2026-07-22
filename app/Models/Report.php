<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    use HasFactory;

    const UPDATED_AT = null;

    protected $fillable = [
        'cooperation_id',
        'name',
        'type',
        'period_start',
        'period_end',
        'generated_by',
        'file_path',
        'parameters',
        'status',
        'generated_at',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'generated_at' => 'datetime',
        'parameters' => 'array',
    ];

    public function generator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function cooperation(): BelongsTo
    {
        return $this->belongsTo(Cooperation::class);
    }
}
