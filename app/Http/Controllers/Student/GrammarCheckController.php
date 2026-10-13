<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\LoaAccessToken;
use App\Models\LoaRequest;
use App\Services\GrammarCheckService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class GrammarCheckController extends Controller
{
    public function form(Request $request, string $token, GrammarCheckService $grammar): JsonResponse
    {
        if (! $this->validFormToken($token)) {
            return response()->json(['message' => 'That form link is invalid or has expired.'], 403);
        }

        return $this->check($request, $grammar);
    }

    public function resubmit(Request $request, string $token, GrammarCheckService $grammar): JsonResponse
    {
        if (! LoaRequest::findByResubmitToken($token)) {
            return response()->json(['message' => 'That resubmit link is invalid or has expired.'], 403);
        }

        return $this->check($request, $grammar);
    }

    private function check(Request $request, GrammarCheckService $grammar): JsonResponse
    {
        $validated = $request->validate([
            'text' => ['required', 'string', 'max:5000'],
        ]);

        try {
            $result = $grammar->polish($validated['text']);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Grammar check is not available right now. You can still submit your own wording.',
            ], 503);
        }

        return response()->json($result);
    }

    private function validFormToken(string $plain): bool
    {
        if (! preg_match('/^[A-Za-z0-9]{40}$/', $plain)) {
            return false;
        }

        $access = LoaAccessToken::query()
            ->where('token_hash', LoaAccessToken::hashPlainToken($plain))
            ->first();

        if (! $access || $access->isExpired() || ! $access->otp_verified) {
            return false;
        }

        $loa = $access->loaRequest;

        return ! $loa || ! $loa->isSubmitted();
    }
}
