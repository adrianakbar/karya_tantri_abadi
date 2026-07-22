<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExpenseCategoryResource\Pages;
use App\Models\ExpenseCategory;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;

class ExpenseCategoryResource extends Resource
{
    protected static ?string $model = ExpenseCategory::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Transaksi';
    protected static ?string $pluralLabel = 'Jenis Pengeluaran';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Select::make('cooperation_id')
                ->label('Organisasi')
                ->relationship('cooperation', 'name')
                ->required(),

            TextInput::make('name')
                ->label('Nama Kategori')
                ->required()
                ->maxLength(255),

            Textarea::make('description')
                ->label('Deskripsi')
                ->maxLength(500),

            Toggle::make('is_active')
                ->label('Aktif')
                ->default(true)
                ->inline(false),

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('no')
                ->label('No')
                ->rowIndex(),

            TextColumn::make('cooperation.name')
                ->label('Organisasi')
                ->sortable()
                ->searchable(),

            TextColumn::make('name')
                ->label('Nama Kategori')
                ->sortable()
                ->searchable(),

            TextColumn::make('code')
                ->label('Kode')
                ->sortable()
                ->searchable(),

            IconColumn::make('is_active')
                ->boolean()
                ->label('Aktif'),

            TextColumn::make('created_at')
                ->label('Dibuat')
                ->dateTime('d M Y H:i')
                ->sortable(),
        ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('Semua Status')
                    ->trueLabel('Aktif')
                    ->falseLabel('Tidak Aktif'),
            ])
            ->emptyStateHeading('Data tidak ditemukan')
            ->emptyStateDescription('Belum ada data jenis pengeluaran yang tersedia.')
            ->defaultSort('created_at', 'desc')
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
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExpenseCategories::route('/'),
            'create' => Pages\CreateExpenseCategory::route('/create'),
            'edit' => Pages\EditExpenseCategory::route('/{record}/edit'),
        ];
    }
}
