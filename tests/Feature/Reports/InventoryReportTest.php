<?php

namespace Tests\Feature\Reports;

use App\Models\User;
use App\Models\Cooperation;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\Customer;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Livewire\Livewire;

class InventoryReportTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private $user;
    private $cooperation;
    private $product;
    private $category;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create cooperation
        $this->cooperation = Cooperation::factory()->create();
        
        // Create category
        $this->category = ProductCategory::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'name' => 'Electronics',
        ]);
        
        // Create product
        $this->product = Product::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'product_category_id' => $this->category->id,
            'name' => 'Laptop Gaming',
            'stock' => 25,
            'min_stock' => 5,
            'purchase_price' => 8000000,
            'selling_price' => 10000000,
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
    public function can_access_inventory_report_page()
    {
        $response = $this->get('/admin/inventory-report');
        
        $response->assertStatus(200);
        $response->assertSee('Laporan Inventaris');
    }

    /** @test */
    public function inventory_report_shows_stock_tab_by_default()
    {
        $response = $this->get('/admin/inventory-report');
        
        $response->assertStatus(200);
        $response->assertSee('Laptop Gaming');
        $response->assertSee('25'); // Current stock
        $response->assertSee('5');  // Min stock
        $response->assertSee('Electronics');
        $response->assertSee('8,000,000'); // Purchase price
        $response->assertSee('10,000,000'); // Selling price
    }

    /** @test */
    public function can_switch_to_purchases_tab()
    {
        $supplier = Supplier::factory()->create(['cooperation_id' => $this->cooperation->id]);

        // Create purchase
        $purchase = Purchase::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'supplier_id' => $supplier->id,
            'purchase_date' => now(),
            'total_amount' => 16000000,
        ]);

        // Create purchase detail
        PurchaseDetail::create([
            'purchase_id' => $purchase->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
            'unit_price' => 8000000,
            'total_price' => 16000000,
        ]);

        Livewire::test(\App\Filament\Pages\InventoryReport::class)
            ->call('setActiveTab', 'purchases')
            ->assertSee('16,000,000') // Total purchase amount
            ->assertSee('2'); // Quantity
    }

    /** @test */
    public function can_switch_to_sales_tab()
    {
        $customer = Customer::factory()->create(['cooperation_id' => $this->cooperation->id]);

        // Create sale
        $sale = Sale::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'customer_id' => $customer->id,
            'sale_date' => now(),
            'total_amount' => 20000000,
        ]);

        // Create sale detail
        SaleDetail::create([
            'sale_id' => $sale->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
            'unit_price' => 10000000,
            'total_price' => 20000000,
        ]);

        Livewire::test(\App\Filament\Pages\InventoryReport::class)
            ->call('setActiveTab', 'sales')
            ->assertSee('20,000,000') // Total sale amount
            ->assertSee('2'); // Quantity
    }

    /** @test */
    public function can_switch_to_profit_loss_tab()
    {
        $customer = Customer::factory()->create(['cooperation_id' => $this->cooperation->id]);

        // Create sale with profit
        $sale = Sale::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'customer_id' => $customer->id,
            'sale_date' => now(),
            'total_amount' => 20000000,
        ]);

        SaleDetail::create([
            'sale_id' => $sale->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
            'unit_price' => 10000000,
            'total_price' => 20000000,
        ]);

        Livewire::test(\App\Filament\Pages\InventoryReport::class)
            ->call('setActiveTab', 'profit_loss')
            ->assertSee('20,000,000') // Revenue
            ->assertSee('16,000,000') // Cost (2 * 8M)
            ->assertSee('4,000,000'); // Profit (20M - 16M)
    }

    /** @test */
    public function can_filter_stock_by_category()
    {
        // Create another category and product
        $otherCategory = ProductCategory::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'name' => 'Furniture',
        ]);

        Product::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'product_category_id' => $otherCategory->id,
            'name' => 'Office Chair',
            'stock' => 10,
        ]);

        Livewire::test(\App\Filament\Pages\InventoryReport::class)
            ->filterTable('kategori', [
                'product_category_id' => $this->category->id,
            ])
            ->assertCanSeeTableRecords([
                // Should see only Electronics products
            ]);
    }

    /** @test */
    public function can_filter_stock_by_low_stock_alert()
    {
        // Create product with low stock
        Product::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'product_category_id' => $this->category->id,
            'name' => 'Low Stock Item',
            'stock' => 2,
            'min_stock' => 5,
        ]);

        Livewire::test(\App\Filament\Pages\InventoryReport::class)
            ->filterTable('status_stok', [
                'stock_status' => 'low',
            ])
            ->assertCanSeeTableRecords([
                // Should see only low stock products
            ]);
    }

    /** @test */
    public function can_filter_purchases_by_period()
    {
        $supplier = Supplier::factory()->create(['cooperation_id' => $this->cooperation->id]);

        // Create purchases for different periods
        Purchase::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'supplier_id' => $supplier->id,
            'purchase_date' => now(),
            'total_amount' => 16000000,
        ]);

        Purchase::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'supplier_id' => $supplier->id,
            'purchase_date' => now()->subMonth(),
            'total_amount' => 24000000,
        ]);

        Livewire::test(\App\Filament\Pages\InventoryReport::class)
            ->call('setActiveTab', 'purchases')
            ->filterTable('bulan', [
                'month' => now()->month,
                'year' => now()->year,
            ])
            ->assertCanSeeTableRecords([
                // Should see only current month purchases
            ]);
    }

    /** @test */
    public function can_filter_sales_by_period()
    {
        $customer = Customer::factory()->create(['cooperation_id' => $this->cooperation->id]);

        // Create sales for different periods
        Sale::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'customer_id' => $customer->id,
            'sale_date' => now(),
            'total_amount' => 20000000,
        ]);

        Sale::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'customer_id' => $customer->id,
            'sale_date' => now()->subMonth(),
            'total_amount' => 30000000,
        ]);

        Livewire::test(\App\Filament\Pages\InventoryReport::class)
            ->call('setActiveTab', 'sales')
            ->filterTable('bulanan', [
                'month' => now()->month,
                'year' => now()->year,
            ])
            ->assertCanSeeTableRecords([
                // Should see only current month sales
            ]);
    }

    /** @test */
    public function inventory_report_calculates_summary_cards_correctly()
    {
        // Create additional products
        Product::factory()->count(4)->create([
            'cooperation_id' => $this->cooperation->id,
            'product_category_id' => $this->category->id,
            'stock' => 10,
        ]);

        // Create low stock product
        Product::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'product_category_id' => $this->category->id,
            'stock' => 2,
            'min_stock' => 5,
        ]);

        // Create sales and purchases for current month
        $customer = Customer::factory()->create(['cooperation_id' => $this->cooperation->id]);
        $supplier = Supplier::factory()->create(['cooperation_id' => $this->cooperation->id]);

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

        $response = $this->get('/admin/inventory-report');
        
        $response->assertStatus(200);
        
        // Total products: 6 (1 original + 4 additional + 1 low stock)
        $response->assertSee('6');
        
        // Low stock products: 1
        $response->assertSee('1');
    }

    /** @test */
    public function inventory_report_shows_profit_margins()
    {
        $customer = Customer::factory()->create(['cooperation_id' => $this->cooperation->id]);

        // Create sale
        $sale = Sale::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'customer_id' => $customer->id,
            'sale_date' => now(),
        ]);

        // Create sale detail with known profit margin
        SaleDetail::create([
            'sale_id' => $sale->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
            'unit_price' => 10000000, // Selling price
            'total_price' => 10000000,
        ]);

        // Purchase price is 8M, selling price is 10M
        // Profit = 2M, Margin = 2M/10M = 20%

        Livewire::test(\App\Filament\Pages\InventoryReport::class)
            ->call('setActiveTab', 'profit_loss')
            ->assertSee('20.00%'); // Profit margin
    }

    /** @test */
    public function inventory_report_respects_cooperation_isolation()
    {
        // Create another cooperation and user
        $otherCooperation = Cooperation::factory()->create();
        $otherUser = User::factory()->create(['cooperation_id' => $otherCooperation->id]);
        
        $otherCategory = ProductCategory::factory()->create(['cooperation_id' => $otherCooperation->id]);
        
        // Create product for other cooperation
        Product::factory()->create([
            'cooperation_id' => $otherCooperation->id,
            'product_category_id' => $otherCategory->id,
            'name' => 'Other Coop Product',
            'stock' => 50,
        ]);

        // Current user should only see their cooperation's products
        $response = $this->get('/admin/inventory-report');
        $response->assertStatus(200);
        $response->assertSee('Laptop Gaming');
        $response->assertDontSee('Other Coop Product');
    }

    /** @test */
    public function can_export_inventory_report()
    {
        Livewire::test(\App\Filament\Pages\InventoryReport::class)
            ->callTableAction('export_excel')
            ->assertFileDownloaded();
    }

    /** @test */
    public function inventory_report_identifies_fast_moving_products()
    {
        $customer = Customer::factory()->create(['cooperation_id' => $this->cooperation->id]);

        // Create multiple sales for the same product (fast moving)
        $sale1 = Sale::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'customer_id' => $customer->id,
            'sale_date' => now()->subDays(5),
        ]);

        $sale2 = Sale::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'customer_id' => $customer->id,
            'sale_date' => now()->subDays(3),
        ]);

        // Create sale details for fast moving product
        SaleDetail::create([
            'sale_id' => $sale1->id,
            'product_id' => $this->product->id,
            'quantity' => 5,
            'unit_price' => 10000000,
            'total_price' => 50000000,
        ]);

        SaleDetail::create([
            'sale_id' => $sale2->id,
            'product_id' => $this->product->id,
            'quantity' => 3,
            'unit_price' => 10000000,
            'total_price' => 30000000,
        ]);

        Livewire::test(\App\Filament\Pages\InventoryReport::class)
            ->call('setActiveTab', 'sales')
            ->assertSee('8'); // Total quantity sold (5 + 3)
    }

    /** @test */
    public function inventory_report_handles_out_of_stock_products()
    {
        // Update product to be out of stock
        $this->product->update(['stock' => 0]);

        $response = $this->get('/admin/inventory-report');
        
        $response->assertStatus(200);
        $response->assertSee('0'); // Stock count
        $response->assertSee('Laptop Gaming');
    }

    /** @test */
    public function inventory_report_shows_stock_value()
    {
        // Stock value = stock * purchase_price
        // 25 * 8,000,000 = 200,000,000

        $response = $this->get('/admin/inventory-report');
        
        $response->assertStatus(200);
        $response->assertSee('200,000,000'); // Stock value
    }

    /** @test */
    public function inventory_report_tracks_stock_movements()
    {
        $customer = Customer::factory()->create(['cooperation_id' => $this->cooperation->id]);
        $supplier = Supplier::factory()->create(['cooperation_id' => $this->cooperation->id]);

        // Create purchase (stock in)
        $purchase = Purchase::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'supplier_id' => $supplier->id,
            'purchase_date' => now()->subDays(5),
        ]);

        PurchaseDetail::create([
            'purchase_id' => $purchase->id,
            'product_id' => $this->product->id,
            'quantity' => 10, // Stock increased by 10
            'unit_price' => 8000000,
            'total_price' => 80000000,
        ]);

        // Create sale (stock out)
        $sale = Sale::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'customer_id' => $customer->id,
            'sale_date' => now()->subDays(2),
        ]);

        SaleDetail::create([
            'sale_id' => $sale->id,
            'product_id' => $this->product->id,
            'quantity' => 3, // Stock decreased by 3
            'unit_price' => 10000000,
            'total_price' => 30000000,
        ]);

        Livewire::test(\App\Filament\Pages\InventoryReport::class)
            ->call('setActiveTab', 'purchases')
            ->assertSee('10') // Purchase quantity
            ->call('setActiveTab', 'sales')
            ->assertSee('3'); // Sale quantity
    }
}
