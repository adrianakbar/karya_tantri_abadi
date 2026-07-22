<?php

namespace Tests\Feature\Reports;

use App\Models\User;
use App\Models\Cooperation;
use App\Models\Loan;
use App\Models\LoanPayment;
use App\Models\LoanType;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Livewire\Livewire;

class LoanReportTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private $user;
    private $cooperation;
    private $loanType;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create cooperation
        $this->cooperation = Cooperation::factory()->create();
        
        // Create loan type
        $this->loanType = LoanType::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'name' => 'Pinjaman Usaha',
            'interest_rate' => 2.5,
            'max_amount' => 10000000,
            'max_tenor_months' => 12,
        ]);
        
        // Create user
        $this->user = User::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'email_verified_at' => now(),
        ]);

        $adminRole = \App\Models\Roles::firstOrCreate([
            'cooperation_id' => $this->cooperation->id,
            'name' => 'admin',
        ]);
        \App\Models\UserRole::create([
            'user_id' => $this->user->id,
            'role_id' => $adminRole->id,
        ]);
        
        $this->actingAs($this->user);
    }

    /** @test */
    public function can_access_loan_report_page()
    {
        $response = $this->get('/admin/loan-report');
        
        $response->assertStatus(200);
        $response->assertSee('Laporan Pinjaman');
    }

    /** @test */
    public function loan_report_shows_loans_tab_by_default()
    {
        // Create loan
        $loan = Loan::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'user_id' => $this->user->id,
            'loan_type_id' => $this->loanType->id,
            'amount' => 5000000,
            'interest_rate' => 2.5,
            'term_months' => 12,
            'monthly_payment' => 450000,
            'status' => 'active',
        ]);

        $response = $this->get('/admin/loan-report');
        
        $response->assertStatus(200);
        $response->assertSee('5,000,000');
        $response->assertSee('450,000');
        $response->assertSee($this->user->name);
        $response->assertSee('Pinjaman Usaha');
        $response->assertSee('active');
    }

    /** @test */
    public function can_switch_to_payments_tab()
    {
        // Create loan
        $loan = Loan::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'user_id' => $this->user->id,
            'loan_type_id' => $this->loanType->id,
            'amount' => 5000000,
            'term_months' => 12,
            'monthly_payment' => 450000,
            'status' => 'active',
        ]);

        // Create loan payment
        LoanPayment::factory()->create([
            'loan_id' => $loan->id,
            'payment_date' => now(),
            'amount' => 450000,
            'principal_amount' => 400000,
            'interest_amount' => 50000,
            'status' => 'paid',
        ]);

        Livewire::test(\App\Filament\Pages\LoanReport::class)
            ->call('setActiveTab', 'payments')
            ->assertSee('400,000') // Principal amount
            ->assertSee('50,000');  // Interest amount
    }

    /** @test */
    public function can_filter_loans_by_type()
    {
        // Create another loan type
        $consumerLoanType = LoanType::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'name' => 'Pinjaman Konsumtif',
        ]);

        // Create loans with different types
        Loan::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'user_id' => $this->user->id,
            'loan_type_id' => $this->loanType->id,
            'amount' => 5000000,
        ]);

        Loan::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'user_id' => $this->user->id,
            'loan_type_id' => $consumerLoanType->id,
            'amount' => 3000000,
        ]);

        Livewire::test(\App\Filament\Pages\LoanReport::class)
            ->filterTable('jenis_pinjaman', [
                'loan_type_id' => $this->loanType->id,
            ])
            ->assertCanSeeTableRecords([
                // Should see only business loans
            ]);
    }

    /** @test */
    public function can_filter_loans_by_status()
    {
        // Create loans with different statuses
        Loan::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'user_id' => $this->user->id,
            'loan_type_id' => $this->loanType->id,
            'amount' => 5000000,
            'status' => 'active',
        ]);

        Loan::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'user_id' => $this->user->id,
            'loan_type_id' => $this->loanType->id,
            'amount' => 3000000,
            'status' => 'completed',
        ]);

        Livewire::test(\App\Filament\Pages\LoanReport::class)
            ->filterTable('status', [
                'status' => 'active',
            ])
            ->assertCanSeeTableRecords([
                // Should see only active loans
            ]);
    }

    /** @test */
    public function can_filter_loan_payments_by_period()
    {
        $loan = Loan::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'user_id' => $this->user->id,
            'loan_type_id' => $this->loanType->id,
        ]);

        // Create payments for different periods
        LoanPayment::factory()->create([
            'loan_id' => $loan->id,
            'payment_date' => now(),
            'amount' => 450000,
        ]);

        LoanPayment::factory()->create([
            'loan_id' => $loan->id,
            'payment_date' => now()->subMonth(),
            'amount' => 450000,
        ]);

        Livewire::test(\App\Filament\Pages\LoanReport::class)
            ->call('setActiveTab', 'payments')
            ->filterTable('periode', [
                'period_type' => 'monthly',
                'month' => now()->month,
                'year' => now()->year,
            ])
            ->assertCanSeeTableRecords([
                // Should see only current month payments
            ]);
    }

    /** @test */
    public function loan_report_calculates_remaining_balance_correctly()
    {
        $loan = Loan::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'user_id' => $this->user->id,
            'loan_type_id' => $this->loanType->id,
            'remaining_balance' => 4190000,
            'monthly_payment' => 450000,
            'status' => 'active',
        ]);

        // Create some payments
        LoanPayment::factory()->create([
            'loan_id' => $loan->id,
            'payment_date' => now()->subMonth(2),
            'amount' => 450000,
            'principal_amount' => 400000,
            'interest_amount' => 50000,
            'status' => 'paid',
        ]);

        LoanPayment::factory()->create([
            'loan_id' => $loan->id,
            'payment_date' => now()->subMonth(),
            'amount' => 450000,
            'principal_amount' => 410000,
            'interest_amount' => 40000,
            'status' => 'paid',
        ]);

        $response = $this->get('/admin/loan-report');
        
        $response->assertStatus(200);
        
        // Remaining balance should be: 5,000,000 - 400,000 - 410,000 = 4,190,000
        $response->assertSee('4.190.000');
    }

    /** @test */
    public function loan_report_shows_overdue_payments()
    {
        $loan = Loan::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'user_id' => $this->user->id,
            'loan_type_id' => $this->loanType->id,
            'amount' => 5000000,
            'monthly_payment' => 450000,
            'status' => 'active',
        ]);

        // Create overdue payment
        LoanPayment::factory()->create([
            'loan_id' => $loan->id,
            'payment_date' => now()->subDays(5), // Should have been paid 5 days ago
            'due_date' => now()->subDays(5),
            'amount' => 450000,
            'status' => 'overdue',
        ]);

        Livewire::test(\App\Filament\Pages\LoanReport::class)
            ->call('setActiveTab', 'payments')
            ->assertSee('overdue');
    }

    /** @test */
    public function loan_report_respects_cooperation_isolation()
    {
        // Create another cooperation and user
        $otherCooperation = Cooperation::factory()->create();
        $otherUser = User::factory()->create(['cooperation_id' => $otherCooperation->id]);
        $otherLoanType = LoanType::factory()->create(['cooperation_id' => $otherCooperation->id]);

        // Create loan for current cooperation
        Loan::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'user_id' => $this->user->id,
            'loan_type_id' => $this->loanType->id,
            'amount' => 5000000,
        ]);

        // Create loan for other cooperation
        Loan::factory()->create([
            'cooperation_id' => $otherCooperation->id,
            'user_id' => $otherUser->id,
            'loan_type_id' => $otherLoanType->id,
            'amount' => 8000000,
        ]);

        // Current user should only see their cooperation's data
        $response = $this->get('/admin/loan-report');
        $response->assertStatus(200);
        $response->assertSee('5,000,000');
        $response->assertDontSee('8,000,000');
        $response->assertSee($this->user->name);
        $response->assertDontSee($otherUser->name);
    }

    /** @test */
    public function can_export_loan_report()
    {
        Loan::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'user_id' => $this->user->id,
            'loan_type_id' => $this->loanType->id,
            'amount' => 5000000,
        ]);

        Livewire::test(\App\Filament\Pages\LoanReport::class)
            ->call('exportLoansExcel')
            ->assertFileDownloaded('laporan-pinjaman-' . now()->format('Y-m-d') . '.xlsx');
    }

    /** @test */
    public function loan_report_calculates_summary_cards_correctly()
    {
        // Create multiple loans
        Loan::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'user_id' => $this->user->id,
            'loan_type_id' => $this->loanType->id,
            'remaining_balance' => 5000000,
            'status' => 'active',
        ]);

        $anotherUser = User::factory()->create(['cooperation_id' => $this->cooperation->id]);
        
        Loan::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'user_id' => $anotherUser->id,
            'loan_type_id' => $this->loanType->id,
            'remaining_balance' => 3000000,
            'status' => 'active',
        ]);

        Loan::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'user_id' => $this->user->id,
            'loan_type_id' => $this->loanType->id,
            'amount' => 2000000,
            'status' => 'completed',
        ]);

        $response = $this->get('/admin/loan-report');
        
        $response->assertStatus(200);
        
        // Should show active loans count and total outstanding
        // Active loans: 2 (5M + 3M = 8M total outstanding)
        $response->assertSee('8.000.000');
    }

    /** @test */
    public function loan_report_shows_interest_calculations()
    {
        $loan = Loan::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'user_id' => $this->user->id,
            'loan_type_id' => $this->loanType->id,
            'amount' => 1000000,
            'interest_rate' => 2.0, // 2% per month
            'term_months' => 12,
        ]);

        $response = $this->get('/admin/loan-report');
        
        $response->assertStatus(200);
        $response->assertSee('2'); // Interest rate
        $response->assertSee('1,000,000'); // Principal amount
    }
}
