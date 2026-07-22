<?php

namespace App\Filament\Resources;

use App\Models\Expense;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Filament\Resources\ExpenseResource\Pages;

class ExpenseResource extends Resource
{
    protected static ?string $model = Expense::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Transaksi';
    protected static ?string $label = 'Pengeluaran';
    protected static ?string $pluralLabel = 'Daftar Pengeluaran';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Select::make('cooperation_id')
                ->label('Organisasi')
                ->relationship('cooperation', 'name')
                ->required(),

            Select::make('expense_category_id')
                ->label('Kategori Pengeluaran')
                ->relationship('category', 'name')
                ->required(),

            TextInput::make('receipt_number')
                ->label('No. Kwitansi')
                ->maxLength(100)
                ->nullable(),

            TextInput::make('recipient')
                ->label('Penerima')
                ->maxLength(255),

            TextInput::make('amount')
                ->label('Jumlah')
                ->prefix('Rp')
                ->numeric()
                ->required(),

            DatePicker::make('expense_date')
                ->label('Tanggal Pengeluaran')
                ->default(now())
                ->required(),

            // Select::make('processed_by')
            //     ->label('Diproses Oleh')
            //     ->relationship('processor', 'name')
            //     ->searchable(),

            // Select::make('approved_by')
            //     ->label('Disetujui Oleh')
            //     ->relationship('approver', 'name')
            //     ->searchable(),

            Select::make('status')
                ->label('Status')
                ->options([
                    'pending' => 'Menunggu',
                    'approved' => 'Disetujui',
                    'rejected' => 'Ditolak',
                ])
                ->default('pending'),

            Textarea::make('notes')
                ->label('Catatan Tambahan')
                ->maxLength(500),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('expense_date')
                ->label('Tanggal')
                ->date(),

            TextColumn::make('expense_number')
                ->label('No. Pengeluaran')
                ->searchable(),

            TextColumn::make('category.name')
                ->label('Kategori')
                ->sortable()
                ->searchable(),

            TextColumn::make('cooperation.name')
                ->label('Organisasi')
                ->sortable()
                ->searchable(),

            TextColumn::make('recipient')
                ->label('Penerima'),

            TextColumn::make('amount')
                ->label('Jumlah')
                ->money('IDR', true)
                ->sortable(),

            BadgeColumn::make('status')
                ->label('Status')
                ->colors([
                    'warning' => 'pending',
                    'success' => 'approved',
                    'danger'  => 'rejected',
                ])
                ->formatStateUsing(fn($state) => match ($state) {
                    'pending' => 'Menunggu',
                    'approved' => 'Disetujui',
                    'rejected' => 'Ditolak',
                    default => $state,
                }),
        ])
            ->filters([
                Tables\Filters\SelectFilter::make('expense_category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name'),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Menunggu',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                    ]),

                Tables\Filters\Filter::make('expense_date')
                    ->form([
                        Select::make('period')
                            ->label('Periode Pengeluaran')
                            ->options([
                                'today' => 'Hari Ini',
                                'yesterday' => 'Kemarin',
                                'this_week' => 'Minggu Ini',
                                'last_week' => 'Minggu Lalu',
                                'this_month' => 'Bulan Ini',
                                'last_month' => 'Bulan Lalu',
                                'this_year' => 'Tahun Ini',
                                'custom' => 'Kustom',
                            ])
                            ->live()
                            ->afterStateUpdated(fn (Set $set) => $set('date_from', null) + $set('date_to', null)),
                        DatePicker::make('date_from')
                            ->label('Dari Tanggal')
                            ->visible(fn (Get $get) => $get('period') === 'custom')
                            ->maxDate(fn (Get $get) => $get('date_to') ?: now()),
                        DatePicker::make('date_to')
                            ->label('Sampai Tanggal')
                            ->visible(fn (Get $get) => $get('period') === 'custom')
                            ->minDate(fn (Get $get) => $get('date_from'))
                            ->maxDate(now()),
                    ])
                    ->query(function ($query, array $data) {
                        if (empty($data['period'])) {
                            return $query;
                        }

                        return $query->when(
                            $data['period'],
                            function ($query, $period) use ($data) {
                                $now = now();
                                
                                switch ($period) {
                                    case 'today':
                                        return $query->whereDate('expense_date', $now->toDateString());
                                    case 'yesterday':
                                        return $query->whereDate('expense_date', $now->subDay()->toDateString());
                                    case 'this_week':
                                        return $query->whereBetween('expense_date', [
                                            $now->startOfWeek()->toDateString(),
                                            $now->endOfWeek()->toDateString()
                                        ]);
                                    case 'last_week':
                                        return $query->whereBetween('expense_date', [
                                            $now->subWeek()->startOfWeek()->toDateString(),
                                            $now->subWeek()->endOfWeek()->toDateString()
                                        ]);
                                    case 'this_month':
                                        return $query->whereMonth('expense_date', $now->month)
                                            ->whereYear('expense_date', $now->year);
                                    case 'last_month':
                                        return $query->whereMonth('expense_date', $now->subMonth()->month)
                                            ->whereYear('expense_date', $now->subMonth()->year);
                                    case 'this_year':
                                        return $query->whereYear('expense_date', $now->year);
                                    case 'custom':
                                        if (!empty($data['date_from']) && !empty($data['date_to'])) {
                                            return $query->whereBetween('expense_date', [
                                                $data['date_from'],
                                                $data['date_to']
                                            ]);
                                        }
                                        if (!empty($data['date_from'])) {
                                            return $query->whereDate('expense_date', '>=', $data['date_from']);
                                        }
                                        if (!empty($data['date_to'])) {
                                            return $query->whereDate('expense_date', '<=', $data['date_to']);
                                        }
                                        return $query;
                                    default:
                                        return $query;
                                }
                            }
                        );
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (empty($data['period'])) {
                            return null;
                        }

                        $labels = [
                            'today' => 'Hari Ini',
                            'yesterday' => 'Kemarin',
                            'this_week' => 'Minggu Ini',
                            'last_week' => 'Minggu Lalu',
                            'this_month' => 'Bulan Ini',
                            'last_month' => 'Bulan Lalu',
                            'this_year' => 'Tahun Ini',
                        ];

                        if ($data['period'] === 'custom' && !empty($data['date_from']) && !empty($data['date_to'])) {
                            return 'Periode: ' . date('d M Y', strtotime($data['date_from'])) . ' - ' . date('d M Y', strtotime($data['date_to']));
                        }

                        return 'Periode: ' . ($labels[$data['period']] ?? $data['period']);
                    }),
            ])
            ->emptyStateHeading('Data tidak ditemukan')
            ->emptyStateDescription('Belum ada data pengeluaran yang tersedia.')
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Lihat'),
                Tables\Actions\EditAction::make()
                    ->label('Edit'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus Terpilih'),
                ]),
            ])
            ->defaultSort('expense_date', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExpenses::route('/'),
            'create' => Pages\CreateExpense::route('/create'),
            'edit' => Pages\EditExpense::route('/{record}/edit'),
        ];
    }
}
