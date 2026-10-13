<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name', 100)->nullable()->after('name');
            $table->string('middle_name', 100)->nullable()->after('first_name');
            $table->string('last_name', 100)->nullable()->after('middle_name');
        });

        DB::table('users')->orderBy('id')->each(function (object $row) {
            $parts = $this->splitName((string) ($row->name ?? ''));
            DB::table('users')->where('id', $row->id)->update($parts);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['first_name', 'middle_name', 'last_name']);
        });
    }

    /** @return array{first_name: string, middle_name: ?string, last_name: string} */
    private function splitName(string $full): array
    {
        $full = trim(preg_replace('/\s+/', ' ', $full) ?? '');

        if ($full === '') {
            return ['first_name' => '', 'middle_name' => null, 'last_name' => ''];
        }

        $parts = explode(' ', $full);

        if (count($parts) === 1) {
            return ['first_name' => $parts[0], 'middle_name' => null, 'last_name' => $parts[0]];
        }

        if (count($parts) === 2) {
            return ['first_name' => $parts[0], 'middle_name' => null, 'last_name' => $parts[1]];
        }

        return [
            'first_name'  => array_shift($parts),
            'last_name'   => array_pop($parts),
            'middle_name' => implode(' ', $parts),
        ];
    }
};
