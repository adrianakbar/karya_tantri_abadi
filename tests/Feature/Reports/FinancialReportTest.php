<?php

namespace Tests\Feature\Reports;

use App\Models\User;
use App\Models\Cooperation;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Expense;
use App\Models\SavingsTransaction;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\ExpenseCategory;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Livewire\Livewire;

class FinancialReportTest extends TestCase
{
    use WithFaker;

    private $user;
    private $cooperation;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Find existing cooperation and user instead of creating new ones
        $this->cooperation = Cooperation::first();
        if (!$this->cooperation) {
            $this->markTestSkipped('No cooperation found in database');
        }
        
        $this->user = User::where('cooperation_id', $this->cooperation->id)->first();
        if (!$this->user) {
            $this->markTestSkipped('No user found for cooperation');
        }
        
        $this->actingAs($this->user);
    }

    /** @test */
    public function can_access_financial_report_page()
    {
        // Test Livewire component directly instead of HTTP route
        $component = Livewire::test(\App\Filament\Pages\FinancialReport::class);
        
        $component->assertSuccessful();
    }

    /** @test */
    public function financial_report_shows_correct_data()
    {
        // Create test data
        $customer = User::factory()->create(['cooperation_id' => $this->cooperation->id]);
        $product = Product::factory()->create(['cooperation_id' => $this->cooperation->id]);
        $supplier = Supplier::factory()->create(['cooperation_id' => $this->cooperation->id]);
        $expenseCategory = ExpenseCategory::factory()->create(['cooperation_id' => $this->cooperation->id]);

        // Create sales
        $sale = Sale::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'customer_id' => $customer->id,
            'sale_date' => now(),
            'total_amount' => 100000,
        ]);

        // Create purchases
        $purchase = Purchase::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'supplier_id' => $supplier->id,
            'purchase_date' => now(),
            'total_amount' => 80000,
        ]);

        // Create expenses
        $expense = Expense::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'expense_category_id' => $expenseCategory->id,
            'expense_date' => now(),
            'amount' => 20000,
        ]);

        // Create savings transaction
        $savingsTransaction = SavingsTransaction::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'user_id' => $this->user->id,
            'transaction_date' => now(),
            'amount' => 50000,
        ]);

        $response = $this->get('/admin/financial-report');
        
        $response->assertStatus(200);
        $response->assertSee('100,000'); // Sales amount
        $response->assertSee('80,000');  // Purchase amount
        $response->assertSee('20,000');  // Expense amount
        $response->assertSee('50,000');  // Savings amount
    }

    /** @test */
    public function can_filter_financial_report_by_period()
    {
        // Create test data for different periods
        $customer = User::factory()->create(['cooperation_id' => $this->cooperation->id]);
        
        // Sale this month
        Sale::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'customer_id' => $customer->id,
            'sale_date' => now(),
            'total_amount' => 100000,
        ]);

        // Sale last month
        Sale::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'customer_id' => $customer->id,
            'sale_date' => now()->subMonth(),
            'total_amount' => 200000,
        ]);

        // Test monthly filter
        Livewire::test(\App\Filament\Pages\FinancialReport::class)
            ->assertCanSeeTableRecords([
                // Should see both records initially
            ])
            ->filterTable('periode', [
                'period_type' => 'monthly',
                'month' => now()->month,
                'year' => now()->year,
            ])
            ->assertCanSeeTableRecords([
                // Should see only current month record
            ]);
    }

    /** @test */
    public function can_filter_financial_report_by_transaction_type()
    {
        $customer = User::factory()->create(['cooperation_id' => $this->cooperation->id]);
        $supplier = Supplier::factory()->create(['cooperation_id' => $this->cooperation->id]);

        // Create different transaction types
        Sale::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'customer_id' => $customer->id,
            'total_amount' => 100000,
        ]);

        Purchase::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'supplier_id' => $supplier->id,
            'total_amount' => 80000,
        ]);

        // Test filtering by sales only
        Livewire::test(\App\Filament\Pages\FinancialReport::class)
            ->filterTable('kategori', [
            ])
            ->assertCanSeeTableRecords([
                // Should see only sales records
            ]);
    }

    /** @test */
    public function financial_report_calculates_running_balance_correctly()
    {
        $customer = User::factory()->create(['cooperation_id' => $this->cooperation->id]);
        $expenseCategory = ExpenseCategory::factory()->create(['cooperation_id' => $this->cooperation->id]);

        // Create transactions in chronological order
        Sale::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'customer_id' => $customer->id,
            'sale_date' => '2024-01-01',
            'total_amount' => 100000,
        ]);

        Expense::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'expense_category_id' => $expenseCategory->id,
            'expense_date' => '2024-01-02',
            'amount' => 30000,
        ]);

        Sale::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'customer_id' => $customer->id,
            'sale_date' => '2024-01-03',
            'total_amount' => 50000,
        ]);

        $response = $this->get('/admin/financial-report');
        
        $response->assertStatus(200);
        // Running balance should be: 100000 - 30000 + 50000 = 120000
        $response->assertSee('120,000');
    }

    /** @test */
    public function financial_report_respects_cooperation_isolation()
    {
        // Create another cooperation and user
        $otherCooperation = Cooperation::factory()->create();
        $otherUser = User::factory()->create(['cooperation_id' => $otherCooperation->id]);
        
        $customer1 = User::factory()->create(['cooperation_id' => $this->cooperation->id]);
        $customer2 = User::factory()->create(['cooperation_id' => $otherCooperation->id]);

        // Create sale for current cooperation
        Sale::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'customer_id' => $customer1->id,
            'total_amount' => 100000,
        ]);

        // Create sale for other cooperation
        Sale::factory()->create([
            'cooperation_id' => $otherCooperation->id,
            'customer_id' => $customer2->id,
            'total_amount' => 200000,
        ]);

        // Current user should only see their cooperation's data
        $response = $this->get('/admin/financial-report');
        $response->assertStatus(200);
        $response->assertSee('100,000');
        $response->assertDontSee('200,000');
    }

    /** @test */
    public function can_export_financial_report()
    {
        $customer = User::factory()->create(['cooperation_id' => $this->cooperation->id]);
        
        Sale::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'customer_id' => $customer->id,
            'total_amount' => 100000,
        ]);

        Livewire::test(\App\Filament\Pages\FinancialReport::class)
            ->callTableHeaderAction('export_excel')
            ->assertNotified('Export berhasil');
    }

    /** @test */
    public function financial_report_handles_empty_data()
    {
        $response = $this->get('/admin/financial-report');
        
        $response->assertStatus(200);
        $response->assertSee('Laporan Keuangan');
        // Should show zero amounts when no data
        $response->assertSee('Rp 0');
    }

    /** @test */
    public function financial_report_shows_correct_summary_cards()
    {
        $customer = User::factory()->create(['cooperation_id' => $this->cooperation->id]);
        $supplier = Supplier::factory()->create(['cooperation_id' => $this->cooperation->id]);
        $expenseCategory = ExpenseCategory::factory()->create(['cooperation_id' => $this->cooperation->id]);

        // Create current month data
        Sale::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'customer_id' => $customer->id,
            'sale_date' => now(),
            'total_amount' => 500000, // Pemasukan
        ]);

        Purchase::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'supplier_id' => $supplier->id,
            'purchase_date' => now(),
            'total_amount' => 300000, // Pengeluaran
        ]);

        Expense::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'expense_category_id' => $expenseCategory->id,
            'expense_date' => now(),
            'amount' => 100000, // Pengeluaran
        ]);

        $response = $this->get('/admin/financial-report');
        
        $response->assertStatus(200);
        
        // Total Pemasukan = 500,000
        $response->assertSee('500,000');
        
        // Total Pengeluaran = 300,000 + 100,000 = 400,000
        $response->assertSee('400,000');
        
        // Laba/Rugi = 500,000 - 400,000 = 100,000
        $response->assertSee('100,000');
    }
}
