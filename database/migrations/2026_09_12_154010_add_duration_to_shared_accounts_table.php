<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shared_accounts', function (Blueprint $table) {
            $table->date('purchased_at')->nullable()->after('max_slots');
            $table->unsignedInteger('duration_days')->nullable()->after('purchased_at');
        });
    }

    public function down(): void
    {
        Schema::table('shared_accounts', function (Blueprint $table) {
            $table->dropColumn(['purchased_at', 'duration_days']);
        });
    }
};
