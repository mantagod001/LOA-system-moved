<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\LoaAccessToken;
use App\Models\Program;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GrammarCheckTest extends TestCase
{
    use RefreshDatabase;

    public function test_grammar_check_returns_suggested_text(): void
    {
        Http::fake([
            'api.languagetool.org/*' => Http::response([
                'matches' => [
                    [
                        'offset' => 2,
                        'length' => 5,
                        'replacements' => [['value' => 'need']],
                    ],
                ],
            ], 200),
        ]);

        $this->seed();
        $token = $this->issueFormToken();

        $this->postJson(route('student.form.grammar', $token), [
            'text' => 'I needd a leave of absence.',
        ])->assertOk()
            ->assertJson([
                'original' => 'I needd a leave of absence.',
                'suggested' => 'I need a leave of absence.',
                'changed' => true,
            ]);
    }

    public function test_grammar_check_unavailable_returns_503(): void
    {
        Http::fake([
            'api.languagetool.org/*' => Http::response('down', 500),
        ]);

        $this->seed();
        $token = $this->issueFormToken();

        $this->postJson(route('student.form.grammar', $token), [
            'text' => 'Need leave.',
        ])->assertStatus(503)
            ->assertJsonFragment(['message' => 'Grammar check is not available right now. You can still submit your own wording.']);
    }

    private function issueFormToken(): string
    {
        Department::query()->where('code', 'DCS')->firstOrFail();
        Program::query()->where('code', 'BSIT')->firstOrFail();

        [$record, $plain] = LoaAccessToken::issueForEmail('juan@evsu.edu.ph', [
            'student_id' => '2021-0001',
            'first_name' => 'Juan',
            'last_name' => 'Cruz',
        ]);
        $record->update(['otp_verified' => true]);

        return $plain;
    }
}
