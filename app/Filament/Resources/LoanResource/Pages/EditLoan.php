<?php

namespace App\Filament\Resources\LoanResource\Pages;

use App\Filament\Resources\LoanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLoan extends EditRecord
{
    protected static string $resource = LoanResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);
        abort_unless(LoanResource::canEdit($this->record), 403);
    }

    public function getTitle(): string
    {
        return 'Edit Pinjaman';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Hapus')
                ->visible(fn () => auth()->user()?->hasRole('admin')),
        ];
    }

    public function getRelationManagers(): array
    {
        // Jadwal cicilan dipindah ke halaman View (Detail)
        return [];
    }

    protected function afterSave(): void
    {
        // Jadwal dibuat saat pencairan, bukan saat edit pengajuan
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
