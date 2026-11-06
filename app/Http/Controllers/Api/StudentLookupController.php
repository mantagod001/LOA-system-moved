<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\StudentWhitelistService;
use App\Support\StudentId;
use Illuminate\Http\JsonResponse;

class StudentLookupController extends Controller
{
    public function show(string $student_id, StudentWhitelistService $service): JsonResponse
    {
        $normalized = StudentId::normalize($student_id);

        if ($normalized === null) {
            return response()->json([
                'found' => false,
                'message' => 'Student number must follow the format YYYY-NNNNN, e.g. 2021-12345.',
            ], 422);
        }

        $payload = $service->lookupPayload($normalized);

        if (! $payload['found']) {
            return response()->json($payload, 404);
        }

        return response()->json($payload);
    }
}
