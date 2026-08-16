<?php

namespace App\Filament\Resources\Petugas\NasabahResource\Pages;

use App\Filament\Resources\Petugas\NasabahResource;
use App\Models\Roles;
use App\Models\User;
use App\Models\UserRole;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CreateNasabah extends CreateRecord
{
    protected static string $resource = NasabahResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = Auth::user();
        $data['cooperation_id'] = $user->cooperation_id;
        $data['created_by'] = $user->id;
        $data['is_active'] = true;
        $data['join_date'] = now()->toDateString();
        $data['member_number'] = $this->generateMemberNumber($user->cooperation_id);

        return $data;
    }

    protected function generateMemberNumber(int $cooperationId): string
    {
        $prefix = 'NSB';
        $today = now()->format('ymd');
        $count = User::where('cooperation_id', $cooperationId)
            ->whereDate('created_at', today())
            ->count();
        $sequence = str_pad($count + 1, 3, '0', STR_PAD_LEFT);

        return "{$prefix}{$today}{$sequence}";
    }

    protected function afterCreate(): void
    {
        // Nasabah otomatis punya role anggota (bisa login /anggota, lihat pinjaman sendiri)
        $anggotaRole = Roles::where('name', 'anggota')
            ->where('cooperation_id', $this->record->cooperation_id)
            ->first();

        if ($anggotaRole) {
            UserRole::firstOrCreate([
                'user_id' => $this->record->id,
                'role_id' => $anggotaRole->id,
            ]);
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    public function getTitle(): string
    {
        return 'Input Nasabah Baru';
    }
}
