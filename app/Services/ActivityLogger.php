<?php

namespace App\Services;

use App\Models\ActivityLog;

class ActivityLogger
{
    /**
     * Fields never written to the audit trail.
     */
    private const HIDDEN_FIELDS = [
        'password',
        'remember_token',
        'email_verified_at',
    ];

    /**
     * Log a manual change made through the app UI.
     *
     * @param  string  $action  created|updated|deleted
     * @param  object  $subject  the Eloquent model that changed
     * @param  array  $changes  old => new values (already computed)
     */
    public function log(string $action, object $subject, array $changes = []): void
    {
        $user = auth()->user();

        ActivityLog::create([
            'user_id' => $user?->id,
            'user_name' => $user?->name ?? 'System',
            'action' => $action,
            'subject_type' => $this->subjectType($subject),
            'subject_id' => $subject->id ?? null,
            'subject_label' => $this->subjectLabel($subject),
            'changes' => $this->sanitize($changes),
        ]);
    }

    /**
     * Compute old => new diff from a snapshot taken before the change.
     * Must be captured before save() — Laravel syncs originals on save.
     *
     * @param  array<string, mixed>  $original  getRawOriginal() before update
     * @return array<string, array{old: mixed, new: mixed}>
     */
    public function diff(array $original, object $model): array
    {
        $changes = [];

        foreach ($model->getAttributes() as $field => $new) {
            if (! array_key_exists($field, $original)) {
                continue;
            }

            $old = $original[$field];
            if ((string) $old !== (string) $new) {
                $changes[$field] = ['old' => $old, 'new' => $new];
            }
        }

        return $changes;
    }

    /**
     * @param  array<string, array{old: mixed, new: mixed}>  $changes
     * @return array<string, array{old: mixed, new: mixed}>
     */
    private function sanitize(array $changes): array
    {
        return collect($changes)
            ->reject(fn ($_, $field) => in_array($field, self::HIDDEN_FIELDS, true))
            ->map(fn ($change) => [
                'old' => $this->stringify($change['old'] ?? null),
                'new' => $this->stringify($change['new'] ?? null),
            ])
            ->all();
    }

    private function stringify(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        if (is_array($value)) {
            return json_encode($value) ?: '';
        }

        return (string) $value;
    }

    private function subjectType(object $subject): string
    {
        $class = class_basename($subject);

        return strtolower(preg_replace('/(?<!^)[A-Z]/', ' $0', $class) ?? $class);
    }

    private function subjectLabel(object $subject): string
    {
        return (string) ($subject->name ?? $subject->departure_date?->format('d M Y') ?? $subject->id ?? 'unknown');
    }
}
