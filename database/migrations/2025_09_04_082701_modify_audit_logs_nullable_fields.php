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
        // Modify activity_logs table
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('cooperation_id')->nullable()->change();
            $table->unsignedBigInteger('user_id')->nullable()->change();
        });

        // Modify auth_logs table
        Schema::table('auth_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('cooperation_id')->nullable()->change();
            $table->unsignedBigInteger('user_id')->nullable()->change();
        });

        // Modify data_change_logs table
        Schema::table('data_change_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('cooperation_id')->nullable()->change();
            $table->unsignedBigInteger('user_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse the changes
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropForeign(['cooperation_id']);
            $table->dropForeign(['user_id']);
            
            $table->foreignId('cooperation_id')->constrained('cooperations')->onDelete('cascade')->change();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade')->change();
        });

        Schema::table('auth_logs', function (Blueprint $table) {
            $table->dropForeign(['cooperation_id']);
            $table->dropForeign(['user_id']);
            
            $table->foreignId('cooperation_id')->constrained('cooperations')->onDelete('cascade')->change();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade')->change();
        });

        Schema::table('data_change_logs', function (Blueprint $table) {
            $table->dropForeign(['cooperation_id']);
            $table->dropForeign(['user_id']);
            
            $table->foreignId('cooperation_id')->constrained('cooperations')->onDelete('cascade')->change();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade')->change();
        });
    }
};
