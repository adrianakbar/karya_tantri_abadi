<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Notification extends Model
{
    use HasFactory;

    const UPDATED_AT = null;

    protected $fillable = [
        'cooperation_id',
        'user_id',
        'title',
        'message',
        'type',
        'category',
        'reference_id',
        'reference_table',
        'is_read',
        'sent_at',
        'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'sent_at' => 'datetime',
        'read_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cooperation(): BelongsTo
    {
        return $this->belongsTo(Cooperation::class);
    }

    public function referenceable(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'reference_table', 'reference_id');
    }
}
