<?php

namespace App\Filament\Resources\Anggota\ProductSalesResource\Pages;

use App\Filament\Resources\Anggota\ProductSalesResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewProductSales extends ViewRecord
{
    protected static string $resource = ProductSalesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Remove edit and delete actions for anggota
        ];
    }

    public function getTitle(): string
    {
        return 'Detail Penjualan Produk';
    }
}
