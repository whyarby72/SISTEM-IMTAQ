<?php

namespace App\Domains\Academic\Models;

use App\Shared\Core\Models\Staff;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use InvalidArgumentException;

class ClassHomeroomAssignment extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'class_id', 'staff_id', 'effective_from', 'effective_until', 'status',
        'assigned_by_user_id', 'assigned_at', 'reason', 'version_no',
    ];

    protected $attributes = ['status' => 'ACTIVE', 'version_no' => 1];

    protected static function booted(): void
    {
        static::saving(function (self $assignment): void {
            $from = $assignment->effective_from;
            $until = $assignment->effective_until;

            if ($until !== null && $until->lessThanOrEqualTo($from)) {
                throw new InvalidArgumentException('effective_until must be after effective_from.');
            }

            $overlap = self::query()
                ->where('class_id', $assignment->class_id)
                ->when($assignment->exists, fn ($query) => $query->whereKeyNot($assignment->getKey()))
                ->where('effective_from', '<', $until ?? '9999-12-31')
                ->where(function ($query) use ($from): void {
                    $query->whereNull('effective_until')
                        ->orWhere('effective_until', '>', $from);
                })
                ->exists();

            if ($overlap) {
                throw new InvalidArgumentException('Class homeroom assignments must not overlap.');
            }
        });
    }

    protected function casts(): array
    {
        return [
            'effective_from' => 'date', 'effective_until' => 'date',
            'assigned_at' => 'datetime', 'version_no' => 'integer',
        ];
    }

    public function academicClass(): BelongsTo
    {
        return $this->belongsTo(AcademicClass::class, 'class_id');
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }
}
