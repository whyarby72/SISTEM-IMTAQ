<?php

namespace App\Shared\Platform\Audit\Services;

use App\Shared\Platform\Audit\Models\AuditLog;

class AuditLogger
{
    public function record(array $attributes): AuditLog
    {
        foreach (['old_values', 'new_values', 'technical_metadata'] as $field) {
            if (isset($attributes[$field]) && is_array($attributes[$field])) {
                $attributes[$field] = $this->redactSecrets($attributes[$field]);
            }
        }

        return AuditLog::create([
            'occurred_at' => $attributes['occurred_at'] ?? now(),
            'actor_type' => $attributes['actor_type'] ?? 'USER',
            'source_channel' => $attributes['source_channel'] ?? 'WEB',
            ...$attributes,
        ]);
    }

    private function redactSecrets(array $payload): array
    {
        foreach ($payload as $key => $value) {
            if (preg_match('/password|secret|token|api[_-]?key|authorization/i', (string) $key)) {
                $payload[$key] = '[REDACTED]';
            } elseif (is_array($value)) {
                $payload[$key] = $this->redactSecrets($value);
            }
        }

        return $payload;
    }
}
