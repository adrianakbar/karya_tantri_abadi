<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user';

    protected static ?string $navigationLabel = 'Data Anggota';
    protected static ?string $modelLabel = 'Anggota';
    protected static ?string $pluralModelLabel = 'Daftar Anggota';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('cooperation_id')
                    ->label('Organisasi')
                    ->relationship('cooperation', 'name')
                    ->required(),

                TextInput::make('member_number')
                    ->label('Nomor Anggota')
                    ->maxLength(50),

                TextInput::make('name')
                    ->label('Nama')
                    ->required()
                    ->maxLength(255),

                TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(100),

                TextInput::make('phone')
                    ->tel()
                    ->label('No. Telepon')
                    ->required()
                    ->maxLength(20),

                TextInput::make('password')
                    ->password()
                    ->required(fn($livewire) => $livewire instanceof \Filament\Resources\Pages\CreateRecord)
                    ->maxLength(255),

                Textarea::make('address')
                    ->label('Alamat')
                    ->maxLength(65535)
                    ->required(),

                DatePicker::make('birth_date')
                    ->label('Tanggal Lahir')
                    ->required(),

                Select::make('gender')
                    ->options([
                        'male' => 'Laki-laki',
                        'female' => 'Perempuan',
                    ])
                    ->required(),

                TextInput::make('job')
                    ->label('Pekerjaan')
                    ->required()
                    ->maxLength(100),

                FileUpload::make('profile_photo')
                    ->image()
                    ->label('Foto Profil')
                    ->directory('profile-photos'),

                DatePicker::make('join_date')
                    ->label('Tanggal Bergabung')
                    ->default(now())
                    ->required(),

                Toggle::make('is_active')
                    ->label('Status Anggota')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('member_number')
                    ->label('Nomor Anggota')
                    ->searchable(),

                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('phone')
                    ->label('No. Telepon')
                    ->searchable(),

                TextColumn::make('gender'),

                TextColumn::make('job')
                    ->label('Pekerjaan'),

                ImageColumn::make('profile_photo')
                    ->label('Foto')
                    ->disk('public')
                    ->circular(),

                Tables\Columns\BadgeColumn::make('is_active')
                    ->label('Status')
                    ->formatStateUsing(fn($state) => $state ? 'Aktif' : 'Tidak Aktif')
                    ->colors([
                        'success' => fn($state) => $state === true,
                        'danger' => fn($state) => $state === false,
                    ]),

                TextColumn::make('join_date')
                    ->label('Tanggal Bergabung')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('join_date')
                    ->form([
                        Forms\Components\Select::make('period')
                            ->label('Periode Bergabung')
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
                            ->afterStateUpdated(fn (Forms\Set $set) => $set('date_from', null) + $set('date_to', null)),
                        Forms\Components\DatePicker::make('date_from')
                            ->label('Dari Tanggal')
                            ->visible(fn (Forms\Get $get) => $get('period') === 'custom')
                            ->maxDate(fn (Forms\Get $get) => $get('date_to') ?: now()),
                        Forms\Components\DatePicker::make('date_to')
                            ->label('Sampai Tanggal')
                            ->visible(fn (Forms\Get $get) => $get('period') === 'custom')
                            ->minDate(fn (Forms\Get $get) => $get('date_from'))
                            ->maxDate(now()),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (empty($data['period'])) {
                            return $query;
                        }

                        return $query->when(
                            $data['period'],
                            function (Builder $query, $period) use ($data) {
                                $now = now();
                                
                                switch ($period) {
                                    case 'today':
                                        return $query->whereDate('join_date', $now->toDateString());
                                    case 'yesterday':
                                        return $query->whereDate('join_date', $now->subDay()->toDateString());
                                    case 'this_week':
                                        return $query->whereBetween('join_date', [
                                            $now->startOfWeek()->toDateString(),
                                            $now->endOfWeek()->toDateString()
                                        ]);
                                    case 'last_week':
                                        return $query->whereBetween('join_date', [
                                            $now->subWeek()->startOfWeek()->toDateString(),
                                            $now->subWeek()->endOfWeek()->toDateString()
                                        ]);
                                    case 'this_month':
                                        return $query->whereMonth('join_date', $now->month)
                                            ->whereYear('join_date', $now->year);
                                    case 'last_month':
                                        return $query->whereMonth('join_date', $now->subMonth()->month)
                                            ->whereYear('join_date', $now->subMonth()->year);
                                    case 'this_year':
                                        return $query->whereYear('join_date', $now->year);
                                    case 'custom':
                                        if (!empty($data['date_from']) && !empty($data['date_to'])) {
                                            return $query->whereBetween('join_date', [
                                                $data['date_from'],
                                                $data['date_to']
                                            ]);
                                        }
                                        if (!empty($data['date_from'])) {
                                            return $query->whereDate('join_date', '>=', $data['date_from']);
                                        }
                                        if (!empty($data['date_to'])) {
                                            return $query->whereDate('join_date', '<=', $data['date_to']);
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
            ->emptyStateDescription('Belum ada data pengguna yang tersedia.')
            ->actions([
                Tables\Actions\EditAction::make()->label('Edit'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }


    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
