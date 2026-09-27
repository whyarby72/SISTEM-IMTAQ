<?php

namespace App\Domains\Academic\Services;

use App\Domains\Academic\Models\ClassHomeroomAssignment;
use App\Domains\Academic\Models\ReportCardNote;
use App\Domains\Academic\Models\ReportCardVersion;
use App\Domains\Academic\Models\StudentClassEnrollment;
use App\Models\User;
use App\Shared\Platform\Audit\Services\AuditLogger;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ReportCardNoteService
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function save(ReportCardVersion $version, User $actor, string $note): ReportCardNote
    {
        if (blank($note) || mb_strlen($note) > 2000) {
            throw new InvalidArgumentException('Report note must contain 1 to 2000 characters.');
        }
        if ($version->status !== 'DRAFT') {
            throw new InvalidArgumentException('Notes may only be edited on a DRAFT report version.');
        }

        $version->loadMissing('reportCard');
        $semester = $version->reportCard->semester;
        $staffLink = $actor->staffLink()->where(fn ($query) => $query->whereNull('effective_from')->orWhereDate('effective_from', '<=', $semester->starts_on->toDateString()))->where(fn ($query) => $query->whereNull('effective_until')->orWhereDate('effective_until', '>', $semester->starts_on->toDateString()))->first();
        $allowed = $actor->roleAssignments()->effectiveAt()->whereHas('role', fn ($query) => $query->where('code', 'WALI_KELAS'))->exists()
            && $staffLink !== null
            && ClassHomeroomAssignment::query()->where('class_id', $version->class_id)->where('staff_id', $staffLink->staff_id)->where('status', 'ACTIVE')->whereDate('effective_from', '<=', $semester->starts_on->toDateString())->where(fn ($query) => $query->whereNull('effective_until')->orWhereDate('effective_until', '>', $semester->starts_on->toDateString()))->exists()
            && StudentClassEnrollment::query()->where('student_id', $version->reportCard->student_id)->where('class_id', $version->class_id)->where('status', 'ACTIVE')->whereDate('effective_from', '<=', $semester->ends_on->toDateString())->where(fn ($query) => $query->whereNull('effective_until')->orWhereDate('effective_until', '>', $semester->starts_on->toDateString()))->exists();
        if (! $allowed) {
            throw new AuthorizationException('Only the effective Wali Kelas may edit this report note.');
        }

        return DB::transaction(function () use ($version, $actor, $note): ReportCardNote {
            $existing = ReportCardNote::query()->where('report_card_version_id', $version->id)->lockForUpdate()->first();
            $old = $existing?->note_text;
            $attributes = ['note_text' => $note, 'updated_by' => $actor->id];
            if ($existing === null) {
                $existing = ReportCardNote::create(['report_card_version_id' => $version->id, ...$attributes, 'created_by' => $actor->id, 'approved_for_parent_report' => false]);
            } else {
                $existing->update($attributes);
            }
            $this->auditLogger->record(['actor_user_id' => $actor->id, 'action' => 'REPORT_CARD_NOTE_SAVED', 'entity_type' => ReportCardNote::class, 'entity_id' => (string) $existing->id, 'old_values' => ['note_text' => $old, 'approved_for_parent_report' => false], 'new_values' => ['note_text' => $note, 'approved_for_parent_report' => false]]);

            return $existing->fresh();
        });
    }
}
