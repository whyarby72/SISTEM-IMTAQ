<?php

namespace App\Domains\Academic\Services;

use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Models\ClassHomeroomAssignment;
use App\Domains\Academic\Models\SemesterSubjectGrade;
use App\Domains\Academic\Models\StudentClassEnrollment;
use App\Models\User;
use App\Shared\Platform\Audit\Services\AuditLogger;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class SemesterGradeFinalizationService
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function check(SemesterSubjectGrade $grade, AcademicClass $class, User $actor, int $expectedVersion): SemesterSubjectGrade
    {
        $this->authorizeWali($class, $grade, $actor);
        if ($grade->workflow_status !== 'DRAFT' || $grade->score === null) {
            throw new InvalidArgumentException('Only complete DRAFT grades may be checked.');
        }

        return DB::transaction(function () use ($grade, $actor, $expectedVersion): SemesterSubjectGrade {
            $locked = SemesterSubjectGrade::query()->whereKey($grade->id)->lockForUpdate()->firstOrFail();
            if ($locked->version_no !== $expectedVersion || $locked->workflow_status !== 'DRAFT') {
                throw new InvalidArgumentException('Semester grade version or workflow is stale.');
            }
            $before = $locked->version_no;
            $locked->update(['workflow_status' => 'CHECKED', 'version_no' => $before + 1, 'updated_by' => $actor->id, 'updated_at' => now()]);
            $this->auditLogger->record([
                'actor_user_id' => $actor->id, 'action' => 'SEMESTER_SUBJECT_GRADE_CHECKED',
                'entity_type' => SemesterSubjectGrade::class, 'entity_id' => (string) $locked->id,
                'version_before' => $before, 'version_after' => $locked->version_no,
                'old_values' => ['workflow_status' => 'DRAFT'], 'new_values' => ['workflow_status' => 'CHECKED'],
            ]);

            return $locked->fresh();
        });
    }

    public function approveAndLock(SemesterSubjectGrade $grade, User $actor, int $expectedVersion): SemesterSubjectGrade
    {
        if (! $this->hasRole($actor, 'WAKA_AKADEMIK')) {
            throw new AuthorizationException('Only Waka Akademik may approve and lock semester grades.');
        }
        if ($grade->workflow_status !== 'CHECKED' || $grade->score === null) {
            throw new InvalidArgumentException('Only checked complete grades may be approved and locked.');
        }

        return DB::transaction(function () use ($grade, $actor, $expectedVersion): SemesterSubjectGrade {
            $locked = SemesterSubjectGrade::query()->whereKey($grade->id)->lockForUpdate()->firstOrFail();
            if ($locked->version_no !== $expectedVersion || $locked->workflow_status !== 'CHECKED') {
                throw new InvalidArgumentException('Semester grade version or workflow is stale.');
            }
            $before = $locked->version_no;
            $locked->update(['workflow_status' => 'LOCKED', 'version_no' => $before + 1, 'finalized_by' => $actor->id, 'finalized_at' => now(), 'updated_by' => $actor->id, 'updated_at' => now()]);
            $this->auditLogger->record([
                'actor_user_id' => $actor->id, 'action' => 'SEMESTER_SUBJECT_GRADE_LOCKED',
                'entity_type' => SemesterSubjectGrade::class, 'entity_id' => (string) $locked->id,
                'version_before' => $before, 'version_after' => $locked->version_no,
                'old_values' => ['workflow_status' => 'CHECKED'], 'new_values' => ['workflow_status' => 'LOCKED', 'finalized_by' => $actor->id],
            ]);

            return $locked->fresh();
        });
    }

    private function authorizeWali(AcademicClass $class, SemesterSubjectGrade $grade, User $actor): void
    {
        if (! $this->hasRole($actor, 'WALI_KELAS')) {
            throw new AuthorizationException('Only Wali Kelas may check semester grades.');
        }
        $semesterDate = $grade->semester->starts_on->toDateString();
        $staffLink = $actor->staffLink()
            ->where(fn ($query) => $query->whereNull('effective_from')->orWhereDate('effective_from', '<=', $semesterDate))
            ->where(fn ($query) => $query->whereNull('effective_until')->orWhereDate('effective_until', '>', $semesterDate))
            ->first();
        $assigned = $staffLink !== null && ClassHomeroomAssignment::query()
            ->where('class_id', $class->id)->where('staff_id', $staffLink->staff_id)->where('status', 'ACTIVE')
            ->whereDate('effective_from', '<=', $semesterDate)
            ->where(fn ($query) => $query->whereNull('effective_until')->orWhereDate('effective_until', '>', $semesterDate))->exists();
        $enrolled = StudentClassEnrollment::query()
            ->where('student_id', $grade->student_id)->where('class_id', $class->id)->where('status', 'ACTIVE')
            ->whereDate('effective_from', '<=', $grade->semester->ends_on->toDateString())
            ->where(fn ($query) => $query->whereNull('effective_until')->orWhereDate('effective_until', '>', $grade->semester->starts_on->toDateString()))->exists();
        if (! $assigned || ! $enrolled) {
            throw new AuthorizationException('Actor is not the effective Wali Kelas for this student and semester.');
        }
    }

    private function hasRole(User $actor, string $roleCode): bool
    {
        return $actor->roleAssignments()->effectiveAt()->whereHas('role', fn ($query) => $query->where('code', $roleCode))->exists();
    }
}
