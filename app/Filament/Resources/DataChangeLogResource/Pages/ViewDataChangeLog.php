<?php

namespace App\Filament\Resources\DataChangeLogResource\Pages;

use App\Filament\Resources\DataChangeLogResource;
use Filament\Resources\Pages\ViewRecord;

class ViewDataChangeLog extends ViewRecord
{
    protected static string $resource = DataChangeLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No actions for security
        ];
    }
}
