<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterCreate(): void
    {
        $this->record->update([
            'code' => 'P-' . str_pad($this->record->id, 5, '0', STR_PAD_LEFT),
            'barcode' => 'P-' . str_pad($this->record->id, 5, '0', STR_PAD_LEFT),
        ]);
    }

    public function getTitle(): string
    {
        return 'Tambah Produk';
    }
    
}
