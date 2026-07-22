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
        Schema::create('savings_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cooperation_id')->constrained('cooperations')->onDelete('cascade');
            $table->string('name', 100);
            $table->string('code', 20);
            $table->decimal('amount', 15, 2)->nullable();
            $table->boolean('is_mandatory')->default(false);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('savings_types');
    }
};
