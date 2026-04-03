<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Borrower;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditLogger
{
    public static function logCreated(Model $model): void
    {
        $borrowerId = self::borrowerIdFor($model);
        if ($borrowerId === null) {
            return;
        }

        self::write($borrowerId, $model, 'created', null, self::sanitizeAttributes($model->getAttributes()));
    }

    public static function logUpdated(Model $model): void
    {
        $borrowerId = self::borrowerIdFor($model);
        if ($borrowerId === null) {
            return;
        }

        $changes = $model->getChanges();
        if ($changes === []) {
            return;
        }

        unset($changes['updated_at']);

        $old = [];
        foreach (array_keys($changes) as $key) {
            $old[$key] = $model->getOriginal($key);
        }

        self::write($borrowerId, $model, 'updated', $old, $changes);
    }

    public static function logDeleted(Model $model): void
    {
        $borrowerId = self::borrowerIdFor($model);
        if ($borrowerId === null) {
            return;
        }

        self::write($borrowerId, $model, 'deleted', self::sanitizeAttributes($model->getOriginal()), null);
    }

    /**
     * Non-Eloquent events (SMS, voice tools, etc.) tied to a borrower.
     *
     * @param  array<string, mixed>|null  $newValues
     */
    public static function logBorrowerEvent(int $borrowerId, string $action, string $entityType, ?array $newValues): void
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'borrower_id' => $borrowerId,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => null,
            'old_values' => null,
            'new_values' => $newValues,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
            'created_at' => now(),
        ]);
    }

    private static function write(
        int $borrowerId,
        Model $model,
        string $action,
        ?array $oldValues,
        ?array $newValues,
    ): void {
        AuditLog::create([
            'user_id' => Auth::id(),
            'borrower_id' => $borrowerId,
            'action' => $action,
            'entity_type' => $model::class,
            'entity_id' => $model->getKey(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
            'created_at' => now(),
        ]);
    }

    private static function borrowerIdFor(Model $model): ?int
    {
        if ($model instanceof Borrower) {
            return $model->id;
        }

        if (isset($model->borrower_id)) {
            return (int) $model->borrower_id;
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private static function sanitizeAttributes(array $attributes): array
    {
        unset($attributes['password'], $attributes['remember_token']);

        return $attributes;
    }
}
