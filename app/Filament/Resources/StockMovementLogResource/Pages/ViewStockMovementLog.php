<?php

namespace App\Filament\Resources\StockMovementLogResource\Pages;

use App\Filament\Resources\StockMovementLogResource;
use App\Filament\Resources\ProductResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions;
use Filament\Support\Enums\ActionSize;

class ViewStockMovementLog extends ViewRecord
{
    protected static string $resource = StockMovementLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('view_product')
                ->label('Lihat Produk')
                ->icon('heroicon-o-eye')
                ->color('primary')
                ->url(fn() => ProductResource::getUrl('edit', ['record' => $this->record->product_id]))
                ->openUrlInNewTab(),

            Actions\Action::make('back')
                ->label('Batal')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->url(fn() => StockMovementLogResource::getUrl('index')),
        ];
    }

    public function getTitle(): string
    {
        return 'Detail Riwayat Stok';
    }

    protected function getViewData(): array
    {
        return [
            'record' => $this->record,
            'product' => $this->record->product,
        ];
    }
}
