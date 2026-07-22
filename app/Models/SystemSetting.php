<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SystemSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'cooperation_id',
        'category',
        'key',
        'value',
        'type',
        'description',
        'is_system',
        'updated_by',
    ];

    protected $casts = [
        'is_system' => 'boolean',
        'value' => 'json'
    ];

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
