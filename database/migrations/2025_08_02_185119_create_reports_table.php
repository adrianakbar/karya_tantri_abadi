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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cooperation_id')->constrained('cooperations')->onDelete('cascade');
            $table->string('name');
            $table->enum('type', ['financial', 'savings', 'loans', 'expenses', 'inventory', 'shu']);
            $table->date('period_start');
            $table->date('period_end');
            $table->foreignId('generated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('file_path')->nullable();
            $table->json('parameters')->nullable();
            $table->enum('status', ['generating', 'completed', 'failed'])->default('generating');
            $table->timestamp('generated_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
