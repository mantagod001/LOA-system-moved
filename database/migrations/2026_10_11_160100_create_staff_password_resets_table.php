<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_password_resets', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->string('request_token_hash', 64)->unique();
            $table->string('otp_hash', 64)->nullable();
            $table->timestamp('otp_expires_at')->nullable();
            $table->timestamp('otp_verified_at')->nullable();
            $table->string('reset_token_hash', 64)->nullable()->unique();
            $table->timestamp('reset_token_expires_at')->nullable();
            $table->timestamp('used_at')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_password_resets');
    }
};
