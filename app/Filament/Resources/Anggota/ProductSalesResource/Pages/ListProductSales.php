<?php

namespace App\Filament\Resources\Anggota\ProductSalesResource\Pages;

use App\Filament\Resources\Anggota\ProductSalesResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProductSales extends ListRecords
{
    protected static string $resource = ProductSalesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Remove create action for anggota
        ];
    }
    public function getTitle(): string
    {
        return 'Penjualan Produk';
    }
}
