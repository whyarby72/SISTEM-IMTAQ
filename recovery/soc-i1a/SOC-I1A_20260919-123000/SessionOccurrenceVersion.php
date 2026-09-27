<?php

namespace App\Domains\Academic\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use InvalidArgumentException;
use LogicException;

class SessionOccurrenceVersion extends Model
{
    use HasUuids;

    public const STATUSES = ['SCHEDULED', 'HELD', 'CANCELLED', 'RESCHEDULED'];

    protected $fillable = [
        'class_session_id', 'version_no', 'occurrence_status', 'recorded_by_user_id',
        'reason', 'is_partial', 'partial_reason', 'supersedes_version_id',
        'replacement_class_session_id', 'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'version_no' => 'integer',
            'is_partial' => 'boolean',
            'recorded_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $version): void {
            if (! in_array($version->occurrence_status, self::STATUSES, true)) {
                throw new InvalidArgumentException('Invalid canonical occurrence status.');
            }
        });

        static::updating(function (): void {
            throw new LogicException('Session occurrence versions are append-only.');
        });

        static::deleting(function (): void {
            throw new LogicException('Session occurrence versions are immutable history.');
        });
    }

    public function classSession(): BelongsTo
    {
        return $this->belongsTo(ClassSession::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by_user_id');
    }

    public function supersedes(): BelongsTo
    {
        return $this->belongsTo(self::class, 'supersedes_version_id');
    }

    public function replacementClassSession(): BelongsTo
    {
        return $this->belongsTo(ClassSession::class, 'replacement_class_session_id');
    }
}
