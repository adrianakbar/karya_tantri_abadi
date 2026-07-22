<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LoanTypeResource\Pages;
use App\Filament\Resources\LoanTypeResource\RelationManagers;
use App\Models\LoanType;
use Filament\Forms;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LoanTypeResource extends Resource
{
    protected static ?string $model = LoanType::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $pluralModelLabel = 'Daftar Jenis Pinjaman';
    protected static ?string $navigationLabel = 'Jenis Pinjaman';
    protected static ?string $navigationGroup = 'Transaksi';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Hidden::make('cooperation_id')
                    ->default(fn() => \Illuminate\Support\Facades\Auth::user()->cooperation_id)
                    ->dehydrated(true)
                    ->required(),
                Forms\Components\TextInput::make('name')
                    ->label('Nama Pinjaman')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('max_amount')
                    ->label('Maksimal Pinjaman')
                    ->numeric()->prefix('Rp')->required()
                    ->maxLength(255),
                Hidden::make('interest_rate')
                    ->label('Suku Bunga (%)')
                    ->default(0) // Set default value 0
                    ->dehydrated(true) // Pastikan value tersimpan
                    ->required(),
                Forms\Components\TextInput::make('max_tenor_months')
                    ->label('Maksimal Tenor (Bulan)')
                    ->numeric()->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->label('Deskripsi')
                    ->nullable()
                    ->maxLength(500),
                Forms\Components\Toggle::make('is_active')
                    ->label('Status')
                    ->default(true)
                    ->label('Aktif'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Pinjaman')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('max_amount')
                    ->label('Maksimal Pinjaman')
                    ->money('idr', true)
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('max_tenor_months')
                    ->label('Maksimal Tenor (Bulan)')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Deskripsi'),
                Tables\Columns\BadgeColumn::make('is_active')
                    ->label('Status')
                    ->formatStateUsing(fn($state) => $state ? 'Aktif' : 'Tidak Aktif')
                    ->colors([
                        'success' => fn($state) => $state === true,
                        'danger' => fn($state) => $state === false,
                    ]),
            ])
            ->filters([
                //
            ])
            ->emptyStateHeading('Data tidak ditemukan')
            ->emptyStateDescription('Belum ada data jenis pinjaman yang tersedia.')
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Lihat'),
                Tables\Actions\EditAction::make()
                    ->label('Edit'),
                Tables\Actions\DeleteAction::make()
                    ->label('Hapus'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus Terpilih'),
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
            'index' => Pages\ListLoanTypes::route('/'),
            'create' => Pages\CreateLoanType::route('/create'),
            'edit' => Pages\EditLoanType::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function canViewAny(): bool
    {
        return false;
    }
}
