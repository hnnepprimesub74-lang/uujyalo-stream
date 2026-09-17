<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->text('account_email')->nullable()->after('plan_id');
            $table->text('account_password')->nullable()->after('account_email');
            $table->unsignedInteger('total_days')->nullable()->after('amount');
            $table->unsignedInteger('days_recharged')->default(0)->after('total_days');
            $table->date('last_recharge_date')->nullable()->after('days_recharged');
            $table->date('next_recharge_date')->nullable()->after('last_recharge_date');

            $table->index('next_recharge_date');
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn([
                'account_email', 'account_password', 'total_days',
                'days_recharged', 'last_recharge_date', 'next_recharge_date',
            ]);
        });
    }
};
