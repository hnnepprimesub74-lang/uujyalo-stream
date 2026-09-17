<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('phone_otps', function (Blueprint $table) {
            $table->id();
            $table->string('phone')->unique();
            $table->string('otp');
            $table->unsignedTinyInteger('attempts')->default(0);
            // dateTime (not timestamp) to avoid MySQL implicitly attaching
            // "ON UPDATE CURRENT_TIMESTAMP" to the first TIMESTAMP column,
            // which would silently reset expires_at on every attempts update.
            $table->dateTime('expires_at');
            $table->dateTime('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('phone_otps');
    }
};
