<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductCategory extends Model
{
    use HasFactory;
    protected $fillable = ['cooperation_id', 'name', 'code', 'description', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];

    public function cooperation(): BelongsTo {
        return $this->belongsTo(Cooperation::class);
    }

    public function products() {
        return $this->hasMany(Product::class, 'product_category_id');
    }
}
