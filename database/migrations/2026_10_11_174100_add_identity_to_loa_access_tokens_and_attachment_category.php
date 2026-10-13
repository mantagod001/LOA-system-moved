<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loa_access_tokens', function (Blueprint $table) {
            $table->string('first_name', 100)->nullable()->after('full_name');
            $table->string('middle_name', 100)->nullable()->after('first_name');
            $table->string('last_name', 100)->nullable()->after('middle_name');
        });

        Schema::table('loa_attachments', function (Blueprint $table) {
            $table->string('category', 40)->nullable()->after('original_name');
        });
    }

    public function down(): void
    {
        Schema::table('loa_access_tokens', function (Blueprint $table) {
            $table->dropColumn(['first_name', 'middle_name', 'last_name']);
        });

        Schema::table('loa_attachments', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }
};
