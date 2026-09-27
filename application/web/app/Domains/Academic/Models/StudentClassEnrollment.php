<?php

namespace App\Domains\Academic\Models;

use App\Shared\Core\Models\Student;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use InvalidArgumentException;

class StudentClassEnrollment extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'student_class_enrollments';

    protected $fillable = ['student_id', 'class_id', 'effective_from', 'effective_until', 'status', 'reason', 'source_reference', 'version_no'];

    protected $attributes = ['status' => 'ACTIVE', 'version_no' => 1];

    protected static function booted(): void
    {
        static::saving(function (self $enrollment): void {
            $from = $enrollment->effective_from;
            $until = $enrollment->effective_until;

            if ($until !== null && $until->lessThanOrEqualTo($from)) {
                throw new InvalidArgumentException('effective_until must be after effective_from.');
            }

            $overlap = self::query()
                ->where('student_id', $enrollment->student_id)
                ->when($enrollment->exists, fn ($query) => $query->whereKeyNot($enrollment->getKey()))
                ->where('effective_from', '<', $until ?? '9999-12-31')
                ->where(function ($query) use ($from): void {
                    $query->whereNull('effective_until')
                        ->orWhere('effective_until', '>', $from);
                })
                ->exists();

            if ($overlap) {
                throw new InvalidArgumentException('Student class enrollment intervals must not overlap.');
            }
        });
    }

    protected function casts(): array
    {
        return ['effective_from' => 'date', 'effective_until' => 'date', 'version_no' => 'integer'];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function academicClass(): BelongsTo
    {
        return $this->belongsTo(AcademicClass::class, 'class_id');
    }
}
