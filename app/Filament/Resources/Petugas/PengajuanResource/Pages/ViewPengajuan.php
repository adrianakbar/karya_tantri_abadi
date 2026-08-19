<?php

namespace App\Filament\Resources\Petugas\PengajuanResource\Pages;

use App\Filament\Resources\Petugas\PengajuanResource;
use App\Services\LoanCalculator;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewPengajuan extends ViewRecord
{
    protected static string $resource = PengajuanResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Data Nasabah')->schema([
                Infolists\Components\TextEntry::make('applicant_name')->label('Nama Nasabah'),
                Infolists\Components\ImageEntry::make('ktp_photo')->label('Foto KTP')
                    ->height(220)->columnSpanFull(),
            ])->columns(2),

            Infolists\Components\Section::make('Detail Pinjaman')->schema([
                Infolists\Components\TextEntry::make('loan_number')->label('No. Pengajuan'),
                Infolists\Components\TextEntry::make('principal_amount')->label('Nominal')->money('IDR'),
                Infolists\Components\TextEntry::make('net_disbursement')->label('Cair Bersih')->money('IDR'),
                Infolists\Components\TextEntry::make('tenor_months')->label('Tenor')
                    ->formatStateUsing(fn ($state, $record) => $state . ' bln / ' . ($record->installment_count ?: '-') . 'x'),
                Infolists\Components\TextEntry::make('payment_frequency')->label('Frekuensi')
                    ->formatStateUsing(fn (?string $state) => $state === 'monthly' ? 'Bulanan' : 'Mingguan'),
                Infolists\Components\TextEntry::make('application_date')->label('Tgl Pengajuan')->date('d F Y'),
                Infolists\Components\TextEntry::make('purpose')->label('Tujuan')->placeholder('-')->columnSpanFull(),
                Infolists\Components\TextEntry::make('status')->label('Status')->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Menunggu Koreksi Admin',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        'disbursed' => 'Dicairkan',
                        'active' => 'Aktif',
                        'completed' => 'Lunas',
                        'overdue' => 'Jatuh Tempo',
                        default => $state,
                    }),
            ])->columns(2),
        ]);
    }

    public function getTitle(): string
    {
        return 'Detail Pengajuan';
    }
}
