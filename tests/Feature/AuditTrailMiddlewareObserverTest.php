<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Cooperation;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ActivityLog;
use App\Models\DataChangeLog;
use App\Http\Middleware\LogUserActivity;
use App\Observers\AuditTrailObserver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class AuditTrailMiddlewareObserverTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $cooperation;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->cooperation = Cooperation::create([
            'code' => 'TEST001',
            'name' => 'Test Cooperation',
            'address' => 'Test Address',
            'phone' => '08123456789',
            'email' => 'test@cooperation.com',
        ]);

        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'cooperation_id' => $this->cooperation->id,
        ]);
    }

    /** @test */
    public function middleware_logs_user_activity_for_tracked_routes()
    {
        $this->actingAs($this->user);

        $request = Request::create('/admin/dashboard', 'GET');
        $request->setUserResolver(function () {
            return $this->user;
        });

        $middleware = new LogUserActivity();
        
        $response = $middleware->handle($request, function ($req) {
            return response('OK');
        });

        $this->assertEquals('OK', $response->getContent());

        // Check if activity was logged
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->user->id,
            'cooperation_id' => $this->cooperation->id,
            'action' => 'access_page',
            'module' => 'dashboard',
        ]);
    }

    /** @test */
    public function middleware_does_not_log_excluded_routes()
    {
        $this->actingAs($this->user);

        $excludedRoutes = [
            '/admin/livewire/update',
            '/admin/livewire/message',
            '/admin/_debugbar/assets/stylesheets',
        ];

        foreach ($excludedRoutes as $route) {
            $request = Request::create($route, 'GET');
            $request->setUserResolver(function () {
                return $this->user;
            });

            $middleware = new LogUserActivity();
            $initialCount = ActivityLog::count();
            
            $middleware->handle($request, function ($req) {
                return response('OK');
            });

            // Activity count should not increase for excluded routes
            $this->assertEquals($initialCount, ActivityLog::count());
        }
    }

    /** @test */
    public function middleware_handles_unauthenticated_users()
    {
        $request = Request::create('/admin/dashboard', 'GET');
        
        $middleware = new LogUserActivity();
        $initialCount = ActivityLog::count();
        
        $response = $middleware->handle($request, function ($req) {
            return response('OK');
        });

        $this->assertEquals('OK', $response->getContent());
        
        // No activity should be logged for unauthenticated users
        $this->assertEquals($initialCount, ActivityLog::count());
    }

    /** @test */
    public function observer_logs_model_creation()
    {
        $this->actingAs($this->user);

        // Create a product category first
        $category = ProductCategory::create([
            'cooperation_id' => $this->cooperation->id,
            'name' => 'Test Category',
            'description' => 'Test Description',
        ]);

        // Create a product which should trigger the observer
        $product = Product::create([
            'cooperation_id' => $this->cooperation->id,
            'category_id' => $category->id,
            'name' => 'Test Product',
            'description' => 'Test Description',
            'price' => 10000,
            'stock' => 100,
            'unit' => 'pcs',
        ]);

        // Check if data change was logged
        $this->assertDatabaseHas('data_change_logs', [
            'user_id' => $this->user->id,
            'cooperation_id' => $this->cooperation->id,
            'table_name' => 'products',
            'record_id' => $product->id,
            'action' => 'create',
        ]);

        $log = DataChangeLog::where('table_name', 'products')
            ->where('record_id', $product->id)
            ->where('action', 'create')
            ->first();

        $this->assertNotNull($log);
        $this->assertNull($log->old_values);
        $this->assertNotNull($log->new_values);
        
        $newValues = json_decode($log->new_values, true);
        $this->assertEquals('Test Product', $newValues['name']);
    }

    /** @test */
    public function observer_logs_model_update()
    {
        $this->actingAs($this->user);

        // Create a product category first
        $category = ProductCategory::create([
            'cooperation_id' => $this->cooperation->id,
            'name' => 'Test Category',
            'description' => 'Test Description',
        ]);

        // Create a product
        $product = Product::create([
            'cooperation_id' => $this->cooperation->id,
            'category_id' => $category->id,
            'name' => 'Original Product',
            'description' => 'Original Description',
            'price' => 10000,
            'stock' => 100,
            'unit' => 'pcs',
        ]);

        // Clear any existing logs from creation
        DataChangeLog::where('table_name', 'products')->delete();

        // Update the product
        $product->update([
            'name' => 'Updated Product',
            'price' => 15000,
        ]);

        // Check if update was logged
        $this->assertDatabaseHas('data_change_logs', [
            'user_id' => $this->user->id,
            'cooperation_id' => $this->cooperation->id,
            'table_name' => 'products',
            'record_id' => $product->id,
            'action' => 'update',
        ]);

        $log = DataChangeLog::where('table_name', 'products')
            ->where('record_id', $product->id)
            ->where('action', 'update')
            ->first();

        $this->assertNotNull($log);
        
        $oldValues = json_decode($log->old_values, true);
        $newValues = json_decode($log->new_values, true);
        
        $this->assertEquals('Original Product', $oldValues['name']);
        $this->assertEquals('Updated Product', $newValues['name']);
        $this->assertEquals(10000, $oldValues['price']);
        $this->assertEquals(15000, $newValues['price']);
    }

    /** @test */
    public function observer_logs_model_deletion()
    {
        $this->actingAs($this->user);

        // Create a product category first
        $category = ProductCategory::create([
            'cooperation_id' => $this->cooperation->id,
            'name' => 'Test Category',
            'description' => 'Test Description',
        ]);

        // Create a product
        $product = Product::create([
            'cooperation_id' => $this->cooperation->id,
            'category_id' => $category->id,
            'name' => 'Product to Delete',
            'description' => 'Test Description',
            'price' => 10000,
            'stock' => 100,
            'unit' => 'pcs',
        ]);

        $productId = $product->id;

        // Clear any existing logs from creation
        DataChangeLog::where('table_name', 'products')->delete();

        // Delete the product
        $product->delete();

        // Check if deletion was logged
        $this->assertDatabaseHas('data_change_logs', [
            'user_id' => $this->user->id,
            'cooperation_id' => $this->cooperation->id,
            'table_name' => 'products',
            'record_id' => $productId,
            'action' => 'delete',
        ]);

        $log = DataChangeLog::where('table_name', 'products')
            ->where('record_id', $productId)
            ->where('action', 'delete')
            ->first();

        $this->assertNotNull($log);
        $this->assertNotNull($log->old_values);
        $this->assertNull($log->new_values);
        
        $oldValues = json_decode($log->old_values, true);
        $this->assertEquals('Product to Delete', $oldValues['name']);
    }

    /** @test */
    public function observer_does_not_log_excluded_models()
    {
        $this->actingAs($this->user);

        $initialCount = DataChangeLog::count();

        // Create an ActivityLog (which should be excluded)
        ActivityLog::create([
            'user_id' => $this->user->id,
            'cooperation_id' => $this->cooperation->id,
            'action' => 'view',
            'module' => 'test',
            'description' => 'Test',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test',
        ]);

        // DataChangeLog count should not increase
        $this->assertEquals($initialCount, DataChangeLog::count());
    }

    /** @test */
    public function observer_handles_unauthenticated_operations()
    {
        // Don't authenticate user
        
        // Create a product category
        $category = ProductCategory::create([
            'cooperation_id' => $this->cooperation->id,
            'name' => 'Test Category',
            'description' => 'Test Description',
        ]);

        // Check if log was created without user
        $this->assertDatabaseHas('data_change_logs', [
            'user_id' => null,
            'cooperation_id' => $this->cooperation->id,
            'table_name' => 'product_categories',
            'record_id' => $category->id,
            'action' => 'create',
        ]);
    }

    /** @test */
    public function observer_determines_module_correctly()
    {
        $this->actingAs($this->user);

        // Test different model types
        $category = ProductCategory::create([
            'cooperation_id' => $this->cooperation->id,
            'name' => 'Test Category',
            'description' => 'Test Description',
        ]);

        $product = Product::create([
            'cooperation_id' => $this->cooperation->id,
            'category_id' => $category->id,
            'name' => 'Test Product',
            'description' => 'Test Description',
            'price' => 10000,
            'stock' => 100,
            'unit' => 'pcs',
        ]);

        // Check module determination
        $categoryLog = DataChangeLog::where('table_name', 'product_categories')->first();
        $productLog = DataChangeLog::where('table_name', 'products')->first();

        $this->assertEquals('products', $categoryLog->module);
        $this->assertEquals('products', $productLog->module);
    }
}
