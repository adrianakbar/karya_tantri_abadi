<?php

namespace App\Jobs;

use App\Models\Product;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendLowStockNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $cooperationId;

    public function __construct($cooperationId)
    {
        $this->cooperationId = $cooperationId;
    }

    public function handle(): void
    {
        $lowStockProducts = Product::where('cooperation_id', $this->cooperationId)
            ->whereColumn('current_stock', '<', 'min_stock')
            ->where('current_stock', '>', 0)
            ->get();

        $outOfStockProducts = Product::where('cooperation_id', $this->cooperationId)
            ->where('current_stock', '<=', 0)
            ->get();

        if ($lowStockProducts->count() > 0 || $outOfStockProducts->count() > 0) {
            // Get admin users for this cooperation
            $adminUsers = User::where('cooperation_id', $this->cooperationId)
                ->whereHas('roles', function($query) {
                    $query->where('name', 'admin'); // Sesuaikan dengan sistem role Anda
                })
                ->get();

            foreach ($adminUsers as $admin) {
                // Di sini Anda bisa mengirim email, push notification, atau menyimpan ke tabel notifications
                // Contoh: Mail::to($admin->email)->send(new LowStockMail($lowStockProducts, $outOfStockProducts));
                
                Log::info('Low stock notification sent', [
                    'user_id' => $admin->id,
                    'cooperation_id' => $this->cooperationId,
                    'low_stock_count' => $lowStockProducts->count(),
                    'out_of_stock_count' => $outOfStockProducts->count(),
                ]);
            }
        }
    }
}
