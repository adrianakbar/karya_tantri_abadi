<?php

namespace App\Filament\Resources\PermissionResource\Pages;

use App\Filament\Resources\PermissionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPermission extends EditRecord
{
    protected static string $resource = PermissionResource::class;

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
        return 'Edit Hak Akses';
    }
}
