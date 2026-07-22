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
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cooperation_id')->constrained('cooperations')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('loan_type_id')->constrained('loan_types')->onDelete('cascade');
            $table->string('loan_number', 50)->unique();
            $table->decimal('principal_amount', 15, 2);
            $table->decimal('interest_rate', 5, 2);
            $table->integer('tenor_months');
            $table->decimal('monthly_payment', 15, 2);
            $table->decimal('total_payment', 15, 2);
            $table->decimal('remaining_balance', 15, 2);
            $table->date('application_date');
            $table->date('approved_date')->nullable();
            $table->date('disbursement_date')->nullable();
            $table->date('due_date')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('purpose')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'disbursed', 'active', 'completed', 'overdue'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
