<?php

namespace App\Services\Urla1003;

use App\Models\Borrower;
use App\Models\UrlaConversationState;

class Urla1003PromptService
{
    public function __construct(
        private Urla1003SnapshotService $snapshots,
    ) {}

    /**
     * @return array{system: string, section_instruction: string, missing_fields: list<array{path: string, label: string, section: string}>, snapshot_compact: array<string, mixed>, stage: string, section: string, clarification_counts: array<string, int>, compliance: array{voice_disclaimer: string, sms_footer: string}}
     */
    public function buildPack(Borrower $borrower, UrlaConversationState $state): array
    {
        $stage = $state->current_stage;
        $section = $state->current_section;

        $missing = $this->snapshots->missingForStage($borrower, $stage);
        $missingPaths = array_column($missing, 'path');
        $compact = $this->snapshots->compactSnapshot($borrower, $section, $missingPaths);

        $system = trim((string) config('urla1003.prompts.system', ''));
        $sectionInstruction = trim((string) (config('urla1003.prompts.sections.'.$section) ?? ''));

        $system .= "\n\nCurrent stage: {$stage}. Current section: {$section}.";
        $system .= "\nPrioritize asking about MISSING fields first (listed below).";

        $voiceDisclaimer = trim((string) config('compliance.voice_disclaimer', ''));
        if ($voiceDisclaimer !== '') {
            $system .= "\n\n--- Compliance ---\n".$voiceDisclaimer;
        }
        $system .= "\n\nIf the borrower asks for a human, legal advice, or disputes terms, use the transfer_to_human tool.";

        $clarification = $state->clarification_counts ?? [];

        return [
            'system' => $system,
            'section_instruction' => $sectionInstruction,
            'missing_fields' => $missing,
            'snapshot_compact' => $compact,
            'stage' => $stage,
            'section' => $section,
            'clarification_counts' => is_array($clarification) ? $clarification : [],
            'compliance' => [
                'voice_disclaimer' => $voiceDisclaimer,
                'sms_footer' => trim((string) config('compliance.sms_footer', '')),
            ],
        ];
    }
}
