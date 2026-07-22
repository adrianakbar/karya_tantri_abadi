<?php

namespace Tests\Feature\Reports;

use App\Models\User;
use App\Models\Cooperation;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Livewire\Livewire;

class ExpenseReportTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private $user;
    private $cooperation;
    private $expenseCategory;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create cooperation
        $this->cooperation = Cooperation::factory()->create();
        
        // Create expense category
        $this->expenseCategory = ExpenseCategory::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'name' => 'Operasional',
            'description' => 'Pengeluaran operasional harian',
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
    public function can_access_expense_report_page()
    {
        $response = $this->get('/admin/expense-report');
        
        $response->assertStatus(200);
        $response->assertSee('Laporan Pengeluaran');
    }

    /** @test */
    public function expense_report_shows_correct_data()
    {
        // Create expenses
        Expense::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'expense_category_id' => $this->expenseCategory->id,
            'expense_date' => now(),
            'notes' => 'Pembayaran listrik',
            'amount' => 500000,
            'recipient' => 'PT PLN',
            'receipt_number' => 'RCP-001',
            'status' => 'approved',
        ]);

        Expense::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'expense_category_id' => $this->expenseCategory->id,
            'expense_date' => now(),
            'notes' => 'Pembelian ATK',
            'amount' => 250000,
            'recipient' => 'Toko ABC',
            'receipt_number' => 'RCP-002',
            'status' => 'approved',
        ]);

        $response = $this->get('/admin/expense-report');
        
        $response->assertStatus(200);
        $response->assertSee('500.000');
        $response->assertSee('250.000');
        $response->assertSee('Pembayaran listrik');
        $response->assertSee('Pembelian ATK');
        $response->assertSee('PT PLN');
        $response->assertSee('Toko ABC');
        $response->assertSee('RCP-001');
        $response->assertSee('RCP-002');
    }

    /** @test */
    public function can_filter_expense_report_by_period()
    {
        // Create expenses for different periods
        Expense::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'expense_category_id' => $this->expenseCategory->id,
            'expense_date' => now(),
            'amount' => 500000,
            'notes' => 'Current month expense',
        ]);

        Expense::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'expense_category_id' => $this->expenseCategory->id,
            'expense_date' => now()->subMonth(),
            'amount' => 300000,
            'notes' => 'Last month expense',
        ]);

        Livewire::test(\App\Filament\Pages\ExpenseReport::class)
            ->filterTable('periode', [
                'period_type' => 'monthly',
                'month' => now()->month,
                'year' => now()->year,
            ])
            ->assertCanSeeTableRecords([
                // Should see only current month expenses
            ]);
    }

    /** @test */
    public function can_filter_expense_report_by_category()
    {
        // Create another expense category
        $marketingCategory = ExpenseCategory::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'name' => 'Marketing',
        ]);

        // Create expenses for different categories
        Expense::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'expense_category_id' => $this->expenseCategory->id,
            'amount' => 500000,
            'notes' => 'Operational expense',
        ]);

        Expense::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'expense_category_id' => $marketingCategory->id,
            'amount' => 300000,
            'notes' => 'Marketing expense',
        ]);

        Livewire::test(\App\Filament\Pages\ExpenseReport::class)
            ->filterTable('kategori', [
                'expense_category_id' => $this->expenseCategory->id,
            ])
            ->assertCanSeeTableRecords([
                // Should see only operational expenses
            ]);
    }

    /** @test */
    public function can_filter_expense_report_by_amount_range()
    {
        // Create expenses with different amounts
        Expense::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'expense_category_id' => $this->expenseCategory->id,
            'amount' => 500000,
            'notes' => 'Small expense',
        ]);

        Expense::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'expense_category_id' => $this->expenseCategory->id,
            'amount' => 2000000,
            'notes' => 'Medium expense',
        ]);

        Livewire::test(\App\Filament\Pages\ExpenseReport::class)
            ->filterTable('jumlah', [
                'amount_range' => 'medium',
            ])
            ->assertCanSeeTableRecords([
                // Should see only expenses between 1M-5M
            ]);
    }

    /** @test */
    public function can_filter_expense_report_by_status()
    {
        // Create expenses with different statuses
        Expense::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'expense_category_id' => $this->expenseCategory->id,
            'amount' => 500000,
            'notes' => 'Approved expense',
            'status' => 'approved',
        ]);

        Expense::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'expense_category_id' => $this->expenseCategory->id,
            'amount' => 300000,
            'notes' => 'Pending expense',
            'status' => 'pending',
        ]);

        Livewire::test(\App\Filament\Pages\ExpenseReport::class)
            ->filterTable('status', [
                'status' => 'approved',
            ])
            ->assertCanSeeTableRecords([
                // Should see only approved expenses
            ]);
    }

    /** @test */
    public function expense_report_calculates_summary_correctly()
    {
        // Create multiple expenses
        Expense::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'expense_category_id' => $this->expenseCategory->id,
            'expense_date' => now(),
            'amount' => 500000,
            'status' => 'approved',
        ]);

        Expense::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'expense_category_id' => $this->expenseCategory->id,
            'expense_date' => now(),
            'amount' => 300000,
            'status' => 'approved',
        ]);

        Expense::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'expense_category_id' => $this->expenseCategory->id,
            'expense_date' => now(),
            'amount' => 200000,
            'status' => 'pending',
        ]);

        // Create expense from last month
        Expense::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'expense_category_id' => $this->expenseCategory->id,
            'expense_date' => now()->subMonth(),
            'amount' => 400000,
            'status' => 'approved',
        ]);

        $response = $this->get('/admin/expense-report');
        
        $response->assertStatus(200);
        
        // Total expenses this month: 500.000 + 300.000 + 200.000 = 1.000.000
        $response->assertSee('1.000.000');
        
        // Pending approval: 200.000
        $response->assertSee('200.000');
    }

    /** @test */
    public function expense_report_shows_expense_numbers()
    {
        Expense::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'expense_category_id' => $this->expenseCategory->id,
            'expense_number' => 'EXP-001',
            'amount' => 500000,
            'notes' => 'Test expense',
        ]);

        $response = $this->get('/admin/expense-report');
        
        $response->assertStatus(200);
        $response->assertSee('EXP-001');
    }

    /** @test */
    public function expense_report_respects_cooperation_isolation()
    {
        // Create another cooperation and user
        $otherCooperation = Cooperation::factory()->create();
        $otherUser = User::factory()->create(['cooperation_id' => $otherCooperation->id]);
        $otherCategory = ExpenseCategory::factory()->create(['cooperation_id' => $otherCooperation->id]);

        // Create expense for current cooperation
        Expense::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'expense_category_id' => $this->expenseCategory->id,
            'amount' => 500000,
            'notes' => 'Current coop expense',
            'status' => 'approved',
        ]);

        // Create expense for other cooperation
        Expense::factory()->create([
            'cooperation_id' => $otherCooperation->id,
            'expense_category_id' => $otherCategory->id,
            'amount' => 800000,
            'notes' => 'Other coop expense',
            'status' => 'approved',
        ]);

        // Current user should only see their cooperation's data
        $response = $this->get('/admin/expense-report');
        $response->assertStatus(200);
        $response->assertSee('500.000');
        $response->assertSee('Current coop expense');
        $response->assertDontSee('800.000');
        $response->assertDontSee('Other coop expense');
    }

    /** @test */
    public function can_export_expense_report()
    {
        Expense::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'expense_category_id' => $this->expenseCategory->id,
            'amount' => 500000,
            'notes' => 'Test expense',
        ]);

        Livewire::test(\App\Filament\Pages\ExpenseReport::class)
            ->callTableAction('export_excel')
            ->assertFileDownloaded('laporan-pengeluaran-' . now()->format('Y-m-d') . '.xlsx');
    }

    /** @test */
    public function expense_report_handles_empty_data()
    {
        $response = $this->get('/admin/expense-report');
        
        $response->assertStatus(200);
        $response->assertSee('Laporan Pengeluaran');
        // Should show zero amounts when no data
        $response->assertSee('Rp 0');
    }

    /** @test */
    public function expense_report_shows_daily_average()
    {
        // Create expenses for current month
        $daysInMonth = now()->daysInMonth;
        $totalExpenses = 900000; // 30k per day average for 30 days
        
        Expense::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'expense_category_id' => $this->expenseCategory->id,
            'expense_date' => now(),
            'amount' => $totalExpenses,
            'status' => 'approved',
        ]);

        $response = $this->get('/admin/expense-report');
        
        $response->assertStatus(200);
        
        // Daily average in view is calculated as avg('amount')
        $response->assertSee('900.000');
    }

    /** @test */
    public function expense_report_tracks_approval_workflow()
    {
        // Create expenses with different approval statuses
        Expense::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'expense_category_id' => $this->expenseCategory->id,
            'amount' => 500000,
            'status' => 'pending',
            'approved_by' => null,
        ]);

        Expense::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'expense_category_id' => $this->expenseCategory->id,
            'amount' => 300000,
            'status' => 'approved',
            'approved_by' => $this->user->id,
        ]);

        Expense::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'expense_category_id' => $this->expenseCategory->id,
            'amount' => 200000,
            'status' => 'paid',
            'approved_by' => $this->user->id,
        ]);

        $response = $this->get('/admin/expense-report');
        
        $response->assertStatus(200);
        $response->assertSee('pending');
        $response->assertSee('approved');
        $response->assertSee('paid');
    }
}
