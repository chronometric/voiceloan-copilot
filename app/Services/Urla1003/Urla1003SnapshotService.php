<?php

namespace App\Services\Urla1003;

use App\Models\Borrower;
use App\Services\PiiRedactor;

class Urla1003SnapshotService
{
    public function __construct(
        private Urla1003FieldResolver $resolver,
    ) {}

    /**
     * Fields still missing for the current stage (per config required_by_stage).
     *
     * @return list<array{path: string, label: string, section: string}>
     */
    public function missingForStage(Borrower $borrower, string $stage): array
    {
        $required = config('urla1003.required_by_stage.'.$stage, []);
        $fields = config('urla1003.fields', []);
        $out = [];

        foreach ($required as $path) {
            if ($this->resolver->isMissing($borrower, $path)) {
                $meta = $fields[$path] ?? ['label' => $path, 'section' => 'unknown'];
                $out[] = [
                    'path' => $path,
                    'label' => $meta['label'] ?? $path,
                    'section' => $meta['section'] ?? 'unknown',
                ];
            }
        }

        return $out;
    }

    /**
     * Compact snapshot: only keys relevant to section + any missing paths (minimal tokens).
     *
     * @param  list<string>  $extraPaths
     * @return array<string, mixed>
     */
    public function compactSnapshot(Borrower $borrower, string $section, array $extraPaths = []): array
    {
        $borrower->load(['identity', 'employments', 'assets', 'declaration']);

        $paths = $this->pathsForSection($section);
        foreach ($extraPaths as $p) {
            if (! in_array($p, $paths, true)) {
                $paths[] = $p;
            }
        }

        $snap = [];
        foreach ($paths as $path) {
            $v = $this->resolver->value($borrower, $path);
            if (! $this->isEffectivelyEmpty($v)) {
                $snap[$path] = PiiRedactor::maskUrlaSnapshot($path, $v);
            }
        }

        return $snap;
    }

    /**
     * @return list<string>
     */
    public function pathsForSection(string $section): array
    {
        $fields = config('urla1003.fields', []);
        $paths = [];
        foreach ($fields as $path => $meta) {
            if (($meta['section'] ?? '') === $section) {
                $paths[] = $path;
            }
        }

        return $paths;
    }

    private function isEffectivelyEmpty(mixed $v): bool
    {
        if ($v === null) {
            return true;
        }
        if (is_string($v) && trim($v) === '') {
            return true;
        }

        return false;
    }
}
