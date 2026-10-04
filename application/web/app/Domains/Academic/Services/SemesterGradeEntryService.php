<?php

namespace App\Domains\Academic\Services;

use App\Domains\Academic\Models\Semester;
use App\Domains\Academic\Models\SemesterSubjectGrade;
use App\Domains\Academic\Models\Subject;
use App\Domains\Academic\Models\TeachingAssignment;
use App\Models\User;
use App\Shared\Core\Models\Student;
use App\Shared\Platform\Audit\Services\AuditLogger;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class SemesterGradeEntryService
{
    private const ALLOWED_FIELDS = ['score', 'grade_source', 'source_teaching_assignment_id', 'responsible_staff_id'];

    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function save(
        Student $student,
        Semester $semester,
        Subject $subject,
        User $actor,
        array $attributes,
        ?int $expectedVersion = null,
    ): SemesterSubjectGrade {
        $unknown = array_diff(array_keys($attributes), self::ALLOWED_FIELDS);
        if ($unknown !== []) {
            throw new InvalidArgumentException('Unsupported semester grade fields were provided.');
        }

        $score = $attributes['score'] ?? null;
        if ($score !== null && (! is_numeric($score) || (float) $score < 0 || (float) $score > 100)) {
            throw new InvalidArgumentException('Score must be between 0 and 100.');
        }

        $gradeSource = $attributes['grade_source'] ?? 'DIRECT_ENTRY';
        if (! in_array($gradeSource, ['DIRECT_ENTRY', 'IMPORTED'], true)) {
            throw new InvalidArgumentException('Grade source is not supported for MVP.');
        }

        if (isset($attributes['source_teaching_assignment_id'])) {
            $provenance = TeachingAssignment::query()
                ->whereKey($attributes['source_teaching_assignment_id'])
                ->where('semester_id', $semester->id)
                ->where('subject_id', $subject->id)
                ->exists();
            if (! $provenance) {
                throw new InvalidArgumentException('Teaching assignment provenance does not match the semester and subject.');
            }
        }

        return DB::transaction(function () use ($student, $semester, $subject, $actor, $attributes, $expectedVersion, $score, $gradeSource): SemesterSubjectGrade {
            $grade = SemesterSubjectGrade::query()
                ->where('student_id', $student->id)
                ->where('semester_id', $semester->id)
                ->where('subject_id', $subject->id)
                ->lockForUpdate()
                ->first();
            $before = $grade?->toArray() ?? [];

            if ($grade !== null) {
                if ($grade->workflow_status !== 'DRAFT') {
                    throw new InvalidArgumentException('Only DRAFT semester grades may be changed through normal save.');
                }
                if ($expectedVersion === null) {
                    throw new InvalidArgumentException('Expected version is required for an existing semester grade.');
                }
                if ($grade->version_no !== $expectedVersion) {
                    throw new InvalidArgumentException('Semester grade version is stale.');
                }
            } elseif ($expectedVersion !== null) {
                throw new InvalidArgumentException('Expected version must be empty for a new semester grade.');
            }

            $now = now();
            $payload = [
                'score' => $score === null ? null : number_format((float) $score, 2, '.', ''),
                'grade_source' => $gradeSource,
                'source_teaching_assignment_id' => $attributes['source_teaching_assignment_id'] ?? null,
                'responsible_staff_id' => $attributes['responsible_staff_id'] ?? null,
                'updated_by' => $actor->id,
                'updated_at' => $now,
            ];

            if ($grade === null) {
                $payload += ['student_id' => $student->id, 'semester_id' => $semester->id, 'subject_id' => $subject->id, 'entered_by' => $actor->id, 'entered_at' => $now, 'workflow_status' => 'DRAFT', 'version_no' => 1];
                $grade = SemesterSubjectGrade::create($payload);
            } else {
                $canonicalFields = ['score', 'grade_source', 'source_teaching_assignment_id', 'responsible_staff_id'];
                $changed = collect($canonicalFields)->contains(fn (string $field): bool => $grade->getAttribute($field) !== $payload[$field]);
                if (! $changed) {
                    return $grade->fresh();
                }

                $payload['version_no'] = $grade->version_no + 1;
                $grade->update($payload);
            }

            $this->auditLogger->record([
                'actor_user_id' => $actor->id,
                'action' => 'SEMESTER_SUBJECT_GRADE_SAVED',
                'entity_type' => SemesterSubjectGrade::class,
                'entity_id' => (string) $grade->id,
                'version_before' => $grade->wasRecentlyCreated ? null : ($grade->version_no - 1),
                'version_after' => $grade->version_no,
                'old_values' => $before,
                'new_values' => $grade->fresh()->toArray(),
            ]);

            return $grade->fresh();
        });
    }
}
