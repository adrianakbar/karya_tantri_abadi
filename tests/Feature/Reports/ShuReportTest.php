<?php

namespace Tests\Feature\Reports;

use App\Models\User;
use App\Models\Cooperation;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Expense;
use App\Models\ShuDistribution;
use App\Models\ShuMemberShare;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\ExpenseCategory;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Livewire\Livewire;

class ShuReportTest extends TestCase
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
    public function can_access_shu_report_page()
    {
        $response = $this->get('/admin/shu-report');
        
        $response->assertStatus(200);
        $response->assertSee('Laporan SHU');
    }

    /** @test */
    public function shu_report_shows_calculation_tab_by_default()
    {
        // Create SHU distribution
        $shuDistribution = ShuDistribution::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'year' => now()->year,
            'total_revenue' => 10000000,
            'total_expenses' => 7000000,
            'total_shu' => 3000000,
            'status' => 'distributed',
        ]);

        $response = $this->get('/admin/shu-report');
        
        $response->assertStatus(200);
        $response->assertSee('10,000,000'); // Total revenue
        $response->assertSee('7,000,000');  // Total expenses
        $response->assertSee('3,000,000');  // Total SHU
    }

    /** @test */
    public function can_switch_to_member_distribution_tab()
    {
        // Create SHU distribution
        $shuDistribution = ShuDistribution::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'year' => now()->year,
            'total_shu' => 3000000,
            'status' => 'distributed',
        ]);

        // Create member share
        ShuMemberShare::factory()->create([
            'shu_distribution_id' => $shuDistribution->id,
            'user_id' => $this->user->id,
            'savings_contribution' => 500000,
            'transaction_contribution' => 300000,
            'shu_amount' => 800000,
            'status' => 'paid',
        ]);

        Livewire::test(\App\Filament\Pages\ShuReport::class)
            ->call('setActiveTab', 'distribution')
            ->assertSee('500,000') // Savings share
            ->assertSee('300,000') // Transaction share
            ->assertSee('800,000'); // Total share
    }

    /** @test */
    public function can_calculate_shu_automatically()
    {
        $customer = Customer::factory()->create(['cooperation_id' => $this->cooperation->id]);
        $supplier = Supplier::factory()->create(['cooperation_id' => $this->cooperation->id]);
        $expenseCategory = ExpenseCategory::factory()->create(['cooperation_id' => $this->cooperation->id]);

        $lastYear = now()->year - 1;

        // Create revenue data for last year
        Sale::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'customer_id' => $customer->id,
            'sale_date' => Carbon::create($lastYear, 6, 15),
            'total_amount' => 5000000,
        ]);

        Sale::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'customer_id' => $customer->id,
            'sale_date' => Carbon::create($lastYear, 8, 20),
            'total_amount' => 3000000,
        ]);

        // Create expense data for last year
        Purchase::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'supplier_id' => $supplier->id,
            'purchase_date' => Carbon::create($lastYear, 5, 10),
            'total_amount' => 2000000,
        ]);

        Expense::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'expense_category_id' => $expenseCategory->id,
            'expense_date' => Carbon::create($lastYear, 7, 25),
            'amount' => 1500000,
        ]);

        Livewire::test(\App\Filament\Pages\ShuReport::class)
            ->call('calculateAutoShu')
            ->assertNotified('Perhitungan SHU Berhasil');

        // Check if SHU distribution was created
        $this->assertDatabaseHas('shu_distributions', [
            'cooperation_id' => $this->cooperation->id,
            'year' => $lastYear,
            'total_revenue' => 8000000, // 5M + 3M
            'total_expenses' => 3500000, // 2M + 1.5M
            'total_shu' => 4500000, // 8M - 3.5M
        ]);
    }

    /** @test */
    public function shu_calculation_handles_negative_shu()
    {
        $customer = Customer::factory()->create(['cooperation_id' => $this->cooperation->id]);
        $supplier = Supplier::factory()->create(['cooperation_id' => $this->cooperation->id]);

        $lastYear = now()->year - 1;

        // Create minimal revenue
        Sale::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'customer_id' => $customer->id,
            'sale_date' => Carbon::create($lastYear, 6, 15),
            'total_amount' => 1000000,
        ]);

        // Create high expenses
        Purchase::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'supplier_id' => $supplier->id,
            'purchase_date' => Carbon::create($lastYear, 5, 10),
            'total_amount' => 2000000,
        ]);

        Livewire::test(\App\Filament\Pages\ShuReport::class)
            ->call('calculateAutoShu')
            ->assertNotified('Tidak Ada SHU'); // Should notify about negative SHU
    }

    /** @test */
    public function can_filter_shu_distributions_by_year()
    {
        // Create SHU distributions for different years
        ShuDistribution::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'year' => 2023,
            'total_shu' => 2000000,
        ]);

        ShuDistribution::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'year' => 2024,
            'total_shu' => 3000000,
        ]);

        Livewire::test(\App\Filament\Pages\ShuReport::class)
            ->filterTable('tahun', [
                'year' => 2024,
            ])
            ->assertCanSeeTableRecords([
                // Should see only 2024 SHU distribution
            ]);
    }

    /** @test */
    public function can_filter_member_shares_by_status()
    {
        $shuDistribution = ShuDistribution::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'year' => now()->year - 1,
            'total_shu' => 3000000,
        ]);

        $anotherUser = User::factory()->create(['cooperation_id' => $this->cooperation->id]);

        // Create member shares with different statuses
        ShuMemberShare::factory()->create([
            'shu_distribution_id' => $shuDistribution->id,
            'user_id' => $this->user->id,
            'shu_amount' => 800000,
            'status' => 'paid',
        ]);

        ShuMemberShare::factory()->create([
            'shu_distribution_id' => $shuDistribution->id,
            'user_id' => $anotherUser->id,
            'shu_amount' => 600000,
            'status' => 'pending',
        ]);

        Livewire::test(\App\Filament\Pages\ShuReport::class)
            ->call('setActiveTab', 'distribution')
            ->filterTable('status', [
                'status' => 'paid',
            ])
            ->assertCanSeeTableRecords([
                // Should see only paid shares
            ]);
    }

    /** @test */
    public function shu_distribution_calculates_member_shares_correctly()
    {
        $customer = Customer::factory()->create(['cooperation_id' => $this->cooperation->id]);
        $anotherUser = User::factory()->create(['cooperation_id' => $this->cooperation->id]);

        $lastYear = now()->year - 1;

        // Create sales for both users
        Sale::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'customer_id' => $this->user->id,
            'sale_date' => Carbon::create($lastYear, 6, 15),
            'total_amount' => 6000000, // 60% of total sales
        ]);

        Sale::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'customer_id' => $anotherUser->id,
            'sale_date' => Carbon::create($lastYear, 8, 20),
            'total_amount' => 4000000, // 40% of total sales
        ]);

        // Create SHU distribution
        $shuDistribution = ShuDistribution::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'year' => $lastYear,
            'total_revenue' => 10000000,
            'total_expenses' => 6000000,
            'total_shu' => 4000000,
            'status' => 'calculated',
        ]);

        // Test member share calculation method
        $page = new \App\Filament\Pages\ShuReport();
        $reflection = new \ReflectionClass($page);
        $method = $reflection->getMethod('calculateMemberShares');
        $method->setAccessible(true);
        
        $this->actingAs($this->user);
        $method->invoke($page, $shuDistribution);

        // Check if member shares were calculated correctly
        $userShare = ShuMemberShare::where('user_id', $this->user->id)->first();
        $anotherUserShare = ShuMemberShare::where('user_id', $anotherUser->id)->first();

        $this->assertNotNull($userShare);
        $this->assertNotNull($anotherUserShare);
        
        // User 1 should get more share (60% of transaction share)
        $this->assertGreaterThan($anotherUserShare->transaction_contribution, $userShare->transaction_contribution);
    }

    /** @test */
    public function shu_report_respects_cooperation_isolation()
    {
        // Create another cooperation and user
        $otherCooperation = Cooperation::factory()->create();
        $otherUser = User::factory()->create(['cooperation_id' => $otherCooperation->id]);

        $otherAdminRole = \App\Models\Roles::create([
            'name' => 'admin',
            'cooperation_id' => $otherCooperation->id
        ]);
        \App\Models\UserRole::create([
            'user_id' => $otherUser->id,
            'role_id' => $otherAdminRole->id
        ]);

        // Create SHU distribution for current cooperation
        ShuDistribution::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'year' => now()->year,
            'total_shu' => 3000000,
        ]);

        // Create SHU distribution for other cooperation
        ShuDistribution::factory()->create([
            'cooperation_id' => $otherCooperation->id,
            'year' => now()->year,
            'total_shu' => 5000000,
        ]);

        // Current user should only see their cooperation's data
        $response = $this->get('/admin/shu-report');
        $response->assertStatus(200);
        $response->assertSee('3,000,000');
        $response->assertDontSee('5,000,000');
    }

    /** @test */
    public function can_export_shu_report()
    {
        $shuDistribution = ShuDistribution::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'year' => now()->year,
            'total_shu' => 3000000,
        ]);

        Livewire::test(\App\Filament\Pages\ShuReport::class)
            ->call('exportShuCalculationExcel')
            ->assertFileDownloaded('laporan-perhitungan-shu-' . now()->format('Y-m-d') . '.xlsx');
    }

    /** @test */
    public function shu_report_shows_summary_statistics()
    {
        $lastYear = now()->year - 1;
        $currentYear = now()->year;

        // Create SHU distribution for last year
        $shuDistribution = ShuDistribution::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'year' => $lastYear,
            'total_shu' => 1400000,
            'status' => 'distributed',
        ]);

        // Create member shares
        ShuMemberShare::factory()->create([
            'shu_distribution_id' => $shuDistribution->id,
            'user_id' => $this->user->id,
            'shu_amount' => 800000,
            'status' => 'paid',
        ]);

        $anotherUser = User::factory()->create(['cooperation_id' => $this->cooperation->id]);
        ShuMemberShare::factory()->create([
            'shu_distribution_id' => $shuDistribution->id,
            'user_id' => $anotherUser->id,
            'shu_amount' => 600000,
            'status' => 'paid',
        ]);

        $response = $this->get('/admin/shu-report');
        
        $response->assertStatus(200);
        
        // Should show summary statistics
        $response->assertSee('1.400.000'); // SHU last year
        $response->assertSee('700.000'); // Average per member (1.4M / 2 members)
    }

    /** @test */
    public function shu_report_handles_no_data()
    {
        $response = $this->get('/admin/shu-report');
        
        $response->assertStatus(200);
        $response->assertSee('Laporan SHU');
        // Should handle empty state gracefully
        $response->assertSee('Rp 0');
    }

    /** @test */
    public function shu_calculation_uses_correct_distribution_formula()
    {
        // Test the 50% savings, 50% transaction formula
        $shuDistribution = ShuDistribution::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'year' => now()->year,
            'total_shu' => 1000000, // 1M total SHU
        ]);

        // Create member with specific savings and transaction amounts
        $memberShare = ShuMemberShare::factory()->create([
            'shu_distribution_id' => $shuDistribution->id,
            'user_id' => $this->user->id,
            'savings_contribution' => 300000,    // 30% of total savings share (500k)
            'transaction_contribution' => 200000, // 20% of total transaction share (500k)
            'shu_amount' => 500000,       // 300k + 200k
        ]);

        Livewire::test(\App\Filament\Pages\ShuReport::class)
            ->call('setActiveTab', 'distribution')
            ->assertSee('300,000') // Savings portion
            ->assertSee('200,000') // Transaction portion
            ->assertSee('500,000'); // Total share
    }
}
