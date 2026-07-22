<?php

namespace App\Filament\Resources\LoanTypeResource\Pages;

use App\Filament\Resources\LoanTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateLoanType extends CreateRecord
{
    protected static string $resource = LoanTypeResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    public function getTitle(): string
    {
        return 'Tambah Jenis Pinjaman';
    }

}
