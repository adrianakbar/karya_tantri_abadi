<?php

namespace App\Filament\Resources\StockMovementLogResource\Pages;

use App\Filament\Resources\StockMovementLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStockMovementLogs extends ListRecords
{
    protected static string $resource = StockMovementLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('export')
                ->label('Export')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(fn() => null),
        ];
    }
}
