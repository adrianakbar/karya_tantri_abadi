<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Data')
                ->icon('heroicon-o-plus'),
            Action::make('printAllCards')
                ->label('Cetak Kartu Anggota')
                ->icon('heroicon-o-printer')
                ->color('info')
                ->form([
                    CheckboxList::make('selected_members')
                        ->label('Pilih Anggota untuk Dicetak')
                        ->options(function () {
                            return User::where('is_active', true)
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->toArray();
                        })
                        ->columns(2)
                        ->searchable()
                        ->required()
                        ->helperText('Pilih anggota yang ingin dicetak kartunya'),
                ])
                ->action(function (array $data) {
                    $memberIds = implode(',', $data['selected_members']);
                    return redirect()->route('member.cards.print', ['ids' => $memberIds]);
                })
                ->modalHeading('Cetak Kartu Anggota')
                ->modalSubmitActionLabel('Cetak Kartu')
                ->modalWidth('2xl'),
        ];
    }
}
