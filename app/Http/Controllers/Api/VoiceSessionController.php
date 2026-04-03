<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Voice\VoicePatchBorrowerRequest;
use App\Http\Resources\BorrowerResource;
use App\Models\Borrower;
use App\Models\VoiceCallSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

        $borrower->update($request->validated());

        $borrower->refresh()->load(['identity', 'employments', 'assets', 'declaration']);

        return new BorrowerResource($borrower);
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
