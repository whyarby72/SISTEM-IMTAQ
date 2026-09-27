<?php

namespace App\Domains\Academic\Services;

use App\Domains\Academic\Models\ScheduleRule;
use App\Domains\Academic\Models\Semester;
use App\Models\User;
use App\Shared\Platform\Audit\Services\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TeacherSchedulePublicationService
{
    public function validate(Semester $semester, ?User $actor = null): int
    {
        $this->assertValid($semester);

        return DB::transaction(fn (): int => $this->transition($semester, 'VALIDATED', $actor));
    }

    public function publish(Semester $semester, ?User $actor = null): int
    {
        $this->assertValid($semester);
        $draftCount = $this->rules($semester)->where('workflow_status', 'DRAFT')->count();
        if ($draftCount > 0) {
            throw ValidationException::withMessages(['schedule' => 'Semua jadwal harus divalidasi sebelum diterbitkan.']);
        }

        return DB::transaction(fn (): int => $this->transition($semester, 'PUBLISHED', $actor));
    }

    private function assertValid(Semester $semester): void
    {
        $errors = app(TeacherSchedulePublicationValidator::class)->validate($semester);
        if ($errors->isNotEmpty()) {
            throw ValidationException::withMessages(['schedule' => $errors->map(fn (array $error): string => $error['schedule_rule_key'].': '.implode(', ', $error['reasons']))->all()]);
        }
    }

    private function transition(Semester $semester, string $status, ?User $actor): int
    {
        $count = 0;
        foreach ($this->rules($semester) as $rule) {
            $before = $rule->getAttributes();
            $rule->update(['workflow_status' => $status, 'version_no' => $rule->version_no + 1]);
            $this->audit($rule, $before, $status, $actor);
            $assignment = $rule->teachingAssignment;
            $assignmentBefore = $assignment->getAttributes();
            $assignment->update(['workflow_status' => $status, 'version_no' => $assignment->version_no + 1]);
            $this->audit($assignment, $assignmentBefore, $status, $actor);
            $count++;
        }

        return $count;
    }

    private function rules(Semester $semester)
    {
        return ScheduleRule::query()->with('teachingAssignment')->whereHas('teachingAssignment', fn ($query) => $query->where('semester_id', $semester->id))->get();
    }

    private function audit(object $model, array $before, string $status, ?User $actor): void
    {
        app(AuditLogger::class)->record([
            'actor_user_id' => $actor?->id,
            'actor_type' => $actor ? 'USER' : 'SYSTEM',
            'source_channel' => 'WEB',
            'action' => 'SCHEDULE_'.$status,
            'entity_type' => $model::class,
            'entity_id' => $model->getKey(),
            'version_before' => $before['version_no'] ?? null,
            'version_after' => $model->version_no,
            'old_values' => $before,
            'new_values' => $model->getAttributes(),
        ]);
    }
}
