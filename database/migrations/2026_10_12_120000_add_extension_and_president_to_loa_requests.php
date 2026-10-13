<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loa_requests', function (Blueprint $table) {
            $table->boolean('is_extension')->default(false)->after('status');
            $table->foreignId('parent_loa_id')->nullable()->after('is_extension')
                ->constrained('loa_requests')->restrictOnDelete();

            $table->string('president_status', 20)->nullable()->index()->after('submitted_at');
            $table->timestamp('president_at')->nullable()->after('president_status');
            $table->foreignId('president_by')->nullable()->after('president_at')
                ->constrained('users')->nullOnDelete();
        });

        Schema::table('loa_access_tokens', function (Blueprint $table) {
            $table->string('purpose', 20)->default('leave')->after('otp_verified');
            $table->foreignId('parent_loa_id')->nullable()->after('purpose')
                ->constrained('loa_requests')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('loa_access_tokens', function (Blueprint $table) {
            $table->dropConstrainedForeignId('parent_loa_id');
            $table->dropColumn('purpose');
        });

        Schema::table('loa_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('president_by');
            $table->dropConstrainedForeignId('parent_loa_id');
            $table->dropIndex(['president_status']);
            $table->dropColumn(['is_extension', 'president_status', 'president_at']);
        });
    }
};
