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
use Illuminate\Support\Facades\Log;

class StockMovementServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $cooperation;
    protected $user;
    protected $category;
    protected $product;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->cooperation = Cooperation::factory()->create();
        $this->user = User::factory()->create(['cooperation_id' => $this->cooperation->id]);
        $this->category = ProductCategory::factory()->create(['cooperation_id' => $this->cooperation->id]);
        $this->product = Product::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'product_category_id' => $this->category->id,
            'current_stock' => 100,
            'min_stock' => 10
        ]);

        $this->actingAs($this->user);
    }

    /** @test */
    public function it_records_purchase_movement_correctly()
    {
        $initialStock = $this->product->current_stock;
        $purchaseQuantity = 50;

        $result = StockMovementService::recordMovement(
            productId: $this->product->id,
            type: 'purchase',
            quantity: $purchaseQuantity,
            referenceType: 'purchase_order',
            referenceId: 123,
            notes: 'Purchase from Supplier ABC',
            createdBy: $this->user->id
        );

        $this->assertTrue($result);
        
        // Check product stock updated
        $this->product->refresh();
        $this->assertEquals($initialStock + $purchaseQuantity, $this->product->current_stock);

        // Check log entry
        $this->assertDatabaseHas('stock_movement_logs', [
            'product_id' => $this->product->id,
            'cooperation_id' => $this->cooperation->id,
            'type' => 'purchase',
            'quantity' => $purchaseQuantity,
            'stock_before' => $initialStock,
            'stock_after' => $initialStock + $purchaseQuantity,
            'reference_type' => 'purchase_order',
            'reference_id' => 123,
            'notes' => 'Purchase from Supplier ABC',
            'created_by' => $this->user->id
        ]);
    }

    /** @test */
    public function it_records_sale_movement_correctly()
    {
        $initialStock = $this->product->current_stock;
        $saleQuantity = 25;

        $result = StockMovementService::recordMovement(
            productId: $this->product->id,
            type: 'sale',
            quantity: $saleQuantity,
            referenceType: 'sales_order',
            referenceId: 456,
            notes: 'Sale to Customer XYZ'
        );

        $this->assertTrue($result);
        
        // Check product stock updated
        $this->product->refresh();
        $this->assertEquals($initialStock - $saleQuantity, $this->product->current_stock);

        // Check log entry
        $this->assertDatabaseHas('stock_movement_logs', [
            'product_id' => $this->product->id,
            'type' => 'sale',
            'quantity' => $saleQuantity,
            'stock_before' => $initialStock,
            'stock_after' => $initialStock - $saleQuantity
        ]);
    }

    /** @test */
    public function it_handles_stock_adjustment_correctly()
    {
        $initialStock = $this->product->current_stock;
        $targetStock = 75;

        $result = StockMovementService::recordMovement(
            productId: $this->product->id,
            type: 'adjustment',
            quantity: $targetStock,
            referenceType: 'manual_adjustment',
            notes: 'Physical count adjustment'
        );

        $this->assertTrue($result);
        
        // Check product stock adjusted to exact amount
        $this->product->refresh();
        $this->assertEquals($targetStock, $this->product->current_stock);

        // Check log entry shows the difference as quantity
        $expectedQuantity = abs($targetStock - $initialStock);
        $this->assertDatabaseHas('stock_movement_logs', [
            'product_id' => $this->product->id,
            'type' => 'adjustment',
            'quantity' => $expectedQuantity,
            'stock_before' => $initialStock,
            'stock_after' => $targetStock
        ]);
    }

    /** @test */
    public function it_prevents_negative_stock()
    {
        // Set low stock
        $this->product->update(['current_stock' => 10]);
        
        $result = StockMovementService::recordMovement(
            productId: $this->product->id,
            type: 'sale',
            quantity: 20 // More than available
        );

        $this->assertTrue($result);
        
        // Stock should not go below 0
        $this->product->refresh();
        $this->assertEquals(0, $this->product->current_stock);

        // Log should record the actual quantity moved
        $this->assertDatabaseHas('stock_movement_logs', [
            'product_id' => $this->product->id,
            'stock_before' => 10,
            'stock_after' => 0,
            'quantity' => 20 // Original requested quantity
        ]);
    }

    /** @test */
    public function it_generates_default_notes_when_none_provided()
    {
        StockMovementService::recordMovement(
            productId: $this->product->id,
            type: 'purchase',
            quantity: 30
        );

        $movement = StockMovementLog::latest()->first();
        $this->assertNotEmpty($movement->notes);
        $this->assertStringContainsString('30', $movement->notes);
    }

    /** @test */
    public function it_handles_return_movements()
    {
        $initialStock = $this->product->current_stock;
        $returnQuantity = 15;

        $result = StockMovementService::recordMovement(
            productId: $this->product->id,
            type: 'return',
            quantity: $returnQuantity,
            referenceType: 'return_order',
            referenceId: 789,
            notes: 'Customer return - defective items'
        );

        $this->assertTrue($result);
        
        // Returns should increase stock
        $this->product->refresh();
        $this->assertEquals($initialStock + $returnQuantity, $this->product->current_stock);
    }

    /** @test */
    public function it_handles_damaged_stock_correctly()
    {
        $initialStock = $this->product->current_stock;
        $damagedQuantity = 8;

        $result = StockMovementService::recordMovement(
            productId: $this->product->id,
            type: 'damaged',
            quantity: $damagedQuantity,
            referenceType: 'damage_report',
            referenceId: 111,
            notes: 'Water damage during storage'
        );

        $this->assertTrue($result);
        
        // Damaged items should reduce stock
        $this->product->refresh();
        $this->assertEquals($initialStock - $damagedQuantity, $this->product->current_stock);
    }

    /** @test */
    public function it_records_bulk_movements_successfully()
    {
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
                'reference_id' => 1,
                'notes' => 'Sale item 1'
            ],
            [
                'product_id' => $product2->id,
                'type' => 'sale',
                'quantity' => 5,
                'reference_type' => 'sales_order',
                'reference_id' => 1,
                'notes' => 'Sale item 2'
            ]
        ];

        $results = StockMovementService::recordBulkMovements($movements);

        // All movements should succeed
        $this->assertCount(2, $results);
        foreach ($results as $result) {
            $this->assertTrue($result);
        }

        // Check stock updates
        $this->product->refresh();
        $product2->refresh();
        
        $this->assertEquals(90, $this->product->current_stock);
        $this->assertEquals(45, $product2->current_stock);

        // Check logs created
        $this->assertEquals(2, StockMovementLog::whereIn('product_id', [$this->product->id, $product2->id])->count());
    }

    /** @test */
    public function it_handles_invalid_product_id_gracefully()
    {
        $result = StockMovementService::recordMovement(
            productId: 99999, // Non-existent product
            type: 'sale',
            quantity: 10
        );

        $this->assertFalse($result);
        
        // No movement log should be created
        $this->assertEquals(0, StockMovementLog::where('product_id', 99999)->count());
    }

    /** @test */
    public function it_uses_authenticated_user_when_created_by_not_specified()
    {
        StockMovementService::recordMovement(
            productId: $this->product->id,
            type: 'purchase',
            quantity: 20
        );

        $this->assertDatabaseHas('stock_movement_logs', [
            'product_id' => $this->product->id,
            'created_by' => $this->user->id
        ]);
    }

    /** @test */
    public function it_logs_errors_for_failed_movements()
    {
        Log::spy();

        // Try to create movement for non-existent product
        StockMovementService::recordMovement(
            productId: 99999,
            type: 'sale',
            quantity: 10
        );

        Log::shouldHaveReceived('error')
            ->with('Failed to record stock movement: No query results for model [App\Models\Product] 99999', [
                'product_id' => 99999,
                'type' => 'sale',
                'quantity' => 10,
                'reference_type' => null,
                'reference_id' => null,
            ]);
    }

    /** @test */
    public function it_can_get_product_stock_summary()
    {
        // Create various movements
        StockMovementService::recordMovement($this->product->id, 'purchase', 50);
        StockMovementService::recordMovement($this->product->id, 'sale', 25);
        StockMovementService::recordMovement($this->product->id, 'damaged', 5);
        StockMovementService::recordMovement($this->product->id, 'return', 10);

        $summary = StockMovementService::getProductStockSummary($this->product->id, 30);

        $this->assertIsArray($summary);
        $this->assertArrayHasKey('total_in', $summary);
        $this->assertArrayHasKey('total_out', $summary);
        $this->assertArrayHasKey('net_movement', $summary);
        $this->assertArrayHasKey('movement_count', $summary);

        $this->assertEquals(60, $summary['total_in']); // 50 purchase + 10 return
        $this->assertEquals(30, $summary['total_out']); // 25 sale + 5 damaged
        $this->assertEquals(30, $summary['net_movement']); // 60 - 30
        $this->assertEquals(4, $summary['movement_count']);
    }

    /** @test */
    public function it_handles_in_and_out_movements()
    {
        $initialStock = $this->product->current_stock;

        // Test generic 'in' movement
        StockMovementService::recordMovement(
            productId: $this->product->id,
            type: 'in',
            quantity: 20,
            referenceType: 'stock_in',
            notes: 'Manual stock addition'
        );

        $this->product->refresh();
        $this->assertEquals($initialStock + 20, $this->product->current_stock);

        // Test generic 'out' movement
        StockMovementService::recordMovement(
            productId: $this->product->id,
            type: 'out',
            quantity: 15,
            referenceType: 'stock_out',
            notes: 'Manual stock reduction'
        );

        $this->product->refresh();
        $this->assertEquals($initialStock + 20 - 15, $this->product->current_stock);
    }
}
