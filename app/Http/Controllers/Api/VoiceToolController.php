<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BorrowerResource;
use App\Models\Borrower;
use App\Models\VoiceCallSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Single entry for OpenAI Realtime tool calls from the voice-bridge (JSON in/out).
 */
class VoiceToolController extends Controller
{
    public function execute(Request $request): JsonResponse
    {
        $base = $request->validate([
            'call_sid' => ['required', 'string', 'max:64'],
            'name' => ['required', 'string', 'max:64'],
            'arguments' => ['nullable', 'array'],
        ]);

        $session = VoiceCallSession::query()->where('call_sid', $base['call_sid'])->first();
        if ($session === null || $session->isExpired()) {
            return response()->json([
                'ok' => false,
                'error' => 'unknown_or_expired_session',
                'message' => 'Unknown or expired voice session.',
            ], 404);
        }

        $borrower = Borrower::query()->where('uuid', $session->borrower_uuid)->first();
        if ($borrower === null) {
            return response()->json([
                'ok' => false,
                'error' => 'borrower_not_found',
                'message' => 'Borrower not found.',
            ], 404);
        }

        $name = $base['name'];
        $args = $base['arguments'] ?? [];

        return match ($name) {
            'get_borrower' => $this->toolGetBorrower($borrower),
            'patch_borrower' => $this->toolPatchBorrower($borrower, $args),
            default => response()->json([
                'ok' => false,
                'error' => 'unknown_tool',
                'message' => 'Unknown tool: '.$name,
            ], 422),
        };
    }

    private function toolGetBorrower(Borrower $borrower): JsonResponse
    {
        $borrower->load(['identity', 'employments', 'assets', 'declaration']);

        return response()->json([
            'ok' => true,
            'data' => (new BorrowerResource($borrower))->resolve(),
        ]);
    }

    private function toolPatchBorrower(Borrower $borrower, array $args): JsonResponse
    {
        $validator = Validator::make($args, [
            'display_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:32'],
            'status' => ['sometimes', 'string', 'max:32'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'ok' => false,
                'error' => 'validation_failed',
                'message' => 'Validation failed.',
                'errors' => $validator->errors()->toArray(),
            ], 422);
        }

        $borrower->update($validator->validated());

        $borrower->refresh()->load(['identity', 'employments', 'assets', 'declaration']);

        return response()->json([
            'ok' => true,
            'data' => (new BorrowerResource($borrower))->resolve(),
        ]);
    }
}
