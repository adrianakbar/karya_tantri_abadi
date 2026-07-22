<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovementLog extends Model
{
    use HasFactory;

    const UPDATED_AT = null;

    protected $fillable = [
        'cooperation_id',
        'product_id',
        'reference_type',
        'reference_id',
        'type',
        'quantity',
        'stock_before',
        'stock_after',
        'notes',
        'created_by',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
