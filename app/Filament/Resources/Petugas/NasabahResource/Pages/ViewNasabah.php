<?php

namespace App\Filament\Resources\Petugas\NasabahResource\Pages;

use App\Filament\Resources\Petugas\NasabahResource;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewNasabah extends ViewRecord
{
    protected static string $resource = NasabahResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Data Nasabah')
                ->schema([
                    Infolists\Components\TextEntry::make('member_number')->label('No. Anggota'),
                    Infolists\Components\TextEntry::make('name')->label('Nama Lengkap'),
                    Infolists\Components\TextEntry::make('phone')->label('No. Telepon'),
                    Infolists\Components\TextEntry::make('email')->label('Email')->placeholder('-'),
                    Infolists\Components\TextEntry::make('birth_date')->label('Tanggal Lahir')->date('d F Y'),
                    Infolists\Components\TextEntry::make('gender')
                        ->label('Jenis Kelamin')
                        ->formatStateUsing(fn (string $state): string => $state === 'male' ? 'Laki-laki' : 'Perempuan'),
                    Infolists\Components\TextEntry::make('job')->label('Pekerjaan')->placeholder('-'),
                    Infolists\Components\TextEntry::make('address')->label('Alamat')->columnSpanFull(),
                    Infolists\Components\TextEntry::make('join_date')->label('Tanggal Bergabung')->date('d F Y'),
                ])->columns(2),
        ]);
    }

    public function getTitle(): string
    {
        return 'Detail Nasabah';
    }
}
