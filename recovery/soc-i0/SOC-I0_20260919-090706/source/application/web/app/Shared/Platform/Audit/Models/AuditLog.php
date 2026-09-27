<?php

namespace App\Shared\Platform\Audit\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use LogicException;

class AuditLog extends Model
{
    use HasFactory, HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'occurred_at', 'actor_user_id', 'actor_type', 'action', 'entity_type', 'entity_id',
        'version_before', 'version_after', 'old_values', 'new_values', 'reason', 'source_channel',
        'correlation_id', 'request_id', 'correction_request_id', 'technical_metadata',
    ];

    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
            'old_values' => 'array',
            'new_values' => 'array',
            'technical_metadata' => 'array',
            'version_before' => 'integer',
            'version_after' => 'integer',
        ];
    }

    protected function performUpdate(Builder $query): bool
    {
        throw new LogicException('Audit logs are append-only.');
    }

    protected function performDeleteOnModel(): void
    {
        throw new LogicException('Audit logs are append-only.');
    }
}
