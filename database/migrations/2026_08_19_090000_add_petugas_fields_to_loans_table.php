<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // user_id boleh kosong: pengajuan petugas belum punya akun anggota
        Schema::table('loans', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        Schema::table('loans', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->change();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();

            $table->string('applicant_name')->nullable()->after('user_id');
            $table->string('ktp_photo')->nullable()->after('applicant_name');
            $table->foreignId('created_by')->nullable()->after('approved_by')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['user_id']);
            $table->dropColumn(['applicant_name', 'ktp_photo', 'created_by']);
        });
        Schema::table('loans', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable(false)->change();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};
