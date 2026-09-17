<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->string('type')->nullable()->after('product_id');
        });

        foreach (DB::table('plans')->get() as $plan) {
            if (! str_contains($plan->name, ' - ')) {
                continue;
            }

            [$type, $rest] = explode(' - ', $plan->name, 2);

            DB::table('plans')->where('id', $plan->id)->update([
                'type' => trim($type),
                'name' => trim($rest),
            ]);
        }
    }

    public function down(): void
    {
        foreach (DB::table('plans')->whereNotNull('type')->get() as $plan) {
            DB::table('plans')->where('id', $plan->id)->update([
                'name' => "{$plan->type} - {$plan->name}",
            ]);
        }

        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
