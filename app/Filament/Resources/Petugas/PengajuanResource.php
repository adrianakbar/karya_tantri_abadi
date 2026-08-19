<?php

namespace App\Filament\Resources\Petugas;

use App\Filament\Resources\Petugas\PengajuanResource\Pages;
use App\Models\Loan;
use App\Services\LoanCalculator;
use Closure;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
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

class PengajuanResource extends Resource
{
    protected static ?string $model = Loan::class;
    protected static ?string $slug = 'pengajuan';

    protected static ?string $navigationIcon = 'heroicon-o-document-plus';
    protected static ?string $navigationLabel = 'Pengajuan Pinjaman';
    protected static ?string $modelLabel = 'Pengajuan';
    protected static ?string $pluralModelLabel = 'Pengajuan Pinjaman';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Data Nasabah')->schema([
                TextInput::make('applicant_name')
                    ->label('Nama Nasabah')
                    ->required()
                    ->maxLength(255),

                FileUpload::make('ktp_photo')
                    ->label('Foto KTP')
                    ->image()
                    ->directory('ktp')
                    ->maxSize(4096)
                    ->required()
                    ->helperText('JPG/PNG, maks 4 MB'),
            ])->columns(2),

            Section::make('Detail Pinjaman')
                ->description(fn (Get $get) => LoanCalculator::feeDescription(
                    floatval($get('principal_amount') ?: 0)
                ))
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('principal_amount')->label('Nominal Pinjaman')
                            ->numeric()->prefix('Rp')->required()
                            ->helperText('Maksimal Rp 5.000.000')
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
                            ->numeric()->suffix('Bulan')->required()->default(3)
                            ->helperText('Maksimal 3 bulan')
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
                            ->required(),
                        DatePicker::make('application_date')->label('Tanggal Pengajuan')
                            ->default(now())->required(),
                    ]),
                    Textarea::make('purpose')->label('Tujuan Pinjaman')->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('loan_number')->label('No. Pengajuan')->searchable()->sortable(),
                TextColumn::make('applicant_name')->label('Nasabah')->searchable()->sortable(),
                TextColumn::make('principal_amount')->label('Nominal')->money('IDR')->sortable(),
                TextColumn::make('application_date')->label('Tgl Pengajuan')->date('d M Y')->sortable(),
                BadgeColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Menunggu Koreksi Admin',
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
            ->filters([])
            ->actions([
                Tables\Actions\ViewAction::make()->label('Detail'),
            ])
            ->bulkActions([])
            ->emptyStateHeading('Belum ada pengajuan')
            ->emptyStateDescription('Buat pengajuan pinjaman baru untuk nasabah.');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPengajuans::route('/'),
            'create' => Pages\CreatePengajuan::route('/create'),
            'view' => Pages\ViewPengajuan::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        // Petugas hanya melihat pengajuan yang dia buat sendiri
        return parent::getEloquentQuery()
            ->where('created_by', Auth::id())
            ->where('cooperation_id', Auth::user()?->cooperation_id);
    }

    public static function canViewAny(): bool
    {
        return Auth::user()?->hasRole('petugas') ?? false;
    }

    // Setelah diajukan, koreksi/hapus jadi wewenang admin
    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }
}
