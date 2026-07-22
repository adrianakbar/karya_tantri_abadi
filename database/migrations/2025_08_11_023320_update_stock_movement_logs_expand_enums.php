<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('stock_movement_logs', function (Blueprint $table) {
            // Drop the existing enum constraints and recreate with more options
            $table->dropColumn(['reference_type', 'type']);
        });
        
        Schema::table('stock_movement_logs', function (Blueprint $table) {
            // Add new columns with expanded options
            $table->string('reference_type', 50)->nullable()->after('product_id');
            $table->string('type', 20)->after('reference_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_movement_logs', function (Blueprint $table) {
            $table->dropColumn(['reference_type', 'type']);
        });
        
        Schema::table('stock_movement_logs', function (Blueprint $table) {
            // Restore original enum constraints
            $table->enum('reference_type', ['purchase', 'sale', 'adjustment'])->after('product_id');
            $table->enum('type', ['in', 'out'])->after('reference_id');
        });
    }
};
