<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campaign_deliveries', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->change();
            $table->foreignId('contact_id')->nullable()->after('user_id')->constrained('marketing_contacts')->cascadeOnDelete();
            $table->unique(['campaign_id', 'contact_id', 'channel']);
        });
    }

    public function down(): void
    {
        Schema::table('campaign_deliveries', function (Blueprint $table) {
            $table->dropUnique(['campaign_id', 'contact_id', 'channel']);
            $table->dropConstrainedForeignId('contact_id');
            $table->foreignId('user_id')->nullable(false)->change();
        });
    }
};
