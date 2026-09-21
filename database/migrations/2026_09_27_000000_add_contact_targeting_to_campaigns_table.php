<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->json('excluded_user_ids')->nullable()->after('audience_plan_id');
            $table->boolean('include_contacts')->default(false)->after('excluded_user_ids');
        });
    }

    public function down(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropColumn(['excluded_user_ids', 'include_contacts']);
        });
    }
};
