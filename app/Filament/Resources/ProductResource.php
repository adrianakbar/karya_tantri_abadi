<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Cooperation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Forms\Components\Hidden;
use Filament\Tables\Table;
use Filament\Forms\Components\{TextInput, Select, Textarea, FileUpload, Toggle};
use Filament\Notifications\Notification;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $modelLabel = 'Produk';
    protected static ?string $pluralModelLabel = 'Daftar Produk';
    protected static ?string $navigationLabel = 'Daftar Produk';

    public static function getNavigationGroup(): ?string
    {
        return 'Barang & Inventaris';
    }

    public static function getNavigationLabel(): string
    {
        return 'Daftar Produk';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function canViewAny(): bool
    {
        return false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Cooperation (relasi wajib)
                Hidden::make('cooperation_id')
                    ->default(fn() => \Illuminate\Support\Facades\Auth::user()->cooperation_id)
                    ->dehydrated(true)
                    ->required(),

                // Category (relasi nullable)
                Select::make('product_category_id')
                    ->label('Kategori Produk')
                    ->relationship('category', 'name')
                    ->nullable(),

                TextInput::make('name')
                    ->label('Nama Produk')
                    ->required(),
                Textarea::make('description')
                    ->label('Deskripsi')
                    ->nullable(),
                TextInput::make('unit')
                    ->required(),
                TextInput::make('purchase_price')
                    ->label('Harga Beli')
                    ->numeric()
                    ->required(),
                TextInput::make('selling_price')
                    ->label('Harga Jual')
                    ->numeric()
                    ->required(),
                TextInput::make('min_stock')
                    ->label('Stok Minimum')
                    ->numeric()
                    ->default(0),
                TextInput::make('current_stock')
                    ->label('Stok Tersedia')
                    ->numeric()
                    ->default(0),
                FileUpload::make('image_url')
                    ->label('Gambar Produk')
                    ->directory('product-images')
                    ->nullable(),
                Toggle::make('is_active')
                    ->label('Status')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_url')
                    ->disk('public')
                    ->circular()
                    ->label('Gambar'),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Produk')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('code')
                    ->label('Kode Produk')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori'),
                Tables\Columns\TextColumn::make('unit'),
                Tables\Columns\TextColumn::make('purchase_price')
                    ->money('IDR')
                    ->label('Harga Beli'),
                Tables\Columns\TextColumn::make('selling_price')
                    ->money('IDR')
                    ->label('Harga Jual'),
                Tables\Columns\TextColumn::make('current_stock')
                    ->label('Stok Tersedia')
                    ->badge()
                    ->color(fn($record) => match($record->stock_status) {
                        'habis' => 'danger',
                        'rendah' => 'warning', 
                        'normal' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(function ($record) {
                        $status = '';
                        if ($record->current_stock <= 0) {
                            $status = ' (Habis)';
                        } elseif ($record->isLowStock()) {
                            $status = ' (Stok Rendah)';
                        }
                        return $record->current_stock . $status;
                    }),
                Tables\Columns\TextColumn::make('min_stock')
                    ->label('Stok Minimum'),
                Tables\Columns\BadgeColumn::make('is_active')
                    ->label('Status')
                    ->formatStateUsing(fn($state) => $state ? 'Aktif' : 'Tidak Aktif')
                    ->colors([
                        'success' => fn($state) => $state === true,
                        'danger' => fn($state) => $state === false,
                    ]),
            ])
            ->defaultSort('current_stock', 'asc') // Menampilkan stok rendah di atas
            ->filters([
                Tables\Filters\SelectFilter::make('stock_status')
                    ->label('Status Stok')
                    ->options([
                        'habis' => 'Stok Habis',
                        'rendah' => 'Stok Rendah',
                        'normal' => 'Stok Normal',
                    ])
                    ->query(function ($query, $data) {
                        if (!$data['value']) return;
                        
                        switch ($data['value']) {
                            case 'habis':
                                return $query->where('current_stock', '<=', 0);
                            case 'rendah':
                                return $query->whereColumn('current_stock', '<', 'min_stock')
                                           ->where('current_stock', '>', 0);
                            case 'normal':
                                return $query->whereColumn('current_stock', '>=', 'min_stock');
                        }
                    }),
                
                Tables\Filters\Filter::make('low_stock_alert')
                    ->label('⚠️ Stok Rendah & Habis')
                    ->query(fn($query) => $query->whereColumn('current_stock', '<=', 'min_stock'))
                    ->toggle(),
            ])
            ->emptyStateHeading('Data tidak ditemukan')
            ->emptyStateDescription('Belum ada data produk yang tersedia.')
            ->actions([
                Tables\Actions\Action::make('view_stock_history')
                    ->label('Riwayat Stok')
                    ->icon('heroicon-o-clock')
                    ->color('info')
                    ->url(fn($record) => route('filament.admin.resources.stock-movement-logs.index', [
                        'tableFilters[product_id][value]' => $record->id
                    ]))
                    ->openUrlInNewTab(),
                Tables\Actions\Action::make('update_stock')
                    ->label('Update Stok')
                    ->icon('heroicon-o-plus-circle')
                    ->color('success')
                    ->visible(fn($record) => $record->isLowStock())
                    ->form([
                        Forms\Components\TextInput::make('add_stock')
                            ->label('Tambah Stok')
                            ->numeric()
                            ->required()
                            ->minValue(1),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'current_stock' => $record->current_stock + $data['add_stock']
                        ]);
                        
                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('Stok berhasil diperbarui!')
                            ->body("Stok {$record->name} bertambah {$data['add_stock']} unit.")
                            ->send();
                    }),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
