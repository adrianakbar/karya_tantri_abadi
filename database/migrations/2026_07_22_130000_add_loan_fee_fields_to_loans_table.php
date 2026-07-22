<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->decimal('admin_fee', 15, 2)->default(0)->after('principal_amount');
            $table->decimal('utj_fee', 15, 2)->default(0)->after('admin_fee');
            $table->decimal('installment_fee', 15, 2)->default(0)->after('utj_fee');
            $table->decimal('net_disbursement', 15, 2)->default(0)->after('installment_fee');
            $table->enum('payment_frequency', ['weekly', 'monthly'])->default('weekly')->after('tenor_months');
            $table->integer('installment_count')->default(0)->after('payment_frequency');
        });
    }

    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropColumn([
                'admin_fee',
                'utj_fee',
                'installment_fee',
                'net_disbursement',
                'payment_frequency',
                'installment_count',
            ]);
        });
    }
};
