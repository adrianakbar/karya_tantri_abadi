<?php

namespace App\Filament\Resources\LoanResource\Pages;

use App\Filament\Resources\LoanResource;
use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;

class ViewLoan extends ViewRecord
{
    protected static string $resource = LoanResource::class;

    public function getTitle(): string
    {
        return 'Detail Pinjaman';
    }

    protected function getHeaderActions(): array
    {
        $actions = [];

        if (LoanResource::canEdit($this->record)) {
            $actions[] = Actions\EditAction::make()->label('Edit Pengajuan');
        }

        return $actions;
    }

    public function infolist(Infolist $infolist): Infolist
    {
        $isAdmin = Auth::user()?->hasRole('admin');

        return $infolist->schema([
            Infolists\Components\Section::make('Informasi Pinjaman')
                ->schema([
                    Infolists\Components\TextEntry::make('loan_number')->label('No. Pinjaman'),
                    Infolists\Components\TextEntry::make('user.name')->label('Peminjam'),
                    Infolists\Components\TextEntry::make('application_date')->label('Tgl Pengajuan')->date('d M Y'),
                    Infolists\Components\TextEntry::make('disbursement_date')->label('Tgl Pencairan')->date('d M Y')->placeholder('-'),
                    Infolists\Components\TextEntry::make('principal_amount')->label('Nominal')->money('IDR'),
                    Infolists\Components\TextEntry::make('net_disbursement')->label('Cair Bersih')->money('IDR'),
                    Infolists\Components\TextEntry::make('tenor_months')->label('Tenor')->suffix(' bulan'),
                    Infolists\Components\TextEntry::make('payment_frequency')
                        ->label('Frekuensi')
                        ->formatStateUsing(fn ($state) => $state === 'monthly' ? 'Bulanan' : 'Mingguan'),
                    Infolists\Components\TextEntry::make('installment_count')->label('Jumlah Angsuran')->suffix(' kali'),
                    Infolists\Components\TextEntry::make('monthly_payment')->label('Angsuran per Periode')->money('IDR'),
                    Infolists\Components\TextEntry::make('total_payment')->label('Total Dilunasi')->money('IDR'),
                    Infolists\Components\TextEntry::make('remaining_balance')->label('Sisa Hutang')->money('IDR'),
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
                    Infolists\Components\TextEntry::make('purpose')->label('Tujuan')->columnSpanFull(),
                ])->columns(3),

            // Rincian biaya hanya admin
            Infolists\Components\Section::make('Rincian Biaya')
                ->description('Angsuran 11% · Admin 5% · UTJ 22% · Cair bersih 73%')
                ->visible($isAdmin)
                ->schema([
                    Infolists\Components\TextEntry::make('installment_fee')->label('Biaya Angsuran (11%)')->money('IDR'),
                    Infolists\Components\TextEntry::make('admin_fee')->label('Admin (5%)')->money('IDR'),
                    Infolists\Components\TextEntry::make('utj_fee')->label('UTJ (22%)')->money('IDR'),
                    Infolists\Components\TextEntry::make('net_disbursement')->label('Cair Bersih (73%)')->money('IDR'),
                ])->columns(4),
        ]);
    }

    public function getRelationManagers(): array
    {
        // Jadwal cicilan tampil setelah pinjaman disetujui/dicairkan
        if (in_array($this->record->status, ['pending', 'rejected', 'approved'], true)) {
            // approved belum cair → belum ada jadwal; pending/rejected juga kosong
            if ($this->record->status !== 'approved' && $this->record->payments()->exists()) {
                return parent::getRelationManagers();
            }
            if (in_array($this->record->status, ['pending', 'rejected'], true)) {
                return [];
            }
            // approved tanpa payments: tetap tampilkan kosong agar jelas belum cair
            return parent::getRelationManagers();
        }

        return parent::getRelationManagers();
    }
}
