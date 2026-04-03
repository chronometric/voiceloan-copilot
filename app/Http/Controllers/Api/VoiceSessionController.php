<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Voice\VoicePatchBorrowerRequest;
use App\Http\Resources\BorrowerResource;
use App\Models\Borrower;
use App\Models\VoiceCallSession;
use App\Services\Urla1003\Urla1003PromptService;
use App\Services\Urla1003\UrlaConversationStateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VoiceSessionController extends Controller
{
    /**
     * Bind Twilio CallSid (or relay session id) to a borrower for tool calls.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'call_sid' => ['required', 'string', 'max:64'],
            'borrower_uuid' => ['required', 'uuid', 'exists:borrowers,uuid'],
        ]);

        $ttl = config('voice.session_ttl_hours', 4);

        VoiceCallSession::updateOrCreate(
            ['call_sid' => $data['call_sid']],
            [
                'borrower_uuid' => $data['borrower_uuid'],
                'expires_at' => now()->addHours($ttl),
            ],
        );

        return response()->json(['ok' => true]);
    }

    public function showBorrower(string $callSid): BorrowerResource
    {
        $session = $this->resolveSession($callSid);
        $borrower = $this->borrowerForSession($session);

        $borrower->load(['identity', 'employments', 'assets', 'declaration']);

        return new BorrowerResource($borrower);
    }

    /**
     * Partial update; returns 422 with Laravel validation errors for voice re-prompts.
     */
    public function updateBorrower(VoicePatchBorrowerRequest $request, string $callSid): BorrowerResource
    {
        $session = $this->resolveSession($callSid);
        $borrower = $this->borrowerForSession($session);

        $validated = $request->validated();
        $borrower->update($validated);

        $borrower->refresh()->load(['identity', 'employments', 'assets', 'declaration']);

        $touched = array_map(static fn (string $k): string => 'borrower.'.$k, array_keys($validated));
        app(UrlaConversationStateService::class)->syncAfterBorrowerPatch($borrower, $callSid, $touched);

        return new BorrowerResource($borrower);
    }

    /**
     * URLA / 1003 prompt pack: system + section + missing fields + compact snapshot + state.
     */
    public function urlaContext(string $callSid): JsonResponse
    {
        $session = $this->resolveSession($callSid);
        $borrower = $this->borrowerForSession($session);

        $stateSvc = app(UrlaConversationStateService::class);
        $state = $stateSvc->getOrCreate($borrower, $callSid);
        $pack = app(Urla1003PromptService::class)->buildPack($borrower, $state);

        return response()->json($pack);
    }

    /**
     * Update conversational section/stage or increment clarification count for a field path.
     */
    public function updateUrlaState(Request $request, string $callSid): JsonResponse
    {
        $session = $this->resolveSession($callSid);
        $borrower = $this->borrowerForSession($session);

        $sections = array_keys(config('urla1003.prompts.sections', []));
        if (! $request->hasAny(['current_stage', 'current_section']) && ! $request->filled('increment_clarification_for')) {
            abort(422, 'Provide current_stage, current_section, or increment_clarification_for.');
        }

        $data = $request->validate([
            'current_stage' => ['sometimes', 'string', Rule::in(config('urla1003.stages', []))],
            'current_section' => ['sometimes', 'string', Rule::in($sections)],
            'increment_clarification_for' => ['sometimes', 'string', 'max:128'],
        ]);

        $stateSvc = app(UrlaConversationStateService::class);

        if ($request->filled('increment_clarification_for')) {
            $state = $stateSvc->incrementClarification($borrower, $callSid, $data['increment_clarification_for']);
        } else {
            $state = $stateSvc->updateSectionAndStage(
                $borrower,
                $callSid,
                $data['current_stage'] ?? null,
                $data['current_section'] ?? null,
            );
        }

        $pack = app(Urla1003PromptService::class)->buildPack($borrower, $state);

        return response()->json([
            'state' => [
                'current_stage' => $state->current_stage,
                'current_section' => $state->current_section,
                'clarification_counts' => $state->clarification_counts ?? [],
                'last_tool_results' => $state->last_tool_results,
            ],
            'prompt' => $pack,
        ]);
    }

    private function resolveSession(string $callSid): VoiceCallSession
    {
        $session = VoiceCallSession::query()->where('call_sid', $callSid)->first();
        if ($session === null || $session->isExpired()) {
            abort(404, 'Unknown or expired voice session.');
        }

        return $session;
    }

    private function borrowerForSession(VoiceCallSession $session): Borrower
    {
        $borrower = Borrower::query()->where('uuid', $session->borrower_uuid)->first();
        if ($borrower === null) {
            abort(404, 'Borrower not found.');
        }

        return $borrower;
    }
}
