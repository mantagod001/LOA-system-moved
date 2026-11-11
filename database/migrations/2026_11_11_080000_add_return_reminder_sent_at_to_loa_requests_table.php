<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loa_requests', function (Blueprint $table) {
            $table->timestamp('return_reminder_sent_at')->nullable()->after('did_not_enroll_semester');
        });
    }

    public function down(): void
    {
        Schema::table('loa_requests', function (Blueprint $table) {
            $table->dropColumn('return_reminder_sent_at');
        });
    }
};
