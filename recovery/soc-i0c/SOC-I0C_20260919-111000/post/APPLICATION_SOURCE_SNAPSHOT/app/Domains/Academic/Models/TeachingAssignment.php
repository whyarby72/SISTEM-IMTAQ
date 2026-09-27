<?php

namespace App\Domains\Academic\Models;

use App\Shared\Core\Models\Staff;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use InvalidArgumentException;

class TeachingAssignment extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'assignment_code', 'semester_id', 'class_id', 'subject_id', 'teacher_staff_id',
        'effective_from', 'effective_until', 'workflow_status', 'source_reference',
        'created_by_user_id', 'updated_by_user_id', 'version_no',
    ];

    protected $attributes = ['version_no' => 1];

    protected static function booted(): void
    {
        static::saving(function (self $assignment): void {
            if ($assignment->effective_until !== null && $assignment->effective_until->lessThanOrEqualTo($assignment->effective_from)) {
                throw new InvalidArgumentException('effective_until must be after effective_from.');
            }
        });
    }

    protected function casts(): array
    {
        return ['effective_from' => 'date', 'effective_until' => 'date', 'version_no' => 'integer'];
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    public function academicClass(): BelongsTo
    {
        return $this->belongsTo(AcademicClass::class, 'class_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'teacher_staff_id');
    }

    public function scheduleRules(): HasMany
    {
        return $this->hasMany(ScheduleRule::class);
    }
}
