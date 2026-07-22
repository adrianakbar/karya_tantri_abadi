<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SavingsTypeResource\Pages;
use App\Models\SavingsType;
use Filament\Forms;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SavingsTypeResource extends Resource
{
    protected static ?string $model = SavingsType::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Transaksi';
    protected static ?string $navigationLabel = 'Jenis Tabungan';
    protected static ?string $pluralModelLabel = 'Jenis Tabungan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Hidden::make('cooperation_id')
                    ->default(fn() => \Illuminate\Support\Facades\Auth::user()->cooperation_id)
                    ->dehydrated(true)
                    ->required(),
                Forms\Components\TextInput::make('name')
                    ->label('Nama')
                    ->required()
                    ->maxLength(100),
                Forms\Components\TextInput::make('code')
                    ->label('Kode')
                    ->required()
                    ->maxLength(20),
                Forms\Components\TextInput::make('amount')
                    ->label('Nominal')
                    ->numeric()
                    ->rules(['nullable', 'numeric', 'min:0'])
                    ->helperText('Kosongkan untuk simpanan sukarela')
                    ->prefix('Rp'),
                Forms\Components\Toggle::make('is_mandatory')
                    ->label('Wajib')
                    ->default(false),
                Forms\Components\Textarea::make('description')
                    ->label('Deskripsi')
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('code')
                    ->label('Kode')
                    ->searchable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Nominal')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_mandatory')
                    ->label('Wajib')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Terakhir Diubah')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('Semua Status')
                    ->trueLabel('Aktif')
                    ->falseLabel('Tidak Aktif'),
                Tables\Filters\TernaryFilter::make('is_mandatory')
                    ->label('Jenis')
                    ->placeholder('Semua Jenis')
                    ->trueLabel('Wajib')
                    ->falseLabel('Sukarela'),
            ])
            ->emptyStateHeading('Data tidak ditemukan')
            ->emptyStateDescription('Belum ada data jenis tabungan yang tersedia.')
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSavingsTypes::route('/'),
            'create' => Pages\CreateSavingsType::route('/create'),
            'edit' => Pages\EditSavingsType::route('/{record}/edit'),
        ];
    }
}
