<?php

namespace App\Domains\Academic\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use InvalidArgumentException;

class ClassSession extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'session_code', 'teaching_assignment_id', 'schedule_rule_id', 'class_id', 'subject_id',
        'location_id', 'planned_start_at', 'planned_end_at', 'actual_start_at', 'actual_end_at',
        'session_source', 'participant_scope', 'session_status', 'rescheduled_from_session_id', 'notes', 'version_no',
    ];

    protected $attributes = ['version_no' => 1];

    protected static function booted(): void
    {
        static::saving(function (self $session): void {
            if ($session->planned_end_at->lessThanOrEqualTo($session->planned_start_at)) {
                throw new InvalidArgumentException('planned_end_at must be after planned_start_at.');
            }
        });
    }

    protected function casts(): array
    {
        return [
            'planned_start_at' => 'datetime', 'planned_end_at' => 'datetime',
            'actual_start_at' => 'datetime', 'actual_end_at' => 'datetime', 'version_no' => 'integer',
        ];
    }

    public function scheduleRule(): BelongsTo
    {
        return $this->belongsTo(ScheduleRule::class);
    }

    public function teachingAssignment(): BelongsTo
    {
        return $this->belongsTo(TeachingAssignment::class);
    }

    public function academicClass(): BelongsTo
    {
        return $this->belongsTo(AcademicClass::class, 'class_id');
    }

    public function scopeGroups(): HasMany
    {
        return $this->hasMany(ClassSessionGroup::class);
    }

    public function studentParticipants(): HasMany
    {
        return $this->hasMany(SessionStudentParticipant::class);
    }

    public function teacherParticipations(): HasMany
    {
        return $this->hasMany(SessionTeacherParticipation::class);
    }

    public function scheduleChanges(): HasMany
    {
        return $this->hasMany(ScheduleChange::class, 'source_session_id');
    }
}
