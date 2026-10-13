<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class GrammarCheckService
{
    /**
     * @return array{original: string, suggested: string, changed: bool, fixes: int}
     */
    public function polish(string $text): array
    {
        $response = Http::asForm()
            ->timeout(8)
            ->withUserAgent('LeaveFlow-EVSU-Ormoc/1.0')
            ->post('https://api.languagetool.org/v2/check', [
                'text' => $text,
                'language' => 'en-US',
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('Grammar service unavailable.');
        }

        $matches = $response->json('matches') ?? [];
        if (! is_array($matches)) {
            $matches = [];
        }

        usort($matches, fn ($a, $b) => ((int) ($b['offset'] ?? 0)) <=> ((int) ($a['offset'] ?? 0)));

        $suggested = $text;
        $fixes = 0;

        foreach ($matches as $match) {
            if (! is_array($match)) {
                continue;
            }

            $replacement = $match['replacements'][0]['value'] ?? null;
            if (! is_string($replacement)) {
                continue;
            }

            $offset = (int) ($match['offset'] ?? -1);
            $length = (int) ($match['length'] ?? 0);
            $chars = mb_strlen($suggested);

            if ($offset < 0 || $offset > $chars || $length < 0 || ($offset + $length) > $chars) {
                continue;
            }

            $suggested = mb_substr($suggested, 0, $offset).$replacement.mb_substr($suggested, $offset + $length);
            $fixes++;
        }

        return [
            'original' => $text,
            'suggested' => $suggested,
            'changed' => $suggested !== $text,
            'fixes' => $fixes,
        ];
    }
}
