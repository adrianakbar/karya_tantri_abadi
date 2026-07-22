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
        Schema::create('savings_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cooperation_id')->constrained('cooperations')->onDelete('cascade');
            $table->foreignId('user_id')->comment('Anggota')->constrained('users')->onDelete('cascade');
            $table->foreignId('savings_type_id')->constrained('savings_types')->onDelete('cascade');
            $table->string('transaction_number', 50)->unique();
            $table->decimal('amount', 15, 2);
            $table->date('transaction_date');
            $table->text('notes')->nullable();
            $table->string('receipt_number', 50)->nullable();
            $table->foreignId('processed_by')->comment('Staff')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('completed');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('savings_transactions');
    }
};
