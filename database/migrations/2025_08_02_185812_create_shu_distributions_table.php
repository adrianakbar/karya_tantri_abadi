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
        Schema::create('shu_distributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cooperation_id')->constrained('cooperations')->onDelete('cascade');
            $table->year('year');
            $table->decimal('total_revenue', 15, 2)->default(0);
            $table->decimal('total_expenses', 15, 2)->default(0);
            $table->decimal('total_shu', 15, 2);
            $table->date('distribution_date')->nullable();
            $table->enum('status', ['calculated', 'distributed'])->default('calculated');
            $table->foreignId('calculated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('distributed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shu_distributions');
    }
};
