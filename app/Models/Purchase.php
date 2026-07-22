<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Purchase extends Model
{
    use HasFactory;
    protected $fillable = [
        'cooperation_id', 'supplier_id', 'purchase_number', 'invoice_number', 'purchase_date',
        'total_amount', 'tax_amount', 'discount_amount', 'grand_total', 'processed_by',
        'status', 'notes'
    ];
    protected $casts = ['purchase_date' => 'date'];
    
    /**
     * Check if purchase is received (completed)
     */
    public function isReceived(): bool
    {
        return $this->status === 'received';
    }
    
    /**
     * Get status label for display
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Dalam Proses',
            'received' => 'Diterima',
            'cancelled' => 'Dibatalkan',
            default => $this->status,
        };
    }
    
    public function details(): HasMany 
    { 
        return $this->hasMany(PurchaseDetail::class); 
    }
    
    public function supplier(): BelongsTo 
    { 
        return $this->belongsTo(Supplier::class); 
    }
    
    public function cooperation(): BelongsTo 
    { 
        return $this->belongsTo(Cooperation::class, 'cooperation_id'); 
    }
}
