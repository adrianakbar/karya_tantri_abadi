<?php

namespace App\Filament\Resources\SavingsTypeResource\Pages;

use App\Filament\Resources\SavingsTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSavingsTypes extends ListRecords
{
    protected static string $resource = SavingsTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Data')
                ->icon('heroicon-o-plus'),
        ];
    }

    public function getTitle(): string
    {
        return 'Jenis Simpanan';
    }
}
