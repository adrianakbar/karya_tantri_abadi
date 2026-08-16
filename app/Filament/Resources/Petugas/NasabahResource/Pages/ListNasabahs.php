<?php

namespace App\Filament\Resources\Petugas\NasabahResource\Pages;

use App\Filament\Resources\Petugas\NasabahResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

class ListNasabahs extends ListRecords
{
    protected static string $resource = NasabahResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Input Nasabah'),
        ];
    }

    public function getTitle(): string
    {
        return 'Data Nasabah';
    }
}
