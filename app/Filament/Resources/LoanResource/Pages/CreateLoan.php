<?php

namespace App\Filament\Resources\LoanResource\Pages;

use App\Filament\Resources\LoanResource;
use App\Models\Loan;
use App\Models\LoanType;
use App\Services\LoanCalculator;
use App\Services\LoanService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateLoan extends CreateRecord
{
    protected static string $resource = LoanResource::class;

    public function mount(): void
    {
        abort_unless(LoanResource::canCreate(), 403);
        parent::mount();
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $today = now()->format('Ymd');
        $cooperationId = $data['cooperation_id'] ?? Auth::user()?->cooperation_id ?? 'X';
        $lastLoan = Loan::where('cooperation_id', $cooperationId)
            ->whereDate('created_at', today())->count();
        $sequence = str_pad($lastLoan + 1, 4, '0', STR_PAD_LEFT);

        $data['loan_number'] = "LOAN/{$cooperationId}/{$today}/{$sequence}";
        $data['status'] = $data['status'] ?? 'pending';
        $data['cooperation_id'] = $cooperationId;

        // Pastikan jenis pinjaman = Kelompok
        $kelompokId = LoanType::where('name', 'like', '%kelompok%')
            ->orWhere('name', 'Kelompok')
            ->value('id');
        if ($kelompokId) {
            $data['loan_type_id'] = $kelompokId;
        }

        $principal = floatval($data['principal_amount'] ?? 0);
        $tenor = intval($data['tenor_months'] ?? 3);
        $frequency = $data['payment_frequency'] ?? 'weekly';
        $calc = LoanCalculator::calculate($principal, $tenor, $frequency);
        $data = array_merge($data, $calc);

        return $data;
    }

    protected function afterCreate(): void
    {
        // Jadwal angsuran dibuat saat pencairan (kasir), bukan saat pengajuan
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    public function getTitle(): string
    {
        return 'Tambah Pinjaman Kelompok';
    }
}
