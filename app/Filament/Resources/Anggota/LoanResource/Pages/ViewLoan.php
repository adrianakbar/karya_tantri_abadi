<?php

namespace App\Filament\Resources\Anggota\LoanResource\Pages;

use App\Filament\Resources\Anggota\LoanResource;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewLoan extends ViewRecord
{
    protected static string $resource = LoanResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Informasi Pinjaman')
                    ->schema([
                        Infolists\Components\TextEntry::make('loan_number')
                            ->label('No. Pinjaman'),
                        Infolists\Components\TextEntry::make('application_date')
                            ->label('Tanggal Pengajuan')
                            ->date('d F Y'),
                        Infolists\Components\TextEntry::make('disbursement_date')
                            ->label('Tanggal Pencairan')
                            ->date('d F Y')
                            ->placeholder('-'),
                        Infolists\Components\TextEntry::make('net_disbursement')
                            ->label('Cair Bersih')
                            ->money('IDR')
                            ->helperText('Dana yang diterima anggota'),
                        Infolists\Components\TextEntry::make('tenor_months')
                            ->label('Tenor')
                            ->suffix(' bulan'),
                        Infolists\Components\TextEntry::make('payment_frequency')
                            ->label('Frekuensi Angsuran')
                            ->formatStateUsing(fn ($state) => $state === 'monthly' ? 'Bulanan' : 'Mingguan'),
                        Infolists\Components\TextEntry::make('monthly_payment')
                            ->label('Angsuran per Periode')
                            ->money('IDR'),
                        Infolists\Components\TextEntry::make('installment_count')
                            ->label('Jumlah Angsuran')
                            ->suffix(' kali'),
                        Infolists\Components\TextEntry::make('remaining_balance')
                            ->label('Sisa Hutang')
                            ->money('IDR'),
                        Infolists\Components\TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'pending' => 'Menunggu',
                                'approved' => 'Disetujui',
                                'rejected' => 'Ditolak',
                                'disbursed' => 'Dicairkan',
                                'active' => 'Aktif',
                                'completed' => 'Lunas',
                                'overdue' => 'Jatuh Tempo',
                                default => $state,
                            }),
                        Infolists\Components\TextEntry::make('purpose')
                            ->label('Tujuan')
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public function getTitle(): string
    {
        return 'Detail Pinjaman';
    }
}
