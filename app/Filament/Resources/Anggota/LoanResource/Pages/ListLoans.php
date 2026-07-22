<?php

namespace App\Filament\Resources\Anggota\LoanResource\Pages;

use App\Filament\Resources\Anggota\LoanResource;
use Filament\Resources\Pages\ListRecords;

class ListLoans extends ListRecords
{
    protected static string $resource = LoanResource::class;

    protected function getHeaderActions(): array
    {
        // Anggota hanya lihat — pengajuan diinput admin
        return [];
    }

    public function getTitle(): string
    {
        return 'Daftar Pinjaman';
    }
}
