<?php

namespace App\Filament\Resources\SavingsTypeResource\Pages;

use App\Filament\Resources\SavingsTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateSavingsType extends CreateRecord
{
    protected static string $resource = SavingsTypeResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    public function getTitle(): string
    {
        return 'Tambah Jenis Tabungan';
    }
}
