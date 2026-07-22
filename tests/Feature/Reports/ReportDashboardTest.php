<?php

namespace Tests\Feature\Reports;

use App\Models\User;
use App\Models\Cooperation;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Expense;
use App\Models\SavingsTransaction;
use App\Models\Loan;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\ExpenseCategory;
use App\Models\ShuDistribution;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ReportDashboardTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private $user;
    private $cooperation;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create cooperation
        $this->cooperation = Cooperation::factory()->create();
        
        // Create user
        $this->user = User::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'email_verified_at' => now(),
        ]);
        
        $adminRole = \App\Models\Roles::create([
            'name' => 'admin',
            'cooperation_id' => $this->cooperation->id
        ]);
        \App\Models\UserRole::create([
            'user_id' => $this->user->id,
            'role_id' => $adminRole->id
        ]);
        
        $this->actingAs($this->user);
    }

    /** @test */
    public function can_access_report_dashboard_page()
    {
        $response = $this->get('/admin/report-dashboard');
        
        $response->assertStatus(200);
        $response->assertSee('Dashboard Laporan Karya Tantri Abadi');
    }

    /** @test */
    public function dashboard_shows_financial_summary()
    {
        $customer = Customer::factory()->create(['cooperation_id' => $this->cooperation->id]);
        $supplier = Supplier::factory()->create(['cooperation_id' => $this->cooperation->id]);
        $expenseCategory = ExpenseCategory::factory()->create(['cooperation_id' => $this->cooperation->id]);

        // Create current month financial data
        Sale::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'customer_id' => $customer->id,
            'sale_date' => now(),
            'total_amount' => 5000000,
        ]);

        Purchase::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'supplier_id' => $supplier->id,
            'purchase_date' => now(),
            'total_amount' => 3000000,
        ]);

        Expense::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'expense_category_id' => $expenseCategory->id,
            'expense_date' => now(),
            'amount' => 500000,
        ]);

        $response = $this->get('/admin/report-dashboard');
        
        $response->assertStatus(200);
        $response->assertSee('5,000,000'); // Monthly revenue
        $response->assertSee('3,500,000'); // Monthly expenses (3M + 0.5M)
    }

    /** @test */
    public function dashboard_shows_member_statistics()
    {
        // Create additional members
        User::factory()->count(4)->create([
            'cooperation_id' => $this->cooperation->id,
        ]);

        $response = $this->get('/admin/report-dashboard');
        
        $response->assertStatus(200);
        // Total members: 5 (1 original + 4 additional)
        $response->assertSee('5');
    }

    /** @test */
    public function dashboard_shows_savings_summary()
    {
        // Create savings transactions
        SavingsTransaction::factory()->count(3)->create([
            'cooperation_id' => $this->cooperation->id,
            'user_id' => $this->user->id,
            'transaction_date' => now(),
            'amount' => 1000000,
            'status' => 'completed',
        ]);

        $response = $this->get('/admin/report-dashboard');
        
        $response->assertStatus(200);
        $response->assertSee('3,000,000'); // Total savings
    }

    /** @test */
    public function dashboard_shows_loan_statistics()
    {
        // Create loans
        Loan::factory()->count(2)->create([
            'cooperation_id' => $this->cooperation->id,
            'user_id' => $this->user->id,
            'amount' => 5000000,
            'status' => 'active',
        ]);

        Loan::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'user_id' => $this->user->id,
            'amount' => 3000000,
            'status' => 'completed',
        ]);

        $response = $this->get('/admin/report-dashboard');
        
        $response->assertStatus(200);
        // Active loans: 2
        // Total outstanding: 10,000,000 (2 * 5M)
        $response->assertSee('10,000,000');
    }

    /** @test */
    public function dashboard_shows_shu_summary()
    {
        // Create SHU distribution for last year
        ShuDistribution::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'year' => now()->year - 1,
            'total_shu' => 2500000,
            'status' => 'distributed',
        ]);

        // Create current year revenue and expenses for projection
        $customer = Customer::factory()->create(['cooperation_id' => $this->cooperation->id]);
        $supplier = Supplier::factory()->create(['cooperation_id' => $this->cooperation->id]);

        Sale::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'customer_id' => $customer->id,
            'sale_date' => now(),
            'total_amount' => 8000000,
        ]);

        Purchase::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'supplier_id' => $supplier->id,
            'purchase_date' => now(),
            'total_amount' => 5000000,
        ]);

        $response = $this->get('/admin/report-dashboard');
        
        $response->assertStatus(200);
        $response->assertSee('2,500,000'); // SHU last year
        $response->assertSee('3,000,000'); // Projected SHU this year (8M - 5M)
    }

    /** @test */
    public function dashboard_shows_inventory_alerts()
    {
        // Create products with different stock levels
        Product::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'name' => 'Normal Stock Product',
            'stock' => 25,
            'min_stock' => 5,
        ]);

        Product::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'name' => 'Low Stock Product',
            'stock' => 3,
            'min_stock' => 5,
        ]);

        Product::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'name' => 'Out of Stock Product',
            'stock' => 0,
            'min_stock' => 5,
        ]);

        $response = $this->get('/admin/report-dashboard');
        
        $response->assertStatus(200);
        $response->assertSee('Low Stock Product');
        $response->assertSee('Out of Stock Product');
        // Should show low stock alert count
        $response->assertSee('2'); // 2 products with low/no stock
    }

    /** @test */
    public function dashboard_shows_recent_transactions()
    {
        $customer = Customer::factory()->create(['cooperation_id' => $this->cooperation->id]);
        $supplier = Supplier::factory()->create(['cooperation_id' => $this->cooperation->id]);

        // Create recent transactions
        Sale::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'customer_id' => $customer->id,
            'sale_date' => now(),
            'sale_number' => 'SALE-001',
            'total_amount' => 2000000,
        ]);

        Purchase::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'supplier_id' => $supplier->id,
            'purchase_date' => now(),
            'purchase_number' => 'PUR-001',
            'total_amount' => 1500000,
        ]);

        SavingsTransaction::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'user_id' => $this->user->id,
            'transaction_date' => now(),
            'transaction_number' => 'SAV-001',
            'amount' => 500000,
        ]);

        $response = $this->get('/admin/report-dashboard');
        
        $response->assertStatus(200);
        $response->assertSee('SALE-001');
        $response->assertSee('PUR-001');
        $response->assertSee('SAV-001');
        $response->assertSee('2,000,000');
        $response->assertSee('1,500,000');
        $response->assertSee('500,000');
    }

    /** @test */
    public function dashboard_provides_quick_access_to_reports()
    {
        $response = $this->get('/admin/report-dashboard');
        
        $response->assertStatus(200);
        
        // Should have links to detailed reports
        $response->assertSee('/admin/financial-report');
        $response->assertSee('/admin/savings-report');
        $response->assertSee('/admin/loan-report');
        $response->assertSee('/admin/expense-report');
        $response->assertSee('/admin/shu-report');
        $response->assertSee('/admin/inventory-report');
    }

    /** @test */
    public function dashboard_shows_performance_indicators()
    {
        $customer = Customer::factory()->create(['cooperation_id' => $this->cooperation->id]);

        // Create sales for different periods to show growth
        Sale::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'customer_id' => $customer->id,
            'sale_date' => now(),
            'total_amount' => 6000000, // This month
        ]);

        Sale::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'customer_id' => $customer->id,
            'sale_date' => now()->subMonth(),
            'total_amount' => 5000000, // Last month
        ]);

        $response = $this->get('/admin/report-dashboard');
        
        $response->assertStatus(200);
        $response->assertSee('6,000,000'); // Current month revenue
        // Should show growth percentage or comparison
    }

    /** @test */
    public function dashboard_respects_cooperation_isolation()
    {
        // Create another cooperation and data
        $otherCooperation = Cooperation::factory()->create();
        $otherUser = User::factory()->create(['cooperation_id' => $otherCooperation->id]);
        $otherCustomer = Customer::factory()->create(['cooperation_id' => $otherCooperation->id]);

        // Create sale for current cooperation
        Sale::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'customer_id' => Customer::factory()->create(['cooperation_id' => $this->cooperation->id])->id,
            'sale_date' => now(),
            'total_amount' => 3000000,
        ]);

        // Create sale for other cooperation
        Sale::factory()->create([
            'cooperation_id' => $otherCooperation->id,
            'customer_id' => $otherCustomer->id,
            'sale_date' => now(),
            'total_amount' => 8000000,
        ]);

        // Current user should only see their cooperation's data
        $response = $this->get('/admin/report-dashboard');
        $response->assertStatus(200);
        $response->assertSee('3,000,000');
        $response->assertDontSee('8,000,000');
    }

    /** @test */
    public function dashboard_handles_empty_data_gracefully()
    {
        $response = $this->get('/admin/report-dashboard');
        
        $response->assertStatus(200);
        $response->assertSee('Dashboard Laporan Karya Tantri Abadi');
        // Should show zero values when no data
        $response->assertSee('Rp 0');
    }

    /** @test */
    public function dashboard_shows_yearly_comparison()
    {
        $customer = Customer::factory()->create(['cooperation_id' => $this->cooperation->id]);

        // Create sales for current year
        Sale::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'customer_id' => $customer->id,
            'sale_date' => now(),
            'total_amount' => 12000000,
        ]);

        // Create sales for last year
        Sale::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'customer_id' => $customer->id,
            'sale_date' => now()->subYear(),
            'total_amount' => 10000000,
        ]);

        $response = $this->get('/admin/report-dashboard');
        
        $response->assertStatus(200);
        $response->assertSee('12,000,000'); // Current year
        // Should show year-over-year growth
    }

    /** @test */
    public function dashboard_displays_cooperative_health_metrics()
    {
        // Create comprehensive data for health metrics
        $customer = Customer::factory()->create(['cooperation_id' => $this->cooperation->id]);
        $supplier = Supplier::factory()->create(['cooperation_id' => $this->cooperation->id]);

        // Revenue
        Sale::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'customer_id' => $customer->id,
            'sale_date' => now(),
            'total_amount' => 15000000,
        ]);

        // Expenses
        Purchase::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'supplier_id' => $supplier->id,
            'purchase_date' => now(),
            'total_amount' => 8000000,
        ]);

        // Active members with savings
        SavingsTransaction::factory()->count(5)->create([
            'cooperation_id' => $this->cooperation->id,
            'user_id' => $this->user->id,
            'transaction_date' => now(),
            'amount' => 1000000,
            'status' => 'completed',
        ]);

        $response = $this->get('/admin/report-dashboard');
        
        $response->assertStatus(200);
        
        // Financial health: Revenue > Expenses
        $response->assertSee('15,000,000'); // Revenue
        $response->assertSee('8,000,000');  // Expenses
        
        // Member engagement: Active savers
        $response->assertSee('5,000,000'); // Total savings
    }
}
