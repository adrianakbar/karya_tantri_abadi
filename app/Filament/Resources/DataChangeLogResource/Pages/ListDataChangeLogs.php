<?php

namespace App\Filament\Resources\DataChangeLogResource\Pages;

use App\Filament\Resources\DataChangeLogResource;
use Filament\Resources\Pages\ListRecords;

class ListDataChangeLogs extends ListRecords
{
    protected static string $resource = DataChangeLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No create action for security
        ];
    }
}
