<?php

namespace App\Filament\Resources\SystemSettingResource\Pages;

use App\Filament\Resources\SystemSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSystemSettings extends ListRecords
{
    protected static string $resource = SystemSettingResource::class;

    public function getTitle(): string
    {
        return 'Pengaturan Sistem';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Data')
                ->icon('heroicon-o-plus'),
        ];
    }
}
