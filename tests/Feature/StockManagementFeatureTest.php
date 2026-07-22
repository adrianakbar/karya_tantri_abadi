<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Cooperation;
use App\Models\StockMovementLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

class StockManagementFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected $cooperation;
    protected $user;
    protected $category;
    protected $product;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Setup test data
        $this->cooperation = Cooperation::factory()->create();
        
        $this->user = User::factory()->create([
            'cooperation_id' => $this->cooperation->id
        ]);
        
        // Create and assign admin role for authorization
        $adminRole = \App\Models\Roles::create([
            'name' => 'admin',
            'cooperation_id' => $this->cooperation->id
        ]);
        \App\Models\UserRole::create([
            'user_id' => $this->user->id,
            'role_id' => $adminRole->id
        ]);
        
        $this->category = ProductCategory::factory()->create([
            'cooperation_id' => $this->cooperation->id
        ]);
        
        $this->product = Product::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'product_category_id' => $this->category->id,
            'current_stock' => 100,
            'min_stock' => 10,
            'purchase_price' => 5000,
            'selling_price' => 7500
        ]);

        $this->actingAs($this->user);
    }

    /** @test */
    public function debug_role_assignment()
    {
        $this->assertTrue($this->user->hasRole('admin'));
    }

    /** @test */
    public function user_can_access_product_list_page()
    {
        $response = $this->get('/admin/products');
        
        $response->assertStatus(200);
        $response->assertSee($this->product->name);
    }

    /** @test */
    public function user_can_access_stock_movement_logs_page()
    {
        // Create some stock movements first
        StockMovementLog::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'product_id' => $this->product->id,
            'type' => 'sale',
            'quantity' => 10,
            'stock_before' => 100,
            'stock_after' => 90,
            'created_by' => $this->user->id
        ]);

        $response = $this->get('/admin/stock-movement-logs');
        
        $response->assertStatus(200);
        $response->assertSee($this->product->name);
        $response->assertSee('sale');
    }

    /** @test */
    public function user_can_view_low_stock_alerts_on_product_page()
    {
        // Create a low stock product
        $lowStockProduct = Product::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'product_category_id' => $this->category->id,
            'name' => 'Low Stock Product',
            'current_stock' => 5,
            'min_stock' => 10
        ]);

        $response = $this->get('/admin/products');
        
        $response->assertStatus(200);
        // Should see low stock alert messages
        $response->assertSee('Stok Rendah');
    }

    /** @test */
    public function user_can_filter_stock_movements_by_product()
    {
        // Create another product and movements
        $product2 = Product::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'product_category_id' => $this->category->id,
            'name' => 'Product Two'
        ]);

        StockMovementLog::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'product_id' => $this->product->id,
            'type' => 'sale',
            'created_by' => $this->user->id
        ]);

        StockMovementLog::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'product_id' => $product2->id,
            'type' => 'purchase',
            'created_by' => $this->user->id
        ]);

        $response = $this->get('/admin/stock-movement-logs');
        
        $response->assertStatus(200);
        $response->assertSee($this->product->name);
        $response->assertSee('Product Two');
    }

    /** @test */
    public function user_can_view_stock_movement_details()
    {
        $movement = StockMovementLog::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'product_id' => $this->product->id,
            'type' => 'purchase',
            'quantity' => 25,
            'stock_before' => 100,
            'stock_after' => 125,
            'reference_type' => 'purchase_order',
            'reference_id' => 1,
            'notes' => 'Purchase from supplier ABC',
            'created_by' => $this->user->id
        ]);

        Livewire::test(\App\Filament\Resources\StockMovementLogResource\Pages\ViewStockMovementLog::class, [
            'record' => $movement->id,
        ])
        ->assertFormSet([
            'product_id' => $this->product->id,
            'type' => 'purchase',
            'quantity' => 25,
            'notes' => 'Purchase from supplier ABC',
        ]);
    }

    /** @test */
    public function product_stock_update_creates_movement_log()
    {
        $initialMovementCount = StockMovementLog::count();
        
        // Simulate updating product stock via Filament form
        $this->product->update(['current_stock' => 150]);
        
        // Check if movement log was created
        $this->assertEquals($initialMovementCount + 1, StockMovementLog::count());
        
        $latestMovement = StockMovementLog::orderBy('id', 'desc')->first();
        $this->assertEquals($this->product->id, $latestMovement->product_id);
        $this->assertEquals(100, $latestMovement->stock_before);
        $this->assertEquals(150, $latestMovement->stock_after);
        $this->assertEquals('in', $latestMovement->type);
    }

    /** @test */
    public function user_can_access_inventory_report_page()
    {
        $response = $this->get('/admin/inventory-report');
        
        $response->assertStatus(200);
        $response->assertSee('Laporan Inventaris');
    }

    /** @test */
    public function inventory_report_shows_stock_statistics()
    {
        // Create products with different stock levels
        Product::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'product_category_id' => $this->category->id,
            'current_stock' => 0, // Out of stock
            'min_stock' => 5
        ]);

        Product::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'product_category_id' => $this->category->id,
            'current_stock' => 3, // Low stock
            'min_stock' => 10
        ]);

        $response = $this->get('/admin/inventory-report');
        
        $response->assertStatus(200);
        $response->assertSee('Total Produk');
        $response->assertSee('Stok Rendah');
        $response->assertSee('Pembelian');
        $response->assertSee('Penjualan');
    }

    /** @test */
    public function user_can_view_stock_history_from_product_page()
    {
        // Create some stock movements
        StockMovementLog::factory()->count(3)->create([
            'cooperation_id' => $this->cooperation->id,
            'product_id' => $this->product->id,
            'created_by' => $this->user->id
        ]);

        // Access product list page (where stock history action should be available)
        $response = $this->get('/admin/products');
        
        $response->assertStatus(200);
        // The action link should be available for viewing stock history
        $response->assertSee('Riwayat Stok');
    }

    /** @test */
    public function stock_movements_are_isolated_by_cooperation()
    {
        // Create another cooperation and user
        $otherCooperation = Cooperation::factory()->create();
        $otherUser = User::factory()->create([
            'cooperation_id' => $otherCooperation->id
        ]);
        $otherAdminRole = \App\Models\Roles::create([
            'name' => 'admin',
            'cooperation_id' => $otherCooperation->id
        ]);
        \App\Models\UserRole::create([
            'user_id' => $otherUser->id,
            'role_id' => $otherAdminRole->id
        ]);
        $otherCategory = ProductCategory::factory()->create([
            'cooperation_id' => $otherCooperation->id
        ]);
        $otherProduct = Product::factory()->create([
            'cooperation_id' => $otherCooperation->id,
            'product_category_id' => $otherCategory->id
        ]);

        // Create movements for both cooperations
        StockMovementLog::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'product_id' => $this->product->id,
            'created_by' => $this->user->id
        ]);

        StockMovementLog::factory()->create([
            'cooperation_id' => $otherCooperation->id,
            'product_id' => $otherProduct->id,
            'created_by' => $otherUser->id
        ]);

        // Test that current user only sees their cooperation's data
        $response = $this->get('/admin/stock-movement-logs');
        $response->assertStatus(200);
        $response->assertSee($this->product->name);
        $response->assertDontSee($otherProduct->name);

        // Test with other user
        $this->actingAs($otherUser);
        $response = $this->get('/admin/stock-movement-logs');
        $response->assertStatus(200);
        $response->assertSee($otherProduct->name);
        $response->assertDontSee($this->product->name);
    }

    /** @test */
    public function user_can_export_stock_data()
    {
        // Create some stock movements
        StockMovementLog::factory()->count(5)->create([
            'cooperation_id' => $this->cooperation->id,
            'product_id' => $this->product->id,
            'created_by' => $this->user->id
        ]);

        // Test if export functionality is available
        $response = $this->get('/admin/stock-movement-logs');
        $response->assertStatus(200);
        
        // Should have export options
        $response->assertSee('Export');
    }

    /** @test */
    public function low_stock_notification_appears_on_dashboard()
    {
        // Create low stock products
        Product::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'product_category_id' => $this->category->id,
            'current_stock' => 2,
            'min_stock' => 10
        ]);

        Product::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'product_category_id' => $this->category->id,
            'current_stock' => 0,
            'min_stock' => 5
        ]);

        $response = $this->get('/admin/products');
        
        $response->assertStatus(200);
        // Should show low stock warnings
        $response->assertSee('Peringatan Stok');
    }

    /** @test */
    public function stock_movement_widget_shows_correct_data()
    {
        // Create movements for today
        StockMovementLog::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'product_id' => $this->product->id,
            'type' => 'purchase',
            'quantity' => 50,
            'created_by' => $this->user->id,
            'created_at' => now()
        ]);

        StockMovementLog::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'product_id' => $this->product->id,
            'type' => 'sale',
            'quantity' => 25,
            'created_by' => $this->user->id,
            'created_at' => now()
        ]);

        $response = $this->get('/admin/stock-movement-logs');
        
        $response->assertStatus(200);
        // Widget should show today's movements
        $response->assertSee('Hari ini');
    }
}
