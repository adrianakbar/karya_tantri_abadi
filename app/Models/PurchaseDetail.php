<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseDetail extends Model
{
    public $timestamps = false;
    protected $fillable = ['purchase_id', 'product_id', 'quantity', 'unit_price', 'total_price', 'notes'];
    
    public function product(): BelongsTo 
    { 
        return $this->belongsTo(Product::class); 
    }
    
    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }
}
