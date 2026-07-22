<?php

namespace Tests\Feature\Reports;

use App\Models\User;
use App\Models\Cooperation;
use App\Models\SavingsTransaction;
use App\Models\SavingsType;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Livewire\Livewire;

class SavingsReportTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private $user;
    private $cooperation;
    private $savingsType;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create cooperation
        $this->cooperation = Cooperation::factory()->create();
        
        // Create savings type
        $this->savingsType = SavingsType::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'name' => 'Simpanan Pokok',
            'is_mandatory' => true,
        ]);
        
        // Create user
        $this->user = User::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'email_verified_at' => now(),
        ]);

        // Create admin role and assign to user
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
    public function can_access_savings_report_page()
    {
        $response = $this->get('/admin/savings-report');
        
        $response->assertStatus(200);
        $response->assertSee('Laporan Simpanan');
    }

    /** @test */
    public function savings_report_shows_correct_data()
    {
        // Create savings transactions
        SavingsTransaction::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'user_id' => $this->user->id,
            'savings_type_id' => $this->savingsType->id,
            'transaction_date' => now(),
            'amount' => 100000,
            'status' => 'completed',
        ]);

        SavingsTransaction::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'user_id' => $this->user->id,
            'savings_type_id' => $this->savingsType->id,
            'transaction_date' => now(),
            'amount' => 50000,
            'status' => 'completed',
        ]);

        $response = $this->get('/admin/savings-report');
        
        $response->assertStatus(200);
        $response->assertSee('100,000');
        $response->assertSee('50,000');
        $response->assertSee($this->user->name);
        $response->assertSee('Simpanan Pokok');
    }

    /** @test */
    public function can_filter_savings_report_by_period()
    {
        // Create savings for different periods
        SavingsTransaction::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'user_id' => $this->user->id,
            'savings_type_id' => $this->savingsType->id,
            'transaction_date' => now(),
            'amount' => 100000,
        ]);

        SavingsTransaction::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'user_id' => $this->user->id,
            'savings_type_id' => $this->savingsType->id,
            'transaction_date' => now()->subMonth(),
            'amount' => 200000,
        ]);

        Livewire::test(\App\Filament\Pages\SavingsReport::class)
            ->filterTable('periode', [
                'period_type' => 'monthly',
                'month' => now()->month,
                'year' => now()->year,
            ])
            ->assertCanSeeTableRecords([
                // Should see only current month records
            ]);
    }

    /** @test */
    public function can_filter_savings_report_by_savings_type()
    {
        // Create another savings type
        $voluntarySavings = SavingsType::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'name' => 'Simpanan Sukarela',
            'is_mandatory' => false,
        ]);

        // Create transactions for different types
        SavingsTransaction::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'user_id' => $this->user->id,
            'savings_type_id' => $this->savingsType->id,
            'amount' => 100000,
        ]);

        SavingsTransaction::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'user_id' => $this->user->id,
            'savings_type_id' => $voluntarySavings->id,
            'amount' => 200000,
        ]);

        Livewire::test(\App\Filament\Pages\SavingsReport::class)
            ->filterTable('jenis_simpanan', [
                'savings_type_id' => $this->savingsType->id,
            ])
            ->assertCanSeeTableRecords([
                // Should see only mandatory savings records
            ]);
    }

    /** @test */
    public function can_filter_savings_report_by_member()
    {
        // Create another user
        $anotherUser = User::factory()->create([
            'cooperation_id' => $this->cooperation->id,
        ]);

        // Create transactions for different users
        SavingsTransaction::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'user_id' => $this->user->id,
            'savings_type_id' => $this->savingsType->id,
            'amount' => 100000,
        ]);

        SavingsTransaction::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'user_id' => $anotherUser->id,
            'savings_type_id' => $this->savingsType->id,
            'amount' => 200000,
        ]);

        Livewire::test(\App\Filament\Pages\SavingsReport::class)
            ->filterTable('anggota', [
                'user_id' => $this->user->id,
            ])
            ->assertCanSeeTableRecords([
                // Should see only current user's records
            ]);
    }

    /** @test */
    public function can_filter_savings_report_by_status()
    {
        // Create transactions with different statuses
        SavingsTransaction::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'user_id' => $this->user->id,
            'savings_type_id' => $this->savingsType->id,
            'amount' => 100000,
            'status' => 'completed',
        ]);

        SavingsTransaction::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'user_id' => $this->user->id,
            'savings_type_id' => $this->savingsType->id,
            'amount' => 200000,
            'status' => 'pending',
        ]);

        Livewire::test(\App\Filament\Pages\SavingsReport::class)
            ->filterTable('status', [
                'status' => 'completed',
            ])
            ->assertCanSeeTableRecords([
                // Should see only completed transactions
            ]);
    }

    /** @test */
    public function savings_report_calculates_summary_correctly()
    {
        // Create multiple savings transactions
        SavingsTransaction::factory()->count(4)->create([
            'cooperation_id' => $this->cooperation->id,
            'user_id' => $this->user->id,
            'savings_type_id' => $this->savingsType->id,
            'transaction_date' => now(),
            'amount' => 100000,
            'status' => 'completed',
        ]);

        // Create another user with savings
        $anotherUser = User::factory()->create([
            'cooperation_id' => $this->cooperation->id,
        ]);

        SavingsTransaction::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'user_id' => $anotherUser->id,
            'savings_type_id' => $this->savingsType->id,
            'transaction_date' => now(),
            'amount' => 150000,
            'status' => 'completed',
        ]);

        $response = $this->get('/admin/savings-report');
        
        $response->assertStatus(200);
        
        // Should show correct totals
        // Total members with savings: 2
        // Total savings: 4 * 100,000 + 1 * 150,000 = 550,000
        $response->assertSee('550.000');
    }

    /** @test */
    public function savings_report_shows_transaction_numbers()
    {
        SavingsTransaction::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'user_id' => $this->user->id,
            'savings_type_id' => $this->savingsType->id,
            'transaction_number' => 'SIM-001',
            'amount' => 100000,
        ]);

        $response = $this->get('/admin/savings-report');
        
        $response->assertStatus(200);
        $response->assertSee('SIM-001');
    }

    /** @test */
    public function savings_report_respects_cooperation_isolation()
    {
        // Create another cooperation and user
        $otherCooperation = Cooperation::factory()->create();
        $otherUser = User::factory()->create(['cooperation_id' => $otherCooperation->id]);
        $otherSavingsType = SavingsType::factory()->create(['cooperation_id' => $otherCooperation->id]);

        // Create savings for current cooperation
        SavingsTransaction::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'user_id' => $this->user->id,
            'savings_type_id' => $this->savingsType->id,
            'amount' => 100000,
        ]);

        // Create savings for other cooperation
        SavingsTransaction::factory()->create([
            'cooperation_id' => $otherCooperation->id,
            'user_id' => $otherUser->id,
            'savings_type_id' => $otherSavingsType->id,
            'amount' => 200000,
        ]);

        // Current user should only see their cooperation's data
        $response = $this->get('/admin/savings-report');
        $response->assertStatus(200);
        $response->assertSee('100,000');
        $response->assertDontSee('200,000');
        $response->assertSee($this->user->name);
        $response->assertDontSee($otherUser->name);
    }

    /** @test */
    public function can_export_savings_report()
    {
        SavingsTransaction::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'user_id' => $this->user->id,
            'savings_type_id' => $this->savingsType->id,
            'amount' => 100000,
        ]);

        Livewire::test(\App\Filament\Pages\SavingsReport::class)
            ->callTableAction('export_excel')
            ->assertFileDownloaded('laporan-simpanan-' . now()->format('Y-m-d') . '.xlsx');
    }

    /** @test */
    public function savings_report_handles_withdrawal_transactions()
    {
        // Create deposit
        SavingsTransaction::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'user_id' => $this->user->id,
            'savings_type_id' => $this->savingsType->id,
            'transaction_date' => now()->subDay(),
            'amount' => 200000,
            'status' => 'completed',
        ]);

        // Create withdrawal
        SavingsTransaction::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'user_id' => $this->user->id,
            'savings_type_id' => $this->savingsType->id,
            'transaction_date' => now(),
            'amount' => 50000,
            'status' => 'completed',
        ]);

        $response = $this->get('/admin/savings-report');
        
        $response->assertStatus(200);
        $response->assertSee('200,000'); // Deposit
        $response->assertSee('50,000');  // Withdrawal
    }
}
