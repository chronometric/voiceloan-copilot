<?php

namespace App\Services\Urla1003;

use App\Models\Borrower;
use App\Models\UrlaConversationState;
use Illuminate\Support\Facades\DB;

class UrlaConversationStateService
{
    public function __construct(
        private Urla1003PromptService $prompts,
    ) {}

    public function getOrCreate(Borrower $borrower, ?string $callSid = null): UrlaConversationState
    {
        return DB::transaction(function () use ($borrower, $callSid) {
            $state = UrlaConversationState::query()->firstOrCreate(
                ['borrower_id' => $borrower->id],
                [
                    'call_sid' => $callSid,
                    'current_stage' => 'intake',
                    'current_section' => 'borrower',
                    'clarification_counts' => [],
                    'last_tool_results' => null,
                ],
            );

            if ($callSid !== null && $state->call_sid !== $callSid) {
                $state->forceFill(['call_sid' => $callSid])->save();
            }

            return $state->fresh();
        });
    }

    /**
     * After a successful borrower PATCH (voice or API): refresh last_tool_results, clear clarification for filled paths.
     *
     * @param  list<string>  $touchedPaths  logical paths that may have changed (optional)
     */
    public function syncAfterBorrowerPatch(Borrower $borrower, ?string $callSid, array $touchedPaths = []): UrlaConversationState
    {
        $borrower->refresh()->load(['identity', 'employments', 'assets', 'declaration']);

        $resolver = app(Urla1003FieldResolver::class);
        $state = $this->getOrCreate($borrower, $callSid);

        $counts = $state->clarification_counts ?? [];
        foreach ($touchedPaths as $path) {
            if (! $resolver->isMissing($borrower, $path)) {
                unset($counts[$path]);
            }
        }

        $state->forceFill(['clarification_counts' => $counts])->save();
        $state->refresh();

        $pack = $this->prompts->buildPack($borrower, $state);

        $state->forceFill([
            'last_tool_results' => [
                'updated_at' => now()->toIso8601String(),
                'snapshot_compact' => $pack['snapshot_compact'],
                'missing_fields' => $pack['missing_fields'],
                'stage' => $pack['stage'],
                'section' => $pack['section'],
            ],
        ])->save();

        return $state->fresh();
    }

    public function recordToolResult(Borrower $borrower, ?string $callSid, array $payload): UrlaConversationState
    {
        $state = $this->getOrCreate($borrower, $callSid);
        $prev = $state->last_tool_results ?? [];
        $state->forceFill([
            'last_tool_results' => array_merge(is_array($prev) ? $prev : [], [
                'last_call' => $payload,
                'recorded_at' => now()->toIso8601String(),
            ]),
        ])->save();

        return $state->fresh();
    }

    public function updateSectionAndStage(
        Borrower $borrower,
        ?string $callSid,
        ?string $stage = null,
        ?string $section = null,
    ): UrlaConversationState {
        $state = $this->getOrCreate($borrower, $callSid);
        if ($stage !== null) {
            $state->current_stage = $stage;
        }
        if ($section !== null) {
            $state->current_section = $section;
        }
        $state->save();

        return $this->syncAfterBorrowerPatch($borrower, $callSid, []);
    }

    public function incrementClarification(Borrower $borrower, ?string $callSid, string $path): UrlaConversationState
    {
        $state = $this->getOrCreate($borrower, $callSid);
        $counts = $state->clarification_counts ?? [];
        $counts[$path] = ($counts[$path] ?? 0) + 1;
        $state->forceFill(['clarification_counts' => $counts])->save();

        return $this->syncAfterBorrowerPatch($borrower, $callSid, []);
    }
}
