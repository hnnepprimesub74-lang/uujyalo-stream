<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->foreignId('audience_product_id')->nullable()->after('audience_plan_id')->constrained('products')->nullOnDelete();
            $table->unsignedInteger('contact_range_start')->nullable()->after('include_contacts');
            $table->unsignedInteger('contact_range_end')->nullable()->after('contact_range_start');
        });
    }

    public function down(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropConstrainedForeignId('audience_product_id');
            $table->dropColumn(['contact_range_start', 'contact_range_end']);
        });
    }
};
