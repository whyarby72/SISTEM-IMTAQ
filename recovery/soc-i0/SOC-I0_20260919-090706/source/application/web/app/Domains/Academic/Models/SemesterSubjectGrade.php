<?php

namespace App\Domains\Academic\Models;

use App\Models\User;
use App\Shared\Core\Models\Staff;
use App\Shared\Core\Models\Student;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SemesterSubjectGrade extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'semester_subject_grades';

    protected $fillable = [
        'student_id', 'semester_id', 'subject_id', 'score', 'grade_source',
        'source_teaching_assignment_id', 'responsible_staff_id', 'workflow_status',
        'version_no', 'entered_by', 'entered_at', 'finalized_by', 'finalized_at',
        'updated_by', 'updated_at',
    ];

    protected $attributes = ['grade_source' => 'DIRECT_ENTRY', 'workflow_status' => 'DRAFT', 'version_no' => 1];

    protected function casts(): array
    {
        return [
            'score' => 'decimal:2', 'entered_at' => 'datetime', 'finalized_at' => 'datetime',
            'updated_at' => 'datetime', 'version_no' => 'integer',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function sourceTeachingAssignment(): BelongsTo
    {
        return $this->belongsTo(TeachingAssignment::class, 'source_teaching_assignment_id');
    }

    public function responsibleStaff(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'responsible_staff_id');
    }

    public function enteredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'entered_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
