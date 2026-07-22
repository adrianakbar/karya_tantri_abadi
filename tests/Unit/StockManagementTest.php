<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Cooperation;
use App\Models\StockMovementLog;
use App\Models\User;
use App\Services\StockMovementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class StockManagementTest extends TestCase
{
    use RefreshDatabase, WithFaker;

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
    public function it_can_check_low_stock_products()
    {
        // Create products with different stock levels
        $lowStockProduct = Product::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'product_category_id' => $this->category->id,
            'current_stock' => 5,
            'min_stock' => 10
        ]);

        $outOfStockProduct = Product::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'product_category_id' => $this->category->id,
            'current_stock' => 0,
            'min_stock' => 5
        ]);

        $normalStockProduct = Product::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'product_category_id' => $this->category->id,
            'current_stock' => 50,
            'min_stock' => 10
        ]);

        // Test low stock detection
        $this->assertTrue($lowStockProduct->isLowStock());
        $this->assertTrue($outOfStockProduct->isLowStock());
        $this->assertFalse($normalStockProduct->isLowStock());

        // Test queries for different stock levels
        $lowStockCount = Product::where('cooperation_id', $this->cooperation->id)
            ->whereColumn('current_stock', '<', 'min_stock')
            ->where('current_stock', '>', 0)
            ->count();

        $outOfStockCount = Product::where('cooperation_id', $this->cooperation->id)
            ->where('current_stock', '<=', 0)
            ->count();

        $this->assertEquals(1, $lowStockCount);
        $this->assertEquals(1, $outOfStockCount);
    }

    /** @test */
    public function it_can_record_stock_movement_when_stock_changes()
    {
        $initialStock = $this->product->current_stock;
        $newStock = 150;

        // Update stock directly
        $this->product->update(['current_stock' => $newStock]);

        // Check if stock movement log was created (via ProductObserver)
        $this->assertDatabaseHas('stock_movement_logs', [
            'product_id' => $this->product->id,
            'cooperation_id' => $this->cooperation->id,
            'stock_before' => $initialStock,
            'stock_after' => $newStock,
            'type' => 'in'
        ]);
    }

    /** @test */
    public function it_can_record_stock_movement_via_service()
    {
        $initialStock = $this->product->current_stock;
        $quantity = 25;

        // Test stock in via service
        $result = StockMovementService::recordMovement(
            productId: $this->product->id,
            type: 'purchase',
            quantity: $quantity,
            referenceType: 'purchase_order',
            referenceId: 1,
            notes: 'Test purchase from supplier',
            createdBy: $this->user->id
        );

        $this->assertTrue($result);

        // Verify product stock updated
        $this->product->refresh();
        $this->assertEquals($initialStock + $quantity, $this->product->current_stock);

        // Verify log created
        $this->assertDatabaseHas('stock_movement_logs', [
            'product_id' => $this->product->id,
            'type' => 'purchase',
            'quantity' => $quantity,
            'stock_before' => $initialStock,
            'stock_after' => $initialStock + $quantity,
            'reference_type' => 'purchase_order',
            'reference_id' => 1,
            'notes' => 'Test purchase from supplier',
            'created_by' => $this->user->id
        ]);
    }

    /** @test */
    public function it_can_handle_stock_out_movements()
    {
        $initialStock = $this->product->current_stock;
        $quantity = 30;

        $result = StockMovementService::recordMovement(
            productId: $this->product->id,
            type: 'sale',
            quantity: $quantity,
            referenceType: 'sales_order',
            referenceId: 1,
            notes: 'Test sale to customer'
        );

        $this->assertTrue($result);

        // Verify product stock updated
        $this->product->refresh();
        $this->assertEquals($initialStock - $quantity, $this->product->current_stock);

        // Verify log created
        $this->assertDatabaseHas('stock_movement_logs', [
            'product_id' => $this->product->id,
            'type' => 'sale',
            'quantity' => $quantity,
            'stock_before' => $initialStock,
            'stock_after' => $initialStock - $quantity
        ]);
    }

    /** @test */
    public function it_prevents_negative_stock()
    {
        // Set product to low stock
        $this->product->update(['current_stock' => 5]);

        $result = StockMovementService::recordMovement(
            productId: $this->product->id,
            type: 'sale',
            quantity: 10, // More than available
            referenceType: 'sales_order',
            referenceId: 1
        );

        $this->assertTrue($result);

        // Verify stock doesn't go negative
        $this->product->refresh();
        $this->assertEquals(0, $this->product->current_stock);
    }

    /** @test */
    public function it_can_handle_stock_adjustment()
    {
        $initialStock = $this->product->current_stock;
        $targetStock = 75;

        $result = StockMovementService::recordMovement(
            productId: $this->product->id,
            type: 'adjustment',
            quantity: $targetStock,
            referenceType: 'manual_adjustment',
            notes: 'Stock count adjustment'
        );

        $this->assertTrue($result);

        // Verify product stock adjusted
        $this->product->refresh();
        $this->assertEquals($targetStock, $this->product->current_stock);

        // Verify log created with correct quantity difference
        $this->assertDatabaseHas('stock_movement_logs', [
            'product_id' => $this->product->id,
            'type' => 'adjustment',
            'quantity' => abs($targetStock - $initialStock),
            'stock_before' => $initialStock,
            'stock_after' => $targetStock
        ]);
    }

    /** @test */
    public function it_can_handle_damaged_stock()
    {
        $initialStock = $this->product->current_stock;
        $damagedQuantity = 15;

        $result = StockMovementService::recordMovement(
            productId: $this->product->id,
            type: 'damaged',
            quantity: $damagedQuantity,
            referenceType: 'damage_report',
            referenceId: 1,
            notes: 'Damaged goods due to water leak'
        );

        $this->assertTrue($result);

        // Verify stock reduced
        $this->product->refresh();
        $this->assertEquals($initialStock - $damagedQuantity, $this->product->current_stock);

        // Verify log created
        $this->assertDatabaseHas('stock_movement_logs', [
            'product_id' => $this->product->id,
            'type' => 'damaged',
            'quantity' => $damagedQuantity,
            'reference_type' => 'damage_report'
        ]);
    }

    /** @test */
    public function it_can_handle_return_stock()
    {
        $initialStock = $this->product->current_stock;
        $returnQuantity = 8;

        $result = StockMovementService::recordMovement(
            productId: $this->product->id,
            type: 'return',
            quantity: $returnQuantity,
            referenceType: 'return_order',
            referenceId: 1,
            notes: 'Customer return - defective items'
        );

        $this->assertTrue($result);

        // Verify stock increased
        $this->product->refresh();
        $this->assertEquals($initialStock + $returnQuantity, $this->product->current_stock);

        // Verify log created
        $this->assertDatabaseHas('stock_movement_logs', [
            'product_id' => $this->product->id,
            'type' => 'return',
            'quantity' => $returnQuantity,
            'reference_type' => 'return_order'
        ]);
    }

    /** @test */
    public function it_can_track_multiple_movements_for_one_product()
    {
        // Create multiple movements
        StockMovementService::recordMovement($this->product->id, 'purchase', 50, 'purchase_order', 1);
        StockMovementService::recordMovement($this->product->id, 'sale', 25, 'sales_order', 1);
        StockMovementService::recordMovement($this->product->id, 'damaged', 5, 'damage_report', 1);

        // Check total movements
        $movementCount = StockMovementLog::where('product_id', $this->product->id)->count();
        $this->assertEquals(3, $movementCount);

        // Check final stock calculation
        $this->product->refresh();
        $expectedStock = 100 + 50 - 25 - 5; // initial + purchase - sale - damaged
        $this->assertEquals($expectedStock, $this->product->current_stock);
    }

    /** @test */
    public function it_can_get_stock_movement_summary()
    {
        // Create some movements with dates
        StockMovementService::recordMovement($this->product->id, 'purchase', 50, 'purchase_order', 1);
        StockMovementService::recordMovement($this->product->id, 'sale', 25, 'sales_order', 1);
        StockMovementService::recordMovement($this->product->id, 'sale', 15, 'sales_order', 2);

        // Test if we can get summary data
        $totalIn = StockMovementLog::where('product_id', $this->product->id)
            ->whereIn('type', ['purchase', 'in', 'return'])
            ->sum('quantity');

        $totalOut = StockMovementLog::where('product_id', $this->product->id)
            ->whereIn('type', ['sale', 'out', 'damaged'])
            ->sum('quantity');

        $this->assertEquals(50, $totalIn);
        $this->assertEquals(40, $totalOut);
    }

    /** @test */
    public function it_logs_movement_with_correct_user()
    {
        StockMovementService::recordMovement(
            productId: $this->product->id,
            type: 'sale',
            quantity: 10,
            createdBy: $this->user->id
        );

        $this->assertDatabaseHas('stock_movement_logs', [
            'product_id' => $this->product->id,
            'created_by' => $this->user->id
        ]);
    }

    /** @test */
    public function it_can_filter_movements_by_cooperation()
    {
        // Create another cooperation and product
        $otherCooperation = Cooperation::factory()->create();
        $otherCategory = ProductCategory::factory()->create([
            'cooperation_id' => $otherCooperation->id
        ]);
        $otherProduct = Product::factory()->create([
            'cooperation_id' => $otherCooperation->id,
            'product_category_id' => $otherCategory->id
        ]);

        // Create movements for both products
        StockMovementService::recordMovement($this->product->id, 'sale', 10);
        StockMovementService::recordMovement($otherProduct->id, 'sale', 20);

        // Check that movements are properly isolated by cooperation
        $thisCoopMovements = StockMovementLog::where('cooperation_id', $this->cooperation->id)->count();
        $otherCoopMovements = StockMovementLog::where('cooperation_id', $otherCooperation->id)->count();

        $this->assertEquals(1, $thisCoopMovements);
        $this->assertEquals(1, $otherCoopMovements);
    }

    /** @test */
    public function it_handles_bulk_stock_movements()
    {
        // Create another product
        $product2 = Product::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'product_category_id' => $this->category->id,
            'current_stock' => 50
        ]);

        $movements = [
            [
                'product_id' => $this->product->id,
                'type' => 'sale',
                'quantity' => 10,
                'reference_type' => 'sales_order',
                'reference_id' => 1
            ],
            [
                'product_id' => $product2->id,
                'type' => 'sale',
                'quantity' => 5,
                'reference_type' => 'sales_order',
                'reference_id' => 1
            ]
        ];

        $results = StockMovementService::recordBulkMovements($movements);

        // Check all movements succeeded
        foreach ($results as $result) {
            $this->assertTrue($result);
        }

        // Verify both products' stock updated
        $this->product->refresh();
        $product2->refresh();
        
        $this->assertEquals(90, $this->product->current_stock);
        $this->assertEquals(45, $product2->current_stock);

        // Verify movement logs created
        $this->assertEquals(2, StockMovementLog::whereIn('product_id', [$this->product->id, $product2->id])->count());
    }
}
