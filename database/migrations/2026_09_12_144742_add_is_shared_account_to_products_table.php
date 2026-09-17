<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_shared_account')->default(false)->after('is_active');
            $table->unsignedInteger('default_shared_slots')->default(3)->after('is_shared_account');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['is_shared_account', 'default_shared_slots']);
        });
    }
};
