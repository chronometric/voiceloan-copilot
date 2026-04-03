<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BorrowerResource;
use App\Models\Borrower;
use App\Models\VoiceCallSession;
use App\Services\Urla1003\Urla1003PromptService;
use App\Services\Urla1003\UrlaConversationStateService;
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
        $callSid = $base['call_sid'];

        return match ($name) {
            'get_borrower' => $this->toolGetBorrower($borrower, $callSid),
            'patch_borrower' => $this->toolPatchBorrower($borrower, $args, $callSid),
            'get_urla_context' => $this->toolGetUrlaContext($borrower, $callSid),
            default => response()->json([
                'ok' => false,
                'error' => 'unknown_tool',
                'message' => 'Unknown tool: '.$name,
            ], 422),
        };
    }

    private function toolGetBorrower(Borrower $borrower, string $callSid): JsonResponse
    {
        $borrower->load(['identity', 'employments', 'assets', 'declaration']);

        $stateSvc = app(UrlaConversationStateService::class);
        $stateSvc->syncAfterBorrowerPatch($borrower, $callSid, []);
        $stateSvc->recordToolResult($borrower, $callSid, ['tool' => 'get_borrower']);

        return response()->json([
            'ok' => true,
            'data' => (new BorrowerResource($borrower))->resolve(),
        ]);
    }

    private function toolPatchBorrower(Borrower $borrower, array $args, string $callSid): JsonResponse
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

        $validated = $validator->validated();
        $borrower->update($validated);

        $borrower->refresh()->load(['identity', 'employments', 'assets', 'declaration']);

        $touched = array_map(static fn (string $k): string => 'borrower.'.$k, array_keys($validated));
        app(UrlaConversationStateService::class)->syncAfterBorrowerPatch($borrower, $callSid, $touched);

        return response()->json([
            'ok' => true,
            'data' => (new BorrowerResource($borrower))->resolve(),
        ]);
    }

    private function toolGetUrlaContext(Borrower $borrower, string $callSid): JsonResponse
    {
        $stateSvc = app(UrlaConversationStateService::class);
        $state = $stateSvc->getOrCreate($borrower, $callSid);
        $pack = app(Urla1003PromptService::class)->buildPack($borrower, $state);
        $stateSvc->syncAfterBorrowerPatch($borrower, $callSid, []);
        $stateSvc->recordToolResult($borrower, $callSid, ['tool' => 'get_urla_context']);

        return response()->json([
            'ok' => true,
            'data' => $pack,
        ]);
    }
}
