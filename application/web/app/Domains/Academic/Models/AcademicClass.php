<?php

namespace App\Domains\Academic\Models;

use App\Shared\Core\Models\AcademicYear;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicClass extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'classes';

    protected $fillable = ['class_code', 'academic_year_id', 'organizational_unit_id', 'grade_level_id', 'section_code', 'display_name', 'status', 'version_no'];

    protected $attributes = ['status' => 'ACTIVE', 'version_no' => 1];

    protected function casts(): array
    {
        return ['version_no' => 'integer'];
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function gradeLevel(): BelongsTo
    {
        return $this->belongsTo(GradeLevel::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(StudentClassEnrollment::class, 'class_id');
    }

    public function homeroomAssignments(): HasMany
    {
        return $this->hasMany(ClassHomeroomAssignment::class, 'class_id');
    }

    public function calendarEvents(): HasMany
    {
        return $this->hasMany(AcademicCalendarEvent::class, 'class_id');
    }

    public function teachingAssignments(): HasMany
    {
        return $this->hasMany(TeachingAssignment::class, 'class_id');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(ClassSession::class, 'class_id');
    }
}
