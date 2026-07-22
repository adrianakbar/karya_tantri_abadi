<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
         return [
            Actions\DeleteAction::make()
                ->label('Hapus Data')
                ->icon('heroicon-o-trash')
                ->color('danger'),

            Action::make('printCard')
                ->label('Cetak Kartu Anggota')
                ->icon('heroicon-o-printer')
                ->color('success')
                ->url(fn (): string => route('member.card.print', $this->record))
                ->openUrlInNewTab(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
