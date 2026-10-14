<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loa_requests', function (Blueprint $table) {
            $table->timestamp('did_not_enroll_at')->nullable()->after('returned_by');
            $table->foreignId('did_not_enroll_by')->nullable()->after('did_not_enroll_at')
                ->constrained('users')->nullOnDelete();
            $table->string('did_not_enroll_academic_year', 9)->nullable()->after('did_not_enroll_by');
            $table->string('did_not_enroll_semester', 20)->nullable()->after('did_not_enroll_academic_year');
        });
    }

    public function down(): void
    {
        Schema::table('loa_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('did_not_enroll_by');
            $table->dropColumn([
                'did_not_enroll_at',
                'did_not_enroll_academic_year',
                'did_not_enroll_semester',
            ]);
        });
    }
};
