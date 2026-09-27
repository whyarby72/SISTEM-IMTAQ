<?php

namespace App\Domains\Academic\Services;

use App\Domains\Academic\Models\AcademicTranscript;
use App\Domains\Academic\Models\AcademicTranscriptLine;
use App\Domains\Academic\Models\AcademicTranscriptVersion;
use App\Models\User;
use App\Shared\Core\Models\Student;
use App\Shared\Platform\Audit\Services\AuditLogger;
use Illuminate\Support\Facades\DB;

class AcademicTranscriptDraftService
{
    public function __construct(
        private readonly AcademicHistoryService $history,
        private readonly AuditLogger $auditLogger,
    ) {}

    public function create(Student $student, User $actor): AcademicTranscriptVersion
    {
        $history = $this->history->forStudent($student);

        return DB::transaction(function () use ($student, $actor, $history): AcademicTranscriptVersion {
            $transcript = AcademicTranscript::query()->where('student_id', $student->id)->where('transcript_type', 'ACADEMIC')->lockForUpdate()->first();
            if ($transcript === null) {
                $transcript = AcademicTranscript::create(['student_id' => $student->id, 'transcript_type' => 'ACADEMIC']);
            }
            $versionNo = ((int) $transcript->versions()->max('version_no')) + 1;
            $version = AcademicTranscriptVersion::create(['academic_transcript_id' => $transcript->id, 'version_no' => $versionNo, 'status' => 'DRAFT', 'source_cutoff_at' => now(), 'student_name_snapshot' => $student->full_name, 'created_by' => $actor->id]);

            foreach ($history as $line) {
                AcademicTranscriptLine::create(['academic_transcript_version_id' => $version->id, 'semester_id' => $line['semester_id'], 'subject_id' => $line['subject_id'], 'semester_name_snapshot' => $line['semester_name'], 'subject_name_snapshot' => $line['subject_name'], 'semester_subject_grade_id' => $line['grade_id'], 'grade_version_no' => $line['grade_version_no'], 'score' => $line['score']]);
            }

            $this->auditLogger->record(['actor_user_id' => $actor->id, 'action' => 'ACADEMIC_TRANSCRIPT_DRAFT_CREATED', 'entity_type' => AcademicTranscriptVersion::class, 'entity_id' => (string) $version->id, 'version_after' => $version->version_no, 'new_values' => ['transcript_id' => (string) $transcript->id, 'status' => 'DRAFT', 'line_count' => $history->count()]]);

            $version->load('lines.semester');
            $version->setRelation('lines', $version->lines->sort(function ($left, $right): int {
                return $right->semester->starts_on->getTimestamp() <=> $left->semester->starts_on->getTimestamp();
            })->values());

            return $version;
        });
    }
}
