<?php

namespace App\Filament\Resources\ProductSalesResource\Pages;

use App\Filament\Resources\ProductSalesResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProductSales extends EditRecord
{
    protected static string $resource = ProductSalesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Hapus Data')
                ->icon('heroicon-o-trash')
                ->color('danger'),

            Actions\Action::make('printReceipt')
                ->label('Cetak Struk')
                ->icon('heroicon-o-printer')
                ->color('success')
                ->url(fn (): string => route('sales.print', $this->record))
                ->openUrlInNewTab(),
        ];
    }

    public function getTitle(): string
    {
        return 'Edit Penjualan Produk';
    }
}
