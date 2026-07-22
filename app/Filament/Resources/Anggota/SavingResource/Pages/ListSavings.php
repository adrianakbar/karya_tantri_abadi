<?php

namespace App\Filament\Resources\Anggota\SavingResource\Pages;

use App\Filament\Resources\Anggota\SavingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSavings extends ListRecords
{
    protected static string $resource = SavingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Remove create action for anggota
        ];
    }

    public function getTitle(): string
    {
        return 'Simpanan';
    }
}
