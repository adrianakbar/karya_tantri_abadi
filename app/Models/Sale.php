<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    use HasFactory;
    protected $fillable = [
        'cooperation_id', 'customer_id', 'sale_number', 'sale_date',
        'subtotal', 'discount_amount', 'tax_amount', 'total_amount', 'payment_method',
        'processed_by', 'status', 'notes'
    ];
    protected $casts = ['sale_date' => 'date'];

    /**
     * Check if sale is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Get status label for display
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Dalam Proses',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            default => $this->status,
        };
    }

    public function details(): HasMany
    {
        return $this->hasMany(SaleDetail::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function cooperation(): BelongsTo
    {
        return $this->belongsTo(Cooperation::class, 'cooperation_id');
    }
}
