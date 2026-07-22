<?php

namespace App\Filament\Resources\Anggota\LoanResource\Pages;

use App\Filament\Resources\Anggota\LoanResource;
use App\Models\Loan;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateLoan extends CreateRecord
{
    protected static string $resource = LoanResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = Auth::user();
        $cooperationId = $user->cooperation_id;
        $today = now()->format('Ymd');
        
        $lastLoan = Loan::where('cooperation_id', $cooperationId)
            ->whereDate('created_at', today())->count();
        $sequence = str_pad($lastLoan + 1, 4, '0', STR_PAD_LEFT);

        $principal = floatval($data['principal_amount']);
        $tenor = intval($data['tenor_months']);

        $data['loan_number'] = "LOAN/{$cooperationId}/{$today}/{$sequence}";
        $data['user_id'] = $user->id;
        $data['cooperation_id'] = $cooperationId;
        $data['status'] = 'pending';
        $data['application_date'] = now();
        $data['interest_rate'] = 0;
        $data['total_payment'] = $principal;
        $data['monthly_payment'] = round($principal / $tenor);
        $data['remaining_balance'] = $principal;

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    public function getTitle(): string
    {
        return 'Ajukan Pinjaman';
    }
}
