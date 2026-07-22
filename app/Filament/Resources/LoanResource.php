<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LoanResource\Pages;
use App\Filament\Resources\LoanResource\RelationManagers\PaymentsRelationManager;
use App\Models\Loan;
use App\Models\LoanType;
use App\Services\LoanCalculator;
use Closure;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Number as SupportNumber;

class LoanResource extends Resource
{
    protected static ?string $model = Loan::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $pluralModelLabel = 'Daftar Pinjaman';
    protected static ?string $navigationLabel = 'Daftar Pinjaman';
    protected static ?string $navigationGroup = 'Transaksi';

    public static function form(Form $form): Form
    {
        $kelompokTypeId = LoanType::where('name', 'like', '%kelompok%')
            ->orWhere('name', 'Kelompok')
            ->value('id');

        return $form->schema([
            Section::make('Informasi Peminjam')->schema([
                Grid::make(2)->schema([
                    TextInput::make('loan_number')->label('Nomor Pinjaman')
                        ->default('Akan dibuat otomatis')->readonly()->disabled()->dehydrated(false),
                    Select::make('user_id')->label('Nama Peminjam')
                        ->relationship('user', 'name')->searchable()->preload()->required(),
                ]),
                Hidden::make('loan_type_id')->default($kelompokTypeId),
            ]),

            Section::make('Detail Pinjaman Kelompok')->schema([
                Grid::make(2)->schema([
                    TextInput::make('principal_amount')->label('Nominal Pinjaman')
                        ->numeric()->prefix('Rp')->required()->live(onBlur: true)
                        ->helperText('Maksimal Rp 5.000.000')
                        ->afterStateUpdated(fn (Get $get, Set $set) => self::updateCalculations($get, $set))
                        ->rules([
                            fn (): Closure => function (string $attribute, $value, Closure $fail) {
                                if ($value > LoanCalculator::MAX_PRINCIPAL) {
                                    $fail('Nominal pinjaman maksimal ' . SupportNumber::currency(LoanCalculator::MAX_PRINCIPAL, 'IDR'));
                                }
                                if ($value <= 0) {
                                    $fail('Nominal pinjaman harus lebih dari 0');
                                }
                            },
                        ]),
                    TextInput::make('tenor_months')->label('Tenor (Bulan)')
                        ->numeric()->suffix('Bulan')->required()->live(onBlur: true)
                        ->default(3)
                        ->helperText('Maksimal 3 bulan')
                        ->afterStateUpdated(fn (Get $get, Set $set) => self::updateCalculations($get, $set))
                        ->rules([
                            fn (): Closure => function (string $attribute, $value, Closure $fail) {
                                if ($value > LoanCalculator::MAX_TENOR_MONTHS) {
                                    $fail('Tenor maksimal ' . LoanCalculator::MAX_TENOR_MONTHS . ' bulan');
                                }
                                if ($value < 1) {
                                    $fail('Tenor minimal 1 bulan');
                                }
                            },
                        ]),
                ]),
                Grid::make(2)->schema([
                    Select::make('payment_frequency')->label('Frekuensi Angsuran')
                        ->options([
                            'weekly' => 'Mingguan',
                            'monthly' => 'Bulanan',
                        ])
                        ->default('weekly')
                        ->required()
                        ->live()
                        ->afterStateUpdated(fn (Get $get, Set $set) => self::updateCalculations($get, $set)),
                    TextInput::make('installment_count')->label('Jumlah Angsuran')
                        ->numeric()->readonly()->dehydrated(),
                ]),
            ]),

            Section::make('Rincian Biaya (Admin)')
                ->description('Biaya angsuran 11% · Admin 5% · UTJ 22% · Cair bersih 73%')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('installment_fee')->label('Biaya Angsuran (11%)')
                            ->numeric()->prefix('Rp')->readonly()->dehydrated(),
                        TextInput::make('admin_fee')->label('Biaya Admin (5%)')
                            ->numeric()->prefix('Rp')->readonly()->dehydrated(),
                        TextInput::make('utj_fee')->label('UTJ (22%)')
                            ->numeric()->prefix('Rp')->readonly()->dehydrated(),
                        TextInput::make('net_disbursement')->label('Cair Bersih (73%)')
                            ->numeric()->prefix('Rp')->readonly()->dehydrated()
                            ->extraInputAttributes(['class' => 'font-bold']),
                    ]),
                    Grid::make(2)->schema([
                        TextInput::make('monthly_payment')->label('Angsuran per Periode')
                            ->numeric()->prefix('Rp')->readonly()->dehydrated(),
                        TextInput::make('total_payment')->label('Total yang Harus Dilunasi')
                            ->numeric()->prefix('Rp')->readonly()->dehydrated(),
                    ]),
                    Hidden::make('interest_rate')->default(11),
                    Hidden::make('remaining_balance')->default(0),
                ]),

            Section::make('Tanggal & Status')->schema([
                Grid::make(2)->schema([
                    DatePicker::make('application_date')->label('Tanggal Pengajuan')->default(now())->required(),
                    Select::make('status')
                        ->options(fn ($record = null) => self::getStatusOptionsForRole($record))
                        ->required()
                        ->default('pending')
                        ->disabled(function ($record = null) {
                            $user = Auth::user();
                            $userRoles = $user->roles->pluck('name')->toArray();
                            if (in_array('admin', $userRoles, true)) {
                                return !$record || $record->status === 'pending';
                            }
                            return false;
                        }),
                ]),
            ]),

            Section::make('Tujuan & Catatan')->schema([
                Textarea::make('purpose')->label('Tujuan Pinjaman'),
                Textarea::make('notes')->label('Catatan Tambahan'),
            ]),

            Hidden::make('cooperation_id')->default(fn () => Auth::user()->cooperation_id),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('loan_number')->label('No. Pinjaman')->searchable()->sortable(),
                TextColumn::make('user.name')->label('Peminjam')->searchable()->sortable(),
                TextColumn::make('principal_amount')->label('Nominal')->money('IDR')->sortable(),
                TextColumn::make('net_disbursement')->label('Cair Bersih')->money('IDR')->sortable()
                    ->toggleable(),
                TextColumn::make('remaining_balance')->label('Sisa Hutang')->money('IDR')->sortable()
                    ->color(fn ($state) => $state > 0 ? 'danger' : 'success'),
                TextColumn::make('payment_frequency')->label('Frekuensi')
                    ->formatStateUsing(fn (?string $state) => $state === 'monthly' ? 'Bulanan' : 'Mingguan')
                    ->toggleable(),
                TextColumn::make('tenor_months')->label('Tenor')
                    ->formatStateUsing(fn ($state, Loan $record) => $state . ' bln / ' . ($record->installment_count ?: '-') . 'x')
                    ->toggleable(),
                TextColumn::make('application_date')->label('Tgl Pengajuan')->date('d M Y')->sortable(),
                BadgeColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Menunggu',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        'disbursed' => 'Dicairkan',
                        'active' => 'Aktif',
                        'completed' => 'Lunas',
                        'overdue' => 'Jatuh Tempo',
                        default => $state,
                    })
                    ->colors([
                        'warning' => 'pending',
                        'success' => fn ($state) => in_array($state, ['approved', 'disbursed', 'active', 'completed'], true),
                        'danger' => fn ($state) => in_array($state, ['rejected', 'overdue'], true),
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Menunggu',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        'disbursed' => 'Dicairkan',
                        'active' => 'Aktif',
                        'completed' => 'Lunas',
                        'overdue' => 'Jatuh Tempo',
                    ]),
            ])
            ->actions(self::getActionsForRole())
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            PaymentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLoans::route('/'),
            'create' => Pages\CreateLoan::route('/create'),
            'view' => Pages\ViewLoan::route('/{record}'),
            'edit' => Pages\EditLoan::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return true;
    }

    public static function canView(Model $record): bool
    {
        return true;
    }

    public static function updateCalculations(Get $get, Set $set): void
    {
        $principal = floatval($get('principal_amount'));
        $tenor = intval($get('tenor_months')) ?: 1;
        $frequency = $get('payment_frequency') ?: 'weekly';

        if ($principal <= 0) {
            foreach (['admin_fee', 'utj_fee', 'installment_fee', 'net_disbursement', 'monthly_payment', 'total_payment', 'remaining_balance', 'installment_count'] as $field) {
                $set($field, 0);
            }
            return;
        }

        $calc = LoanCalculator::calculate($principal, $tenor, $frequency);
        foreach ($calc as $key => $value) {
            $set($key, $value);
        }
    }

    public static function getStatusOptionsForRole($record = null): array
    {
        $user = Auth::user();
        $userRoles = $user->roles->pluck('name')->toArray();

        if (in_array('admin', $userRoles, true)) {
            if (!$record || $record->status === 'pending') {
                return ['pending' => 'Pending'];
            }
            if ($record->status === 'rejected') {
                return ['rejected' => 'Ditolak'];
            }
            if (in_array($record->status, ['approved', 'disbursed', 'active', 'completed', 'overdue'], true)) {
                return [
                    'approved' => 'Disetujui',
                    'disbursed' => 'Dicairkan',
                    'active' => 'Aktif',
                    'completed' => 'Lunas',
                    'overdue' => 'Jatuh Tempo',
                ];
            }
        }

        if (array_intersect(['spv', 'kepalayayasan', 'kepala_yayasan'], $userRoles)) {
            return [
                'pending' => 'Pending',
                'approved' => 'Disetujui',
                'rejected' => 'Ditolak',
            ];
        }

        if (array_intersect(['kasir', 'cashier', 'bendahara'], $userRoles)) {
            return [
                'approved' => 'Disetujui',
                'disbursed' => 'Dicairkan',
                'active' => 'Aktif',
            ];
        }

        return [
            'pending' => 'Pending',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            'disbursed' => 'Dicairkan',
            'active' => 'Aktif',
            'completed' => 'Lunas',
            'overdue' => 'Jatuh Tempo',
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $user = Auth::user();
        $userRoles = $user->roles->pluck('name')->toArray();
        $query = parent::getEloquentQuery()->where('cooperation_id', $user->cooperation_id);

        // Kasir: lihat daftar pinjaman (approved ke atas)
        if (array_intersect(['kasir', 'cashier', 'bendahara'], $userRoles) && !in_array('admin', $userRoles, true)) {
            $query->whereIn('status', ['approved', 'disbursed', 'active', 'completed', 'overdue']);
        }

        if (array_intersect(['spv', 'kepalayayasan', 'kepala_yayasan'], $userRoles) && !in_array('admin', $userRoles, true)) {
            $query->whereIn('status', ['pending', 'approved', 'rejected', 'disbursed', 'active']);
        }

        return $query;
    }

    public static function canCreate(): bool
    {
        $userRoles = Auth::user()->roles->pluck('name')->toArray();
        return in_array('admin', $userRoles, true);
    }

    public static function canEdit(Model $record): bool
    {
        $user = Auth::user();
        $userRoles = $user->roles->pluck('name')->toArray();

        if (in_array('admin', $userRoles, true)) {
            return in_array($record->status, ['pending', 'rejected'], true);
        }

        return false;
    }

    public static function getActionsForRole(): array
    {
        $user = Auth::user();
        $userRoles = $user->roles->pluck('name')->toArray();
        $actions = [
            Tables\Actions\ViewAction::make()
                ->label('Detail / Cicilan')
                ->url(fn (Loan $record) => static::getUrl('view', ['record' => $record])),
        ];

        if (in_array('admin', $userRoles, true)) {
            $actions[] = Tables\Actions\EditAction::make()
                ->visible(fn (Loan $record) => static::canEdit($record));
        }

        // SPV: setujui / tolak
        if (array_intersect(['spv', 'kepalayayasan', 'kepala_yayasan'], $userRoles)) {
            $actions[] = Tables\Actions\Action::make('approve')
                ->label('Setujui')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn (Loan $record) => $record->status === 'pending')
                ->form([
                    Textarea::make('approval_notes')
                        ->label('Catatan Persetujuan')
                        ->placeholder('Opsional'),
                ])
                ->action(function (Loan $record, array $data) {
                    $record->update([
                        'status' => 'approved',
                        'approved_by' => Auth::id(),
                        'approved_date' => now(),
                        'notes' => ($record->notes ?? '') . "\n[Persetujuan SPV] " . ($data['approval_notes'] ?? 'Disetujui oleh SPV'),
                    ]);

                    \Filament\Notifications\Notification::make()
                        ->title('Pinjaman berhasil disetujui')
                        ->success()
                        ->send();
                });

            $actions[] = Tables\Actions\Action::make('reject')
                ->label('Tolak')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn (Loan $record) => $record->status === 'pending')
                ->requiresConfirmation()
                ->form([
                    Textarea::make('rejection_reason')
                        ->label('Alasan Penolakan')
                        ->required(),
                ])
                ->action(function (Loan $record, array $data) {
                    $record->update([
                        'status' => 'rejected',
                        'approved_by' => Auth::id(),
                        'approved_date' => now(),
                        'notes' => ($record->notes ?? '') . "\n[Penolakan SPV] " . $data['rejection_reason'],
                    ]);

                    \Filament\Notifications\Notification::make()
                        ->title('Pinjaman ditolak')
                        ->warning()
                        ->send();
                });
        }

        // Kasir: cairkan (tampilkan cair bersih)
        if (array_intersect(['kasir', 'cashier', 'bendahara'], $userRoles)) {
            $actions[] = Tables\Actions\Action::make('disburse')
                ->label('Cairkan')
                ->icon('heroicon-o-banknotes')
                ->color('warning')
                ->visible(fn (Loan $record) => $record->status === 'approved')
                ->requiresConfirmation()
                ->modalDescription(fn (Loan $record) => 'Cair bersih: Rp ' . number_format($record->net_disbursement, 0, ',', '.'))
                ->form([
                    DatePicker::make('disbursement_date')
                        ->label('Tanggal Pencairan')
                        ->default(now())
                        ->required(),
                    Textarea::make('disbursement_notes')
                        ->label('Catatan Pencairan')
                        ->placeholder('Opsional'),
                ])
                ->action(function (Loan $record, array $data) {
                    $record->update([
                        'status' => 'disbursed',
                        'disbursement_date' => $data['disbursement_date'],
                        'notes' => ($record->notes ?? '') . "\n[Pencairan Kasir] " . ($data['disbursement_notes'] ?? 'Dana dicairkan'),
                    ]);

                    // regenerate jadwal dari tanggal cair
                    (new \App\Services\LoanService())->generatePaymentSchedule($record->fresh());

                    \Filament\Notifications\Notification::make()
                        ->title('Dana pinjaman berhasil dicairkan')
                        ->body('Cair bersih: Rp ' . number_format($record->net_disbursement, 0, ',', '.'))
                        ->success()
                        ->send();
                });
        }

        return $actions;
    }
}
