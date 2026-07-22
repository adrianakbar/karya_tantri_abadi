<?php

namespace App\Filament\Resources\UserRoleResource\Pages;

use App\Filament\Resources\UserRoleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUserRole extends EditRecord
{
    protected static string $resource = UserRoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Hapus Data')
                ->icon('heroicon-o-trash')
                ->color('danger'),
        ];
    }

    public function getTitle(): string
    {
        return 'Edit Peran Pengguna';
    }
}
