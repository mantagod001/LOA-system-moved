<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loa_requests', function (Blueprint $table) {
            $table->string('academic_year', 9)->nullable()->after('year_level');
            $table->string('semester', 20)->nullable()->after('academic_year');
        });
    }

    public function down(): void
    {
        Schema::table('loa_requests', function (Blueprint $table) {
            $table->dropColumn(['academic_year', 'semester']);
        });
    }
};
