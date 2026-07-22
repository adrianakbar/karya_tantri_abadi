<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use HasFactory;
    protected $fillable = [
        'cooperation_id',
        'product_category_id',
        'name',
        'code',
        'barcode',
        'description',
        'unit',
        'purchase_price',
        'selling_price',
        'min_stock',
        'current_stock',
        'image_url',
        'is_active'
    ];
    protected $casts = [
        'purchase_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'is_active' => 'boolean'
    ];
    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    public function cooperation(): BelongsTo
    {
        return $this->belongsTo(Cooperation::class, 'cooperation_id');
    }

    /**
     * Check if current stock is below minimum stock
     */
    public function isLowStock(): bool
    {
        return $this->current_stock < $this->min_stock;
    }

    /**
     * Get stock status with color coding
     */
    public function getStockStatusAttribute(): string
    {
        if ($this->current_stock <= 0) {
            return 'habis';
        } elseif ($this->isLowStock()) {
            return 'rendah';
        } else {
            return 'normal';
        }
    }

    /**
     * Mutator to handle incorrect 'stock' field usage
     * Redirects to current_stock if someone tries to set 'stock'
     */
    public function setStockAttribute($value): void
    {
        $this->attributes['current_stock'] = $value;
    }
}
