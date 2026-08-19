<?php

namespace App\Filament\Resources\Petugas\PengajuanResource\Pages;

use App\Filament\Resources\Petugas\PengajuanResource;
use App\Models\Loan;
use App\Models\LoanType;
use App\Services\LoanCalculator;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreatePengajuan extends CreateRecord
{
    protected static string $resource = PengajuanResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = Auth::user();
        $cooperationId = $user->cooperation_id;

        $today = now()->format('Ymd');
        $count = Loan::where('cooperation_id', $cooperationId)
            ->whereDate('created_at', today())->count();
        $sequence = str_pad($count + 1, 4, '0', STR_PAD_LEFT);

        $data['loan_number'] = "LOAN/{$cooperationId}/{$today}/{$sequence}";
        $data['cooperation_id'] = $cooperationId;
        $data['created_by'] = $user->id;
        $data['user_id'] = null; // belum ada akun anggota; admin buat saat koreksi
        $data['status'] = 'pending';
        $data['loan_type_id'] = LoanType::where('name', 'like', '%kelompok%')
            ->orWhere('name', 'Kelompok')->value('id');

        $principal = floatval($data['principal_amount'] ?? 0);
        $tenor = intval($data['tenor_months'] ?? 3);
        $frequency = $data['payment_frequency'] ?? 'weekly';

        return array_merge($data, LoanCalculator::calculate($principal, $tenor, $frequency));
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    public function getTitle(): string
    {
        return 'Ajukan Pinjaman Nasabah';
    }
}
