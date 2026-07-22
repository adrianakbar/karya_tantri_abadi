<?php

namespace App\Filament\Resources\AuthLogResource\Pages;

use App\Filament\Resources\AuthLogResource;
use Filament\Resources\Pages\ListRecords;

class ListAuthLogs extends ListRecords
{
    protected static string $resource = AuthLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No create action for security
        ];
    }
}
