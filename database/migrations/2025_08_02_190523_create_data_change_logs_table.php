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
        Schema::create('data_change_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cooperation_id')->constrained('cooperations')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('table_name', 50);
            $table->unsignedBigInteger('record_id');
            $table->enum('action', ['create', 'update', 'delete']);
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->timestamp('changed_at')->useCurrent();
            $table->index(['table_name', 'record_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_change_logs');
    }
};
