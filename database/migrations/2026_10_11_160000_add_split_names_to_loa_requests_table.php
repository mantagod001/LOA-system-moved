<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loa_requests', function (Blueprint $table) {
            $table->string('first_name', 100)->nullable()->after('full_name');
            $table->string('middle_name', 100)->nullable()->after('first_name');
            $table->string('last_name', 100)->nullable()->after('middle_name');
            $table->string('parent_first_name', 100)->nullable()->after('parent_full_name');
            $table->string('parent_middle_name', 100)->nullable()->after('parent_first_name');
            $table->string('parent_last_name', 100)->nullable()->after('parent_middle_name');
        });

        $this->backfillNames('full_name', 'first_name', 'middle_name', 'last_name');
        $this->backfillNames('parent_full_name', 'parent_first_name', 'parent_middle_name', 'parent_last_name');
    }

    public function down(): void
    {
        Schema::table('loa_requests', function (Blueprint $table) {
            $table->dropColumn([
                'first_name',
                'middle_name',
                'last_name',
                'parent_first_name',
                'parent_middle_name',
                'parent_last_name',
            ]);
        });
    }

    private function backfillNames(string $source, string $first, string $middle, string $last): void
    {
        DB::table('loa_requests')->orderBy('id')->each(function (object $row) use ($source, $first, $middle, $last) {
            $parts = $this->splitName((string) ($row->{$source} ?? ''));

            DB::table('loa_requests')->where('id', $row->id)->update([
                $first  => $parts['first'],
                $middle => $parts['middle'],
                $last   => $parts['last'],
            ]);
        });
    }

    /** @return array{first: string, middle: ?string, last: string} */
    private function splitName(string $full): array
    {
        $full = trim(preg_replace('/\s+/', ' ', $full) ?? '');

        if ($full === '') {
            return ['first' => '', 'middle' => null, 'last' => ''];
        }

        $parts = explode(' ', $full);

        if (count($parts) === 1) {
            return ['first' => $parts[0], 'middle' => null, 'last' => $parts[0]];
        }

        if (count($parts) === 2) {
            return ['first' => $parts[0], 'middle' => null, 'last' => $parts[1]];
        }

        return [
            'first'  => array_shift($parts),
            'last'   => array_pop($parts),
            'middle' => implode(' ', $parts),
        ];
    }
};
