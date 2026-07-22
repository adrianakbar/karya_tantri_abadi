<?php

namespace App\Filament\Resources\LoanResource\RelationManagers;

use App\Models\LoanPayment;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

/**
 * Alur cicilan:
 * petugas kumpulkan dari anggota → serahkan ke admin → admin input di sistem.
 * Kasir hanya melihat daftar cicilan (read-only).
 */
class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';
    protected static ?string $title = 'Jadwal Cicilan';

    public function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public function table(Table $table): Table
    {
        $isAdmin = Auth::user()?->hasRole('admin');

        return $table
            ->recordTitleAttribute('payment_number')
            ->columns([
                TextColumn::make('installment_number')->label('Cicilan Ke-')->sortable(),
                TextColumn::make('due_date')->label('Jatuh Tempo')->date('d M Y')->sortable(),
                TextColumn::make('total_amount')->label('Tagihan')->money('IDR'),
                TextColumn::make('paid_amount')->label('Dibayar')->money('IDR'),
                TextColumn::make('payment_date')->label('Tgl Bayar')->date('d M Y')->placeholder('-'),
                TextColumn::make('processor.name')->label('Dicatat Oleh')->placeholder('-')->toggleable(),
                BadgeColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Belum Bayar',
                        'paid' => 'Lunas',
                        'partial' => 'Sebagian',
                        'overdue' => 'Terlambat',
                        default => $state,
                    })
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'paid',
                        'danger' => 'overdue',
                        'primary' => 'partial',
                    ]),
                TextColumn::make('notes')->label('Catatan')->limit(30)->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('installment_number')
            ->filters([])
            ->headerActions([])
            ->actions([
                // Hanya admin yang boleh input pembayaran cicilan
                Tables\Actions\Action::make('pay')
                    ->label('Catat Bayar')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(function (LoanPayment $record) use ($isAdmin): bool {
                        if (!$isAdmin) {
                            return false;
                        }

                        return in_array($record->status, ['pending', 'partial', 'overdue'], true);
                    })
                    ->modalHeading('Catat Pembayaran Cicilan')
                    ->modalDescription('Uang dari petugas diserahkan ke admin, lalu dicatat di sini.')
                    ->form([
                        TextInput::make('paid_amount')
                            ->label('Jumlah Pembayaran')
                            ->numeric()
                            ->prefix('Rp')
                            ->required()
                            ->default(fn (LoanPayment $record) => max(0, $record->total_amount - $record->paid_amount)),
                        DatePicker::make('payment_date')
                            ->label('Tanggal Bayar')
                            ->default(now())
                            ->required(),
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->placeholder('Contoh: diterima dari petugas kelompok A')
                            ->rows(2),
                    ])
                    ->action(function (LoanPayment $record, array $data) {
                        $this->processPayment($record, $data);
                    }),
            ])
            ->bulkActions([])
            ->emptyStateHeading('Belum ada jadwal cicilan')
            ->emptyStateDescription('Jadwal muncul setelah pinjaman dicairkan oleh kasir.');
    }

    protected function processPayment(LoanPayment $installment, array $data): void
    {
        if (!Auth::user()?->hasRole('admin')) {
            Notification::make()
                ->title('Hanya admin yang boleh mencatat cicilan')
                ->danger()
                ->send();
            return;
        }

        $loan = $installment->loan;
        $amountToPay = floatval($data['paid_amount']);

        if ($amountToPay <= 0) {
            Notification::make()->title('Jumlah pembayaran tidak valid')->danger()->send();
            return;
        }

        $installment->paid_amount = floatval($installment->paid_amount) + $amountToPay;
        $installment->payment_date = $data['payment_date'];
        $installment->processed_by = Auth::id();

        if (!empty($data['notes'])) {
            $note = trim((string) $data['notes']);
            $installment->notes = trim(($installment->notes ? $installment->notes . "\n" : '') . $note);
        }

        if ($installment->paid_amount >= $installment->total_amount) {
            $installment->status = 'paid';
            $installment->paid_amount = $installment->total_amount;
        } else {
            $installment->status = 'partial';
        }
        $installment->save();

        $loan->remaining_balance = max(0, floatval($loan->remaining_balance) - $amountToPay);

        // Setelah ada pembayaran, pinjaman aktif
        if (in_array($loan->status, ['disbursed', 'approved'], true)) {
            $loan->status = 'active';
        }

        if ($loan->remaining_balance <= 0) {
            $loan->remaining_balance = 0;
            $loan->status = 'completed';
        }

        $loan->save();

        Notification::make()
            ->title('Cicilan berhasil dicatat')
            ->body('Sisa hutang: Rp ' . number_format($loan->remaining_balance, 0, ',', '.'))
            ->success()
            ->send();
    }
}
