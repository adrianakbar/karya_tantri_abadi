<?php

namespace App\Filament\Resources\ProductCategoryResource\Pages;

use App\Filament\Resources\ProductCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProductCategory extends CreateRecord
{    
    protected static string $resource = ProductCategoryResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterCreate(): void
    {
        $this->record->update([
            'code' => 'PC-' . str_pad($this->record->id, 5, '0', STR_PAD_LEFT),
        ]);
    }

    public function getTitle(): string
    {
        return 'Tambah Kategori Produk';
    }
}
