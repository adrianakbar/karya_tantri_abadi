<?php

namespace App\Filament\Resources\StockMovementLogResource\Pages;

use App\Filament\Resources\StockMovementLogResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStockMovementLog extends EditRecord
{
    protected static string $resource = StockMovementLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
             Actions\DeleteAction::make()
                ->label('Hapus Data')
                ->icon('heroicon-o-trash')
                ->color('danger'),
        ];
    }
}
