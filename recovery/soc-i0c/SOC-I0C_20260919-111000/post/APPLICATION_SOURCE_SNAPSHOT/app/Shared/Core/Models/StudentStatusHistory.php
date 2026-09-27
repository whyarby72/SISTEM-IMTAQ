<?php

namespace App\Shared\Core\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use InvalidArgumentException;

class StudentStatusHistory extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'student_status_history';

    protected $fillable = [
        'student_id', 'status', 'effective_from', 'effective_until',
        'decision_reference', 'reason', 'actor_user_id', 'version_no',
    ];

    protected $attributes = [
        'version_no' => 1,
    ];

    protected static function booted(): void
    {
        static::saving(function (self $history): void {
            $from = $history->effective_from;
            $until = $history->effective_until;

            if ($until !== null && $until->lessThanOrEqualTo($from)) {
                throw new InvalidArgumentException('effective_until must be after effective_from.');
            }

            $overlap = self::query()
                ->where('student_id', $history->student_id)
                ->when($history->exists, fn ($query) => $query->whereKeyNot($history->getKey()))
                ->where('effective_from', '<', $until ?? '9999-12-31')
                ->where(function ($query) use ($from): void {
                    $query->whereNull('effective_until')
                        ->orWhere('effective_until', '>', $from);
                })
                ->exists();

            if ($overlap) {
                throw new InvalidArgumentException('Student status intervals must not overlap.');
            }
        });
    }

    protected function casts(): array
    {
        return [
            'effective_from' => 'date',
            'effective_until' => 'date',
            'version_no' => 'integer',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
