<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ExpenseCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'cooperation_id',
        'name',
        'code',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted()
    {
        static::creating(function ($expenseCategory) {
            if (empty($expenseCategory->code)) {
                $expenseCategory->code = 'EC-' . Str::uuid()->toString();
            }
        });
    }

    public static function getDefaultPurchaseCategory($cooperationId = null): self
    {
        return static::firstOrCreate(
            ['name' => 'Pembelian Barang'],
            [
                'cooperation_id' => $cooperationId ?? 1,
                'description'    => 'Pengeluaran untuk pembelian barang',
                'is_active'      => true,
            ]
        );
    }

    /**
     * Relasi: Kategori ini milik satu Organisasi (Cooperation).
     */
    public function cooperation(): BelongsTo
    {
        return $this->belongsTo(Cooperation::class);
    }

    /**
     * Relasi: Kategori ini memiliki banyak Pengeluaran (Expense).
     */
    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }
}
