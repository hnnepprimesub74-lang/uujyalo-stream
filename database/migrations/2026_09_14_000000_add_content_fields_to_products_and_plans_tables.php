<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->text('description')->nullable()->after('category');
            $table->text('highlight_note')->nullable()->after('description');
            $table->text('plan_guidance')->nullable()->after('highlight_note');
            $table->json('faqs')->nullable()->after('plan_guidance');
        });

        Schema::table('plans', function (Blueprint $table) {
            $table->string('badge_label')->nullable()->after('type');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['description', 'highlight_note', 'plan_guidance', 'faqs']);
        });

        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn('badge_label');
        });
    }
};
