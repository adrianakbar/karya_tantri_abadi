<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use App\Models\AuthLog;
use App\Models\DataChangeLog;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CleanupAuditLogs extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'audit:cleanup {--days=90 : Number of days to keep logs} {--force : Force cleanup without confirmation}';

    /**
     * The console command description.
     */
    protected $description = 'Clean up old audit trail logs to maintain database performance';

    /**ss
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) $this->option('days');
        $force = $this->option('force');
        
        if ($days < 30) {
            $this->error('Minimum retention period is 30 days for security compliance.');
            return self::FAILURE;
        }

        $cutoffDate = Carbon::now()->subDays($days);
        
        $this->info("Cleaning up audit logs older than {$days} days (before {$cutoffDate->format('Y-m-d H:i:s')})");

        // Count records to be deleted
        $activityCount = ActivityLog::where('created_at', '<', $cutoffDate)->count();
        $authCount = AuthLog::where('created_at', '<', $cutoffDate)->count();
        $dataChangeCount = DataChangeLog::where('changed_at', '<', $cutoffDate)->count();
        
        $totalCount = $activityCount + $authCount + $dataChangeCount;

        if ($totalCount === 0) {
            $this->info('No old audit logs found to clean up.');
            return self::SUCCESS;
        }

        $this->table(
            ['Log Type', 'Records to Delete'],
            [
                ['Activity Logs', number_format($activityCount)],
                ['Auth Logs', number_format($authCount)],
                ['Data Change Logs', number_format($dataChangeCount)],
                ['Total', number_format($totalCount)],
            ]
        );

        if (!$force && !$this->confirm("Are you sure you want to delete {$totalCount} audit log records?")) {
            $this->info('Cleanup cancelled.');
            return self::SUCCESS;
        }

        $this->info('Starting cleanup...');
        
        // Delete in chunks to avoid memory issues
        $deletedActivity = $this->deleteInChunks(ActivityLog::class, 'created_at', $cutoffDate, 'Activity Logs');
        $deletedAuth = $this->deleteInChunks(AuthLog::class, 'created_at', $cutoffDate, 'Auth Logs');
        $deletedDataChange = $this->deleteInChunks(DataChangeLog::class, 'changed_at', $cutoffDate, 'Data Change Logs');

        $totalDeleted = $deletedActivity + $deletedAuth + $deletedDataChange;

        $this->info("Cleanup completed successfully!");
        $this->info("Total records deleted: {$totalDeleted}");
        
        // Log the cleanup action
        $this->info('Logging cleanup action...');
        try {
            ActivityLog::create([
                'cooperation_id' => 1, // System action
                'user_id' => 1, // System user
                'action' => 'cleanup',
                'module' => 'audit_trail',
                'description' => "Automated cleanup: deleted {$totalDeleted} audit log records older than {$days} days",
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Console Command',
            ]);
        } catch (\Exception $e) {
            $this->warn('Could not log cleanup action: ' . $e->getMessage());
        }

        return self::SUCCESS;
    }

    /**
     * Delete records in chunks to avoid memory issues
     */
    private function deleteInChunks(string $model, string $dateColumn, Carbon $cutoffDate, string $logType): int
    {
        $totalDeleted = 0;
        $chunkSize = 1000;

        $this->info("Processing {$logType}...");
        
        $progressBar = $this->output->createProgressBar();
        $progressBar->setFormat('debug');

        do {
            $deleted = $model::where($dateColumn, '<', $cutoffDate)
                ->limit($chunkSize)
                ->delete();
            
            $totalDeleted += $deleted;
            $progressBar->advance($deleted);
            
            // Small delay to prevent database overload
            if ($deleted > 0) {
                usleep(10000); // 10ms
            }
        } while ($deleted > 0);

        $progressBar->finish();
        $this->newLine();
        $this->info("{$logType}: {$totalDeleted} records deleted");

        return $totalDeleted;
    }
}
