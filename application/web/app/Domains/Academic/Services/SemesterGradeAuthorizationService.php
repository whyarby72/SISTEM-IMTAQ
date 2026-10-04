<?php

namespace App\Domains\Academic\Services;

use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Models\ClassHomeroomAssignment;
use App\Domains\Academic\Models\Semester;
use App\Domains\Academic\Models\Subject;
use App\Domains\Academic\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class SemesterGradeAuthorizationService
{
    public function canViewGradeWorkspace(User $actor, Semester $semester, AcademicClass $class, Subject $subject): bool
    {
        return $this->isWakaAkademik($actor, $semester)
            || $this->isEffectiveWaliForClass($actor, $semester, $class)
            || $this->isAssignedSubjectTeacher($actor, $semester, $class, $subject);
    }

    public function requireViewGradeWorkspace(User $actor, Semester $semester, AcademicClass $class, Subject $subject): void
    {
        if (! $this->canViewGradeWorkspace($actor, $semester, $class, $subject)) {
            throw new AuthorizationException('You are not authorized to view this grade workspace.');
        }
    }

    public function isAssignedSubjectTeacher(User $actor, Semester $semester, AcademicClass $class, Subject $subject): bool
    {
        $staffId = $this->effectiveStaffId($actor, $semester);
        if ($staffId === null) {
            return false;
        }

        return $this->activeAssignments($semester, $class, $subject)
            ->where('teacher_staff_id', $staffId)
            ->exists();
    }

    public function canEnterDraft(User $actor, Semester $semester, AcademicClass $class, Subject $subject): bool
    {
        try {
            $this->assignedTeachingAssignment($actor, $semester, $class, $subject);

            return true;
        } catch (AuthorizationException|InvalidArgumentException) {
            return false;
        }
    }

    public function requireEnterDraft(User $actor, Semester $semester, AcademicClass $class, Subject $subject): TeachingAssignment
    {
        return $this->assignedTeachingAssignment($actor, $semester, $class, $subject);
    }

    public function assignedTeachingAssignment(User $actor, Semester $semester, AcademicClass $class, Subject $subject): TeachingAssignment
    {
        $staffIds = $this->effectiveStaffIds($actor, $semester);
        if ($staffIds->count() !== 1) {
            throw new AuthorizationException('An unambiguous effective Staff identity is required for DRAFT grade entry.');
        }

        $assignments = $this->activeAssignments($semester, $class, $subject)
            ->where('teacher_staff_id', $staffIds->first())
            ->get();

        if ($assignments->isEmpty()) {
            throw new AuthorizationException('Only the assigned subject teacher may enter DRAFT grades.');
        }
        if ($assignments->count() !== 1) {
            throw new InvalidArgumentException('AMBIGUOUS_TEACHING_ASSIGNMENT');
        }

        return $assignments->first();
    }

    public function isEffectiveWaliForClass(User $actor, Semester $semester, AcademicClass $class): bool
    {
        if (! $this->hasRole($actor, 'WALI_KELAS', $semester)) {
            return false;
        }

        $staffId = $this->effectiveStaffId($actor, $semester);
        if ($staffId === null) {
            return false;
        }

        return ClassHomeroomAssignment::query()
            ->where('class_id', $class->id)
            ->where('staff_id', $staffId)
            ->where('status', 'ACTIVE')
            ->whereDate('effective_from', '<=', $semester->ends_on->toDateString())
            ->where(fn ($query) => $query->whereNull('effective_until')->orWhereDate('effective_until', '>', $semester->starts_on->toDateString()))
            ->exists();
    }

    public function isWakaAkademik(User $actor, Semester $semester): bool
    {
        return $this->hasRole($actor, 'WAKA_AKADEMIK', $semester);
    }

    public function scopeType(User $actor, Semester $semester, ?AcademicClass $class = null, ?Subject $subject = null): ?string
    {
        if ($class !== null && $subject !== null && $this->canEnterDraft($actor, $semester, $class, $subject)) {
            return 'SUBJECT_TEACHER';
        }
        if ($this->isWakaAkademik($actor, $semester)) {
            return 'WAKA_AKADEMIK';
        }
        if ($class !== null && $this->isEffectiveWaliForClass($actor, $semester, $class)) {
            return 'WALI_KELAS';
        }

        return null;
    }

    public function hasAnyViewScope(User $actor): bool
    {
        if ($this->hasAnyRole($actor, 'WAKA_AKADEMIK')) {
            return Semester::query()->whereHas('teachingAssignments', fn ($query) => $query->where('workflow_status', 'ACTIVE'))->exists();
        }

        $staffIds = $this->linkedStaffIds($actor);
        $query = Semester::query()->whereHas('teachingAssignments', fn ($assignmentQuery) => $assignmentQuery->where('workflow_status', 'ACTIVE')->whereIn('teacher_staff_id', $staffIds));
        if ($this->hasAnyRole($actor, 'WALI_KELAS')) {
            $query->orWhereHas('teachingAssignments.academicClass.homeroomAssignments', fn ($assignmentQuery) => $assignmentQuery->where('status', 'ACTIVE')->whereIn('staff_id', $staffIds));
        }

        return $query->exists();
    }

    /** @return Collection<int, Semester> */
    public function visibleSemesters(User $actor): Collection
    {
        if ($this->hasAnyRole($actor, 'WAKA_AKADEMIK')) {
            return Semester::query()->where('status', 'ACTIVE')->whereHas('teachingAssignments', fn ($assignmentQuery) => $assignmentQuery->where('workflow_status', 'ACTIVE'))->orderByDesc('starts_on')->get();
        }

        $staffIds = $this->linkedStaffIds($actor);
        $query = Semester::query()->where('status', 'ACTIVE')->whereHas('teachingAssignments', fn ($assignmentQuery) => $assignmentQuery->where('workflow_status', 'ACTIVE')->whereIn('teacher_staff_id', $staffIds));
        if ($this->hasAnyRole($actor, 'WALI_KELAS')) {
            $query->orWhere(function ($waliQuery) use ($staffIds): void {
                $waliQuery->where('status', 'ACTIVE')->whereHas('teachingAssignments.academicClass.homeroomAssignments', fn ($assignmentQuery) => $assignmentQuery->where('status', 'ACTIVE')->whereIn('staff_id', $staffIds));
            });
        }

        return $query->orderByDesc('starts_on')->get();
    }

    /** @return Collection<int, AcademicClass> */
    public function visibleClasses(User $actor, Semester $semester): Collection
    {
        $query = AcademicClass::query()->where('status', 'ACTIVE');
        if ($this->isWakaAkademik($actor, $semester)) {
            return $query->whereHas('teachingAssignments', fn ($assignmentQuery) => $assignmentQuery->where('semester_id', $semester->id)->where('workflow_status', 'ACTIVE'))->orderBy('display_name')->get();
        }

        $staffIds = $this->linkedStaffIds($actor);
        $query->whereHas('teachingAssignments', fn ($assignmentQuery) => $assignmentQuery->where('semester_id', $semester->id)->where('workflow_status', 'ACTIVE')->whereIn('teacher_staff_id', $staffIds));
        if ($this->hasAnyRole($actor, 'WALI_KELAS')) {
            $query->orWhere(function ($waliQuery) use ($semester, $staffIds): void {
                $waliQuery->where('status', 'ACTIVE')->whereHas('homeroomAssignments', fn ($assignmentQuery) => $assignmentQuery->where('status', 'ACTIVE')->whereIn('staff_id', $staffIds)->whereDate('effective_from', '<=', $semester->ends_on->toDateString())->where(fn ($dateQuery) => $dateQuery->whereNull('effective_until')->orWhereDate('effective_until', '>', $semester->starts_on->toDateString())));
            });
        }

        return $query->orderBy('display_name')->get();
    }

    /** @return Collection<int, Subject> */
    public function visibleSubjects(User $actor, Semester $semester, AcademicClass $class): Collection
    {
        $query = Subject::query()->where('status', 'ACTIVE')->whereHas('teachingAssignments', fn ($assignmentQuery) => $assignmentQuery->where('semester_id', $semester->id)->where('class_id', $class->id)->where('workflow_status', 'ACTIVE'));
        if (! $this->isWakaAkademik($actor, $semester) && ! $this->isEffectiveWaliForClass($actor, $semester, $class)) {
            $staffIds = $this->linkedStaffIds($actor);
            $query->whereHas('teachingAssignments', function ($assignmentQuery) use ($semester, $class, $staffIds): void {
                $assignmentQuery->where('semester_id', $semester->id)
                    ->where('class_id', $class->id)
                    ->where('workflow_status', 'ACTIVE')
                    ->whereIn('teacher_staff_id', $staffIds)
                    ->whereDate('effective_from', '<=', $semester->ends_on->toDateString())
                    ->where(fn ($dateQuery) => $dateQuery->whereNull('effective_until')->orWhereDate('effective_until', '>', $semester->starts_on->toDateString()));
            });
        }

        return $query->orderBy('subject_name')->get();
    }

    private function activeAssignments(Semester $semester, AcademicClass $class, Subject $subject)
    {
        return TeachingAssignment::query()->where('semester_id', $semester->id)->where('class_id', $class->id)->where('subject_id', $subject->id)->where('workflow_status', 'ACTIVE')->whereDate('effective_from', '<=', $semester->ends_on->toDateString())->where(fn ($query) => $query->whereNull('effective_until')->orWhereDate('effective_until', '>', $semester->starts_on->toDateString()));
    }

    private function effectiveStaffId(User $actor, Semester $semester): ?string
    {
        $staffIds = $this->effectiveStaffIds($actor, $semester);

        return $staffIds->count() === 1 ? $staffIds->first() : null;
    }

    /** @return Collection<int, string> */
    private function effectiveStaffIds(User $actor, Semester $semester): Collection
    {
        return $actor->staffLink()
            ->where(fn ($query) => $query->whereNull('effective_from')->orWhereDate('effective_from', '<=', $semester->ends_on->toDateString()))
            ->where(fn ($query) => $query->whereNull('effective_until')->orWhereDate('effective_until', '>', $semester->starts_on->toDateString()))
            ->pluck('staff_id')
            ->filter()
            ->unique()
            ->values();
    }

    private function linkedStaffIds(User $actor): array
    {
        return $actor->staffLink()->pluck('staff_id')->filter()->values()->all();
    }

    private function hasRole(User $actor, string $roleCode, Semester $semester): bool
    {
        return $actor->roleAssignments()->effectiveAt(Carbon::parse($semester->starts_on))->whereHas('role', fn ($query) => $query->where('code', $roleCode))->exists();
    }

    private function hasAnyRole(User $actor, string $roleCode): bool
    {
        return $actor->roleAssignments()->effectiveAt()->whereHas('role', fn ($query) => $query->where('code', $roleCode))->exists();
    }
}
