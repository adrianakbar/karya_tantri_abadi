<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockAdjusmentDetail extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'stock_adjustment_id', 'product_id', 'current_stock', 'actual_stock', 'difference', 'notes'
    ];
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
}
