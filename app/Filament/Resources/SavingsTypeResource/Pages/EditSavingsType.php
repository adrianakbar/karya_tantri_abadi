<?php

namespace App\Filament\Resources\SavingsTypeResource\Pages;

use App\Filament\Resources\SavingsTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSavingsType extends EditRecord
{
    protected static string $resource = SavingsTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Hapus'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    public function getTitle(): string
    {
        return 'Edit Jenis Tabungan';
    }
}
