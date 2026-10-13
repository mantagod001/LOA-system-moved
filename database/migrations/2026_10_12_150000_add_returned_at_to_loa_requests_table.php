<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loa_requests', function (Blueprint $table) {
            $table->timestamp('returned_at')->nullable()->after('discontinuation_reason');
            $table->foreignId('returned_by')->nullable()->after('returned_at')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('loa_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('returned_by');
            $table->dropColumn('returned_at');
        });
    }
};
