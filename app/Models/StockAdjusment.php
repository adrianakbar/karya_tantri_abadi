<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockAdjusment extends Model
{
    protected $fillable = [
        'cooperation_id', 'adjustment_number', 'adjustment_date', 'type', 'description',
        'processed_by', 'approved_by', 'status'
    ];
    protected $casts = ['adjustment_date' => 'date'];
    public function details(): HasMany { return $this->hasMany(StockAdjusmentDetail::class); }
}
