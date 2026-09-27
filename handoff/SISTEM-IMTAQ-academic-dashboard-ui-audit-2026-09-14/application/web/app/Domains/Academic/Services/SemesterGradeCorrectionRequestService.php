<?php

namespace App\Domains\Academic\Services;

use App\Domains\Academic\Models\SemesterSubjectGrade;
use App\Models\User;
use App\Shared\Platform\Audit\Models\CorrectionRequest;
use App\Shared\Platform\Audit\Services\AuditLogger;
use App\Shared\Platform\Audit\Services\CorrectionRequestService;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class SemesterGradeCorrectionRequestService
{
    private const ALLOWED_FIELDS = ['score', 'grade_source', 'source_teaching_assignment_id', 'responsible_staff_id'];

    public function __construct(
        private readonly CorrectionRequestService $correctionRequests,
        private readonly AuditLogger $auditLogger,
    ) {}

    public function request(
        SemesterSubjectGrade $grade,
        User $actor,
        int $expectedVersion,
        string $reason,
        array $changes,
    ): CorrectionRequest {
        if (blank($reason)) {
            throw new InvalidArgumentException('A grade correction reason is required.');
        }
        if ($grade->version_no !== $expectedVersion) {
            throw new InvalidArgumentException('Semester grade version is stale.');
        }

        $updates = collect($changes)->only(self::ALLOWED_FIELDS)->all();
        if ($updates === []) {
            throw new InvalidArgumentException('At least one grade field must be corrected.');
        }
        if (array_key_exists('score', $updates) && ($updates['score'] !== null && (! is_numeric($updates['score']) || (float) $updates['score'] < 0 || (float) $updates['score'] > 100))) {
            throw new InvalidArgumentException('Score must be between 0 and 100.');
        }
        if (isset($updates['grade_source']) && ! in_array($updates['grade_source'], ['DIRECT_ENTRY', 'IMPORTED'], true)) {
            throw new InvalidArgumentException('Grade source is not supported for MVP.');
        }

        return DB::transaction(function () use ($grade, $actor, $expectedVersion, $reason, $updates): CorrectionRequest {
            $request = $this->correctionRequests->submit([
                'requested_by_user_id' => $actor->id,
                'entity_type' => SemesterSubjectGrade::class,
                'entity_id' => (string) $grade->id,
                'correction_type' => 'SEMESTER_SUBJECT_GRADE',
                'requested_changes' => ['expected_version' => $expectedVersion, 'changes' => $updates],
                'reason' => $reason,
            ]);

            $this->auditLogger->record([
                'actor_user_id' => $actor->id,
                'action' => 'SEMESTER_SUBJECT_GRADE_CORRECTION_REQUESTED',
                'entity_type' => SemesterSubjectGrade::class,
                'entity_id' => (string) $grade->id,
                'version_before' => $grade->version_no,
                'version_after' => $grade->version_no,
                'old_values' => $grade->only(array_keys($updates)),
                'new_values' => $updates,
                'reason' => $reason,
                'correction_request_id' => $request->id,
            ]);

            return $request;
        });
    }
}
