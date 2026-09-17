<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_reassignment_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained()->cascadeOnDelete();
            $table->foreignId('old_shared_account_id')->nullable()->constrained('shared_accounts')->nullOnDelete();
            $table->foreignId('new_shared_account_id')->constrained('shared_accounts')->cascadeOnDelete();
            $table->boolean('customer_notified')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_reassignment_logs');
    }
};
