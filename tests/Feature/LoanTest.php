<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Cooperation;
use App\Models\Loan;
use App\Models\LoanType;
use App\Models\LoanPayment;
use App\Models\Roles;
use App\Models\UserRole;
use App\Services\LoanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Livewire\Livewire;

class LoanTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $cooperation;
    private $loanType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cooperation = Cooperation::factory()->create();

        $this->loanType = LoanType::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'name' => 'Pinjaman Pendidikan',
            'max_amount' => 10000000.00,
            'interest_rate' => 2.50,
            'max_tenor_months' => 12,
        ]);

        $this->user = User::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'email_verified_at' => now(),
        ]);

        $adminRole = Roles::firstOrCreate([
            'cooperation_id' => $this->cooperation->id,
            'name' => 'admin',
        ]);
        UserRole::create([
            'user_id' => $this->user->id,
            'role_id' => $adminRole->id,
        ]);

        $this->actingAs($this->user);
    }

    /** @test */
    public function user_can_list_loans()
    {
        $loan = Loan::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'user_id' => $this->user->id,
            'loan_type_id' => $this->loanType->id,
            'principal_amount' => 5000000.00,
            'status' => 'active',
        ]);

        Livewire::test(\App\Filament\Resources\LoanResource\Pages\ListLoans::class)
            ->assertCanSeeTableRecords([$loan]);
    }

    /** @test */
    public function user_can_apply_for_loan()
    {
        $anotherUser = User::factory()->create([
            'cooperation_id' => $this->cooperation->id,
        ]);

        Livewire::test(\App\Filament\Resources\LoanResource\Pages\CreateLoan::class)
            ->fillForm([
                'user_id' => $anotherUser->id,
                'loan_type_id' => $this->loanType->id,
                'principal_amount' => 4000000.00,
                'interest_rate' => 2.50,
                'tenor_months' => 10,
                'monthly_payment' => 440000.00,
                'total_payment' => 4400000.00,
                'remaining_balance' => 4400000.00,
                'status' => 'pending',
                'application_date' => now()->toDateString(),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('loans', [
            'cooperation_id' => $this->cooperation->id,
            'user_id' => $anotherUser->id,
            'loan_type_id' => $this->loanType->id,
            'principal_amount' => 4000000.00,
            'status' => 'pending',
        ]);
    }

    /** @test */
    public function loan_service_generates_correct_payment_schedule()
    {
        $loan = Loan::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'user_id' => $this->user->id,
            'loan_type_id' => $this->loanType->id,
            'principal_amount' => 6000000.00,
            'interest_rate' => 2.00,
            'tenor_months' => 6,
            'monthly_payment' => 1020000.00,
            'total_payment' => 6120000.00,
            'status' => 'active',
        ]);

        $service = new LoanService();
        $service->generatePaymentSchedule($loan);

        $this->assertEquals(6, LoanPayment::where('loan_id', $loan->id)->count());

        $firstPayment = LoanPayment::where('loan_id', $loan->id)
            ->where('installment_number', 1)
            ->first();

        $this->assertEquals(1000000.00, $firstPayment->principal_amount);
        $this->assertEquals(20000.00, $firstPayment->interest_amount);
        $this->assertEquals(1020000.00, $firstPayment->total_amount);
        $this->assertEquals('pending', $firstPayment->status);
    }
}
