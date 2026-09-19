<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->string('order_number')->nullable()->after('id');
        });

        // Backfill existing orders using their own created_at + id, so the
        // number stays stable and unique even for historical records.
        DB::table('subscriptions')->orderBy('id')->chunk(200, function ($subscriptions) {
            foreach ($subscriptions as $subscription) {
                $timestamp = \Illuminate\Support\Carbon::parse($subscription->created_at)->format('Ymd-His');
                $suffix = strtoupper(substr(md5($subscription->id.$subscription->created_at), 0, 4));

                DB::table('subscriptions')
                    ->where('id', $subscription->id)
                    ->update(['order_number' => "ORD-{$timestamp}-{$suffix}"]);
            }
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->string('order_number')->nullable(false)->change();
            $table->unique('order_number');
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropUnique(['order_number']);
            $table->dropColumn('order_number');
        });
    }
};
