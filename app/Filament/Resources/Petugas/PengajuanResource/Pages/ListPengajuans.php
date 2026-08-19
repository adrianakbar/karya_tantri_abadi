<?php

namespace App\Filament\Resources\Petugas\PengajuanResource\Pages;

use App\Filament\Resources\Petugas\PengajuanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPengajuans extends ListRecords
{
    protected static string $resource = PengajuanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Ajukan Pinjaman'),
        ];
    }

    public function getTitle(): string
    {
        return 'Pengajuan Pinjaman';
    }
}
