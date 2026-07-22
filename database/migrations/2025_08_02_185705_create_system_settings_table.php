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
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cooperation_id')->constrained('cooperations')->onDelete('cascade');
            $table->enum('category', ['general', 'ui_theme', 'notification', 'backup', 'report_schedule', 'financial', 'inventory']);
            $table->string('key', 100);
            $table->text('value')->nullable();
            $table->enum('type', ['string', 'number', 'boolean', 'json', 'file'])->default('string');
            $table->text('description')->nullable();
            $table->boolean('is_system')->default(false);
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
