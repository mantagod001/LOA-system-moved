<?php

namespace App\Support;

class PersonName
{
    public static function compose(?string $first, ?string $middle, ?string $last): string
    {
        return collect([$first, $middle, $last])
            ->map(fn ($part) => trim((string) $part))
            ->filter(fn ($part) => $part !== '')
            ->implode(' ');
    }

    /** @return array{first_name: string, middle_name: ?string, last_name: string} */
    public static function split(string $full): array
    {
        $full = trim(preg_replace('/\s+/', ' ', $full) ?? '');

        if ($full === '') {
            return ['first_name' => '', 'middle_name' => null, 'last_name' => ''];
        }

        if (str_contains($full, ',')) {
            [$last, $rest] = array_pad(array_map('trim', explode(',', $full, 2)), 2, '');
            $parts = $rest === '' ? [] : explode(' ', $rest);
            $first = array_shift($parts) ?? '';
            $middle = $parts !== [] ? implode(' ', $parts) : null;

            return [
                'first_name' => $first,
                'middle_name' => $middle ?: null,
                'last_name' => $last,
            ];
        }

        $parts = explode(' ', $full);
        if (count($parts) === 1) {
            return ['first_name' => $parts[0], 'middle_name' => null, 'last_name' => $parts[0]];
        }

        $last = array_pop($parts) ?: '';
        $first = array_shift($parts) ?: '';
        $middle = $parts !== [] ? implode(' ', $parts) : null;

        return [
            'first_name' => $first,
            'middle_name' => $middle,
            'last_name' => $last,
        ];
    }
}
