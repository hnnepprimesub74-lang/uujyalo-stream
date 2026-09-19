<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaign_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('channel');
            $table->string('status');
            $table->text('response')->nullable();
            $table->timestamps();

            $table->index(['campaign_id', 'channel', 'status']);
            $table->unique(['campaign_id', 'user_id', 'channel']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_deliveries');
    }
};
