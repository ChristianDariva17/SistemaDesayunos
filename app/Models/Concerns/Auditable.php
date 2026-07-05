<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Models\Audit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

trait Auditable
{
    protected static function bootAuditable(): void
    {
        static::created(function (Model $model): void {
            self::recordAuditSafely($model, 'created', [], self::currentAuditableValues($model));
        });

        static::updated(function (Model $model): void {
            $newValues = self::changedAuditableValues($model);

            if ($newValues === []) {
                return;
            }

            $oldValues = [];
            foreach (array_keys($newValues) as $attribute) {
                $oldValues[$attribute] = self::normalizeAuditValue($model->getOriginal($attribute));
            }

            self::recordAuditSafely($model, 'updated', $oldValues, $newValues);
        });

        static::deleted(function (Model $model): void {
            self::recordAuditSafely($model, 'deleted', self::currentAuditableValues($model), []);
        });
    }

    /**
     * @return array<int, string>
     */
    protected function auditableAttributes(): array
    {
        return [];
    }

    /**
     * @return array<string, mixed>
     */
    private static function currentAuditableValues(Model $model): array
    {
        $values = [];

        foreach (self::auditableAttributesFor($model) as $attribute) {
            $values[$attribute] = self::normalizeAuditValue($model->getAttribute($attribute));
        }

        return $values;
    }

    /**
     * @return array<string, mixed>
     */
    private static function changedAuditableValues(Model $model): array
    {
        $values = [];

        foreach (self::auditableAttributesFor($model) as $attribute) {
            if (! $model->wasChanged($attribute)) {
                continue;
            }

            $values[$attribute] = self::normalizeAuditValue($model->getAttribute($attribute));
        }

        return $values;
    }

    /**
     * @return array<int, string>
     */
    private static function auditableAttributesFor(Model $model): array
    {
        if (! method_exists($model, 'auditableAttributes')) {
            return [];
        }

        return $model->auditableAttributes();
    }

    /**
     * @param  array<string, mixed>  $oldValues
     * @param  array<string, mixed>  $newValues
     */
    private static function recordAuditSafely(Model $model, string $action, array $oldValues, array $newValues): void
    {
        if ($oldValues === [] && $newValues === []) {
            return;
        }

        try {
            Audit::create([
                'user_id' => Auth::id(),
                'auditable_type' => $model::class,
                'auditable_id' => $model->getKey(),
                'auditable_table' => $model->getTable(),
                'action' => $action,
                'old_values' => $oldValues === [] ? null : $oldValues,
                'new_values' => $newValues === [] ? null : $newValues,
                'audited_at' => now(),
            ]);
        } catch (Throwable $throwable) {
            Log::error('Business audit write failed.', [
                'auditable_type' => $model::class,
                'auditable_id' => $model->getKey(),
                'action' => $action,
                'error' => $throwable->getMessage(),
            ]);
        }
    }

    private static function normalizeAuditValue(mixed $value): mixed
    {
        if (is_float($value)) {
            return number_format($value, 2, '.', '');
        }

        return $value;
    }
}
