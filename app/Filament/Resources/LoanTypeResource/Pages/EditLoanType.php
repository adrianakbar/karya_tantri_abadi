<?php

namespace App\Filament\Resources\LoanTypeResource\Pages;

use App\Filament\Resources\LoanTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLoanType extends EditRecord
{
    protected static string $resource = LoanTypeResource::class;

    public function getTitle(): string
    {
        return 'Edit Jenis Pinjaman';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Hapus'),
        ];
    }
}
