<?php

namespace App\Domains\Academic\Services;

use App\Domains\Academic\Models\ClassHomeroomAssignment;
use App\Domains\Academic\Models\ReportCardNote;
use App\Domains\Academic\Models\ReportCardSignatory;
use App\Domains\Academic\Models\ReportCardVersion;
use App\Domains\Academic\Models\StudentClassEnrollment;
use App\Models\User;
use App\Shared\Core\Models\Staff;
use App\Shared\Platform\Audit\Services\AuditLogger;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ReportCardPublicationService
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function review(ReportCardVersion $version, User $actor): ReportCardVersion
    {
        if ($version->status !== 'DRAFT') {
            throw new InvalidArgumentException('Only DRAFT report versions may be reviewed.');
        }
        $this->authorizeWali($version, $actor);

        return DB::transaction(function () use ($version, $actor): ReportCardVersion {
            $locked = ReportCardVersion::query()->whereKey($version->id)->lockForUpdate()->firstOrFail();
            if ($locked->status !== 'DRAFT') {
                throw new InvalidArgumentException('Report version changed before review.');
            }
            $now = now();
            ReportCardVersion::query()->whereKey($locked->id)->update(['status' => 'REVIEWED', 'reviewed_by' => $actor->id, 'reviewed_at' => $now, 'updated_at' => $now]);
            $this->auditLogger->record(['actor_user_id' => $actor->id, 'action' => 'REPORT_CARD_REVIEWED', 'entity_type' => ReportCardVersion::class, 'entity_id' => (string) $locked->id, 'old_values' => ['status' => 'DRAFT'], 'new_values' => ['status' => 'REVIEWED']]);

            return $locked->fresh();
        });
    }

    public function approveAndPublish(ReportCardVersion $version, User $actor, Staff $headOfSchool): ReportCardVersion
    {
        if (! $this->hasRole($actor, 'WAKA_AKADEMIK')) {
            throw new AuthorizationException('Only Waka Akademik may approve and publish reports.');
        }
        if ($version->status !== 'REVIEWED') {
            throw new InvalidArgumentException('Only REVIEWED report versions may be approved and published.');
        }

        return DB::transaction(function () use ($version, $actor, $headOfSchool): ReportCardVersion {
            $locked = ReportCardVersion::query()->whereKey($version->id)->lockForUpdate()->firstOrFail();
            if ($locked->status !== 'REVIEWED') {
                throw new InvalidArgumentException('Report version changed before approval.');
            }
            $now = now();
            ReportCardVersion::query()->whereKey($locked->id)->update(['status' => 'APPROVED', 'approved_by' => $actor->id, 'approved_at' => $now, 'updated_at' => $now]);
            $this->auditLogger->record(['actor_user_id' => $actor->id, 'action' => 'REPORT_CARD_APPROVED', 'entity_type' => ReportCardVersion::class, 'entity_id' => (string) $locked->id, 'old_values' => ['status' => 'REVIEWED'], 'new_values' => ['status' => 'APPROVED']]);

            $wali = ClassHomeroomAssignment::query()->where('class_id', $locked->class_id)->where('status', 'ACTIVE')->whereDate('effective_from', '<=', $locked->reportCard->semester->starts_on->toDateString())->where(fn ($query) => $query->whereNull('effective_until')->orWhereDate('effective_until', '>', $locked->reportCard->semester->starts_on->toDateString()))->with('staff')->first()?->staff;
            if ($wali === null) {
                throw new InvalidArgumentException('No effective Wali Kelas is available for the report signatory snapshot.');
            }
            ReportCardSignatory::create(['report_card_version_id' => $locked->id, 'signatory_role' => 'KEPALA_SEKOLAH', 'staff_id' => $headOfSchool->id, 'name_snapshot' => $headOfSchool->full_name, 'title_snapshot' => 'Kepala Sekolah']);
            ReportCardSignatory::create(['report_card_version_id' => $locked->id, 'signatory_role' => 'WALI_KELAS', 'staff_id' => $wali->id, 'name_snapshot' => $wali->full_name, 'title_snapshot' => 'Wali Kelas']);
            ReportCardNote::query()->where('report_card_version_id', $locked->id)->update(['approved_for_parent_report' => true, 'updated_at' => $now]);
            ReportCardVersion::query()->whereKey($locked->id)->update(['status' => 'PUBLISHED', 'published_by' => $actor->id, 'published_at' => $now, 'updated_at' => $now]);
            $this->auditLogger->record(['actor_user_id' => $actor->id, 'action' => 'REPORT_CARD_PUBLISHED', 'entity_type' => ReportCardVersion::class, 'entity_id' => (string) $locked->id, 'old_values' => ['status' => 'APPROVED'], 'new_values' => ['status' => 'PUBLISHED', 'signatories' => ['KEPALA_SEKOLAH', 'WALI_KELAS']]]);

            return $locked->fresh(['signatories']);
        });
    }

    private function authorizeWali(ReportCardVersion $version, User $actor): void
    {
        if (! $this->hasRole($actor, 'WALI_KELAS')) {
            throw new AuthorizationException('Only Wali Kelas may review reports.');
        }
        $report = $version->reportCard;
        $semester = $report->semester;
        $date = $semester->starts_on->toDateString();
        $link = $actor->staffLink()->where(fn ($query) => $query->whereNull('effective_from')->orWhereDate('effective_from', '<=', $date))->where(fn ($query) => $query->whereNull('effective_until')->orWhereDate('effective_until', '>', $date))->first();
        $allowed = $link !== null && ClassHomeroomAssignment::query()->where('class_id', $version->class_id)->where('staff_id', $link->staff_id)->where('status', 'ACTIVE')->whereDate('effective_from', '<=', $date)->where(fn ($query) => $query->whereNull('effective_until')->orWhereDate('effective_until', '>', $date))->exists() && StudentClassEnrollment::query()->where('student_id', $report->student_id)->where('class_id', $version->class_id)->where('status', 'ACTIVE')->whereDate('effective_from', '<=', $semester->ends_on->toDateString())->where(fn ($query) => $query->whereNull('effective_until')->orWhereDate('effective_until', '>', $semester->starts_on->toDateString()))->exists();
        if (! $allowed) {
            throw new AuthorizationException('Actor is not the effective Wali Kelas for this report.');
        }
    }

    private function hasRole(User $actor, string $roleCode): bool
    {
        return $actor->roleAssignments()->effectiveAt()->whereHas('role', fn ($query) => $query->where('code', $roleCode))->exists();
    }
}
