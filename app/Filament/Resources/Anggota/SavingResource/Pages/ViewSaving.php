<?php

namespace App\Filament\Resources\Anggota\SavingResource\Pages;

use App\Filament\Resources\Anggota\SavingResource;
use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewSaving extends ViewRecord
{
    protected static string $resource = SavingResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Informasi Transaksi Simpanan')
                    ->schema([
                        Infolists\Components\TextEntry::make('transaction_number')
                            ->label('No. Transaksi'),
                        Infolists\Components\TextEntry::make('savingsType.name')
                            ->label('Jenis Simpanan'),
                        Infolists\Components\TextEntry::make('transaction_date')
                            ->label('Tanggal Transaksi')
                            ->date('d F Y'),
                        Infolists\Components\TextEntry::make('amount')
                            ->label('Jumlah')
                            ->money('IDR'),
                        Infolists\Components\TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'completed' => 'success',
                                'pending' => 'warning', 
                                'cancelled' => 'danger',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'completed' => 'Selesai',
                                'pending' => 'Pending',
                                'cancelled' => 'Dibatalkan',
                                default => $state,
                            }),
                        Infolists\Components\TextEntry::make('notes')
                            ->label('Keterangan')
                            ->default('Tidak ada keterangan'),
                    ])
                    ->columns(2),
                Infolists\Components\Section::make('Informasi Tambahan')
                    ->schema([
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Dibuat Pada')
                            ->dateTime('d F Y H:i'),
                        Infolists\Components\TextEntry::make('updated_at')
                            ->label('Diperbarui Pada')
                            ->dateTime('d F Y H:i'),
                    ])
                    ->columns(2),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            // Remove edit and delete actions for anggota
        ];
    }
}
