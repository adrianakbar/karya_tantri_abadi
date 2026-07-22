<?php

namespace App\Console\Commands;

use App\Services\AuditTrailService;
use App\Models\User;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;

class GenerateAuditTestData extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'audit:generate-test-data {--count=50 : Number of test records to generate}';

    /**
     * The console command description.
     */
    protected $description = 'Generate test data for audit trail functionality';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $count = (int) $this->option('count');
        
        $this->info("Generating {$count} test audit records...");

        $users = User::limit(5)->get();
        $products = Product::limit(10)->get();

        if ($users->isEmpty()) {
            $this->error('No users found. Please create some users first.');
            return self::FAILURE;
        }

        $progressBar = $this->output->createProgressBar($count);

        for ($i = 0; $i < $count; $i++) {
            $user = $users->random();
            
            // Simulate different types of activities
            $activities = [
                'login' => function() use ($user) {
                    AuditTrailService::logAuth('login', $user->id, $user->cooperation_id);
                },
                'page_access' => function() use ($user) {
                    AuditTrailService::logActivity(
                        'page_access',
                        'dashboard',
                        'Viewed dashboard',
                        $user->id,
                        $user->cooperation_id
                    );
                },
                'product_view' => function() use ($user, $products) {
                    if ($products->isNotEmpty()) {
                        $product = $products->random();
                        AuditTrailService::logActivity(
                            'view',
                            'inventory',
                            "Viewed product: {$product->name}",
                            $user->id,
                            $user->cooperation_id
                        );
                    }
                },
                'report_generation' => function() use ($user) {
                    AuditTrailService::logReportGeneration(
                        'sales_report',
                        ['date_from' => '2024-01-01', 'date_to' => '2024-01-31']
                    );
                },
                'config_change' => function() use ($user) {
                    AuditTrailService::logConfigChange(
                        'min_stock_threshold',
                        '10',
                        '15'
                    );
                },
                'failed_login' => function() {
                    AuditTrailService::logAuth('failed_login');
                },
            ];

            $activityType = array_rand($activities);
            $activities[$activityType]();

            $progressBar->advance();
            
            // Small delay to create realistic timestamps
            usleep(rand(1000, 10000)); // 1-10ms
        }

        $progressBar->finish();
        $this->newLine();
        $this->info("Successfully generated {$count} test audit records!");

        return self::SUCCESS;
    }
}
