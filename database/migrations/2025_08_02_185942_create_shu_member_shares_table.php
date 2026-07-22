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
        Schema::create('shu_member_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shu_distribution_id')->constrained('shu_distributions')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->decimal('savings_contribution', 15, 2);
            $table->decimal('transaction_contribution', 15, 2);
            $table->decimal('shu_amount', 15, 2);
            $table->enum('status', ['pending', 'paid'])->default('pending');
            $table->timestamp('paid_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shu_member_shares');
    }
};
