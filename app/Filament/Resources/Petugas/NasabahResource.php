<?php

namespace App\Filament\Resources\Petugas;

use App\Filament\Resources\Petugas\NasabahResource\Pages;
use App\Models\User;
use App\Models\UserRole;
use App\Models\Roles;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class NasabahResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $slug = 'nasabahs';

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Data Nasabah';
    protected static ?string $modelLabel = 'Nasabah';
    protected static ?string $pluralModelLabel = 'Data Nasabah';

    public static function getEloquentQuery(): Builder
    {
        // Petugas hanya melihat nasabah yang dia input sendiri
        return parent::getEloquentQuery()
            ->where('created_by', Auth::id())
            ->where('cooperation_id', Auth::user()?->cooperation_id);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Data Nasabah')->schema([
                TextInput::make('name')
                    ->label('Nama Lengkap')
                    ->required()
                    ->maxLength(255),

                TextInput::make('phone')
                    ->tel()
                    ->label('No. Telepon')
                    ->required()
                    ->maxLength(20),

                TextInput::make('email')
                    ->email()
                    ->label('Email (opsional, untuk login nasabah)')
                    ->unique(ignoreRecord: true)
                    ->maxLength(100),

                DatePicker::make('birth_date')
                    ->label('Tanggal Lahir')
                    ->required(),

                Select::make('gender')
                    ->label('Jenis Kelamin')
                    ->options([
                        'male' => 'Laki-laki',
                        'female' => 'Perempuan',
                    ])
                    ->required(),

                TextInput::make('job')
                    ->label('Pekerjaan')
                    ->maxLength(100),

                Textarea::make('address')
                    ->label('Alamat')
                    ->required()
                    ->rows(2),

                TextInput::make('password')
                    ->password()
                    ->label('Password (untuk login nasabah)')
                    ->default('nasabah123')
                    ->required(fn ($livewire) => $livewire instanceof \Filament\Resources\Pages\CreateRecord)
                    ->revealable(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('member_number')
                    ->label('No. Anggota')
                    ->searchable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label('No. Telepon')
                    ->searchable(),

                Tables\Columns\TextColumn::make('gender')
                    ->label('Jenis Kelamin')
                    ->formatStateUsing(fn (string $state): string => $state === 'male' ? 'Laki-laki' : 'Perempuan'),

                Tables\Columns\TextColumn::make('job')
                    ->label('Pekerjaan'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Input')
                    ->date('d M Y')
                    ->sortable(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\ViewAction::make()->label('Detail'),
            ])
            ->bulkActions([])
            ->emptyStateHeading('Belum ada nasabah')
            ->emptyStateDescription('Input nasabah baru untuk mulai mendata.');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNasabahs::route('/'),
            'create' => Pages\CreateNasabah::route('/create'),
            'view' => Pages\ViewNasabah::route('/{record}'),
        ];
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

    public static function canViewAny(): bool
    {
        return Auth::user()?->hasRole('petugas') ?? false;
    }
}
