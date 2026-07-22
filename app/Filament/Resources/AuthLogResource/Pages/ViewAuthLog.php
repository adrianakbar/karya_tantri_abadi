<?php

namespace App\Filament\Resources\AuthLogResource\Pages;

use App\Filament\Resources\AuthLogResource;
use Filament\Resources\Pages\ViewRecord;

class ViewAuthLog extends ViewRecord
{
    protected static string $resource = AuthLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No actions for security
        ];
    }
}
