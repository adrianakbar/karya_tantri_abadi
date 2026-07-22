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
        Schema::create('expenses', function (Blueprint $table) {
           $table->id();
            $table->foreignId('cooperation_id')->constrained()->onDelete('cascade');
            $table->foreignId('expense_category_id')->constrained()->onDelete('restrict');
            $table->string('expense_number', 50)->unique();
            $table->decimal('amount', 15, 2);
            $table->date('expense_date');
            $table->string('receipt_number', 50)->nullable();
            $table->string('recipient', 255)->nullable();
            
            // Foreign key ke tabel users, bisa null jika belum diproses/disetujui
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');

            $table->enum('status', ['pending', 'approved', 'paid'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
