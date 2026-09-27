<?php

namespace App\Domains\Academic\Services;

use App\Domains\Academic\Models\AcademicTranscriptLine;
use App\Domains\Academic\Models\AcademicTranscriptSignatory;
use App\Domains\Academic\Models\AcademicTranscriptVersion;
use App\Models\User;
use App\Shared\Core\Models\Staff;
use App\Shared\Platform\Audit\Services\AuditLogger;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AcademicTranscriptPublicationService
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function review(AcademicTranscriptVersion $version, User $actor): AcademicTranscriptVersion
    {
        $this->requireRole($actor, 'WALI_KELAS', 'Only Wali Kelas may review transcripts.');
        $this->transition($version, 'DRAFT', 'REVIEWED', $actor, 'ACADEMIC_TRANSCRIPT_REVIEWED', 'reviewed_by', 'reviewed_at');

        return $version->fresh();
    }

    public function approveAndPublish(AcademicTranscriptVersion $version, User $actor, Staff $headOfSchool, Staff $wali): AcademicTranscriptVersion
    {
        $this->requireRole($actor, 'WAKA_AKADEMIK', 'Only Waka Akademik may approve and publish transcripts.');
        if ($version->status !== 'REVIEWED') {
            throw new InvalidArgumentException('Only REVIEWED transcript versions may be approved and published.');
        }

        return DB::transaction(function () use ($version, $actor, $headOfSchool, $wali): AcademicTranscriptVersion {
            $locked = AcademicTranscriptVersion::query()->whereKey($version->id)->lockForUpdate()->firstOrFail();
            if ($locked->status !== 'REVIEWED') {
                throw new InvalidArgumentException('Transcript version changed before approval.');
            }
            $now = now();
            AcademicTranscriptVersion::query()->whereKey($locked->id)->update(['status' => 'APPROVED', 'approved_by' => $actor->id, 'approved_at' => $now, 'updated_at' => $now]);
            AcademicTranscriptSignatory::create(['academic_transcript_version_id' => $locked->id, 'signatory_role' => 'KEPALA_SEKOLAH', 'staff_id' => $headOfSchool->id, 'name_snapshot' => $headOfSchool->full_name, 'title_snapshot' => 'Kepala Sekolah']);
            AcademicTranscriptSignatory::create(['academic_transcript_version_id' => $locked->id, 'signatory_role' => 'WALI_KELAS', 'staff_id' => $wali->id, 'name_snapshot' => $wali->full_name, 'title_snapshot' => 'Wali Kelas']);
            AcademicTranscriptVersion::query()->whereKey($locked->id)->update(['status' => 'PUBLISHED', 'published_by' => $actor->id, 'published_at' => $now, 'updated_at' => $now]);
            $this->auditLogger->record(['actor_user_id' => $actor->id, 'action' => 'ACADEMIC_TRANSCRIPT_APPROVED_AND_PUBLISHED', 'entity_type' => AcademicTranscriptVersion::class, 'entity_id' => (string) $locked->id, 'old_values' => ['status' => 'REVIEWED'], 'new_values' => ['status' => 'PUBLISHED', 'signatories' => ['KEPALA_SEKOLAH', 'WALI_KELAS']]]);

            return $locked->fresh(['signatories']);
        });
    }

    public function createSuperAdminRevision(AcademicTranscriptVersion $source, User $actor, array $lineOverrides = [], ?string $studentName = null): AcademicTranscriptVersion
    {
        $this->requireRole($actor, 'SUPER_ADMIN', 'Only Super Admin may create unrestricted transcript revisions.');

        return DB::transaction(function () use ($source, $actor, $lineOverrides, $studentName): AcademicTranscriptVersion {
            $transcript = $source->transcript()->lockForUpdate()->firstOrFail();
            $nextVersion = ((int) AcademicTranscriptVersion::query()->where('academic_transcript_id', $transcript->id)->max('version_no')) + 1;
            $revision = AcademicTranscriptVersion::create(['academic_transcript_id' => $transcript->id, 'version_no' => $nextVersion, 'status' => 'DRAFT', 'source_cutoff_at' => now(), 'student_name_snapshot' => $studentName ?? $source->student_name_snapshot, 'created_by' => $actor->id]);
            foreach ($source->lines as $line) {
                $override = $lineOverrides[(string) $line->id] ?? [];
                AcademicTranscriptLine::create(['academic_transcript_version_id' => $revision->id, 'semester_id' => $line->semester_id, 'subject_id' => $line->subject_id, 'semester_name_snapshot' => $override['semester_name_snapshot'] ?? $line->semester_name_snapshot, 'subject_name_snapshot' => $override['subject_name_snapshot'] ?? $line->subject_name_snapshot, 'semester_subject_grade_id' => $line->semester_subject_grade_id, 'grade_version_no' => $line->grade_version_no, 'score' => $override['score'] ?? $line->score]);
            }
            $this->auditLogger->record(['actor_user_id' => $actor->id, 'action' => 'ACADEMIC_TRANSCRIPT_SUPER_ADMIN_REVISION_CREATED', 'entity_type' => AcademicTranscriptVersion::class, 'entity_id' => (string) $revision->id, 'old_values' => ['source_version_id' => (string) $source->id], 'new_values' => ['version_no' => $nextVersion, 'status' => 'DRAFT']]);

            return $revision->fresh(['lines']);
        });
    }

    private function transition(AcademicTranscriptVersion $version, string $from, string $to, User $actor, string $action, string $actorField, string $timeField): void
    {
        if ($version->status !== $from) {
            throw new InvalidArgumentException("Only {$from} transcript versions may transition to {$to}.");
        }
        DB::transaction(function () use ($version, $from, $to, $actor, $action, $actorField, $timeField): void {
            $locked = AcademicTranscriptVersion::query()->whereKey($version->id)->lockForUpdate()->firstOrFail();
            if ($locked->status !== $from) {
                throw new InvalidArgumentException('Transcript version changed before transition.');
            }
            $now = now();
            AcademicTranscriptVersion::query()->whereKey($locked->id)->update(['status' => $to, $actorField => $actor->id, $timeField => $now, 'updated_at' => $now]);
            $this->auditLogger->record(['actor_user_id' => $actor->id, 'action' => $action, 'entity_type' => AcademicTranscriptVersion::class, 'entity_id' => (string) $locked->id, 'old_values' => ['status' => $from], 'new_values' => ['status' => $to]]);
        });
    }

    private function requireRole(User $actor, string $role, string $message): void
    {
        if (! $actor->roleAssignments()->effectiveAt()->whereHas('role', fn ($query) => $query->where('code', $role))->exists()) {
            throw new AuthorizationException($message);
        }
    }
}
