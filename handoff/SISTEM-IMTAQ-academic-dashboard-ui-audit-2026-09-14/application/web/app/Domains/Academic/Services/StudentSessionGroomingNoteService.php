<?php

namespace App\Domains\Academic\Services;

use App\Domains\Academic\Models\ClassHomeroomAssignment;
use App\Domains\Academic\Models\ClassSession;
use App\Domains\Academic\Models\SessionStudentParticipant;
use App\Domains\Academic\Models\StudentSessionGroomingNote;
use App\Shared\Core\Models\Staff;
use App\Shared\Platform\Audit\Services\AuditLogger;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

class StudentSessionGroomingNoteService
{
    public const DISCIPLINE_CODES = ['RAPI', 'TIDAK_BERSERAGAM', 'SERAGAM_TIDAK_LENGKAP', 'TIDAK_MEMBAWA_BUKU', 'TIDAK_BERPECI', 'CATATAN_TAMBAHAN'];

    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function save(
        ClassSession $session,
        SessionStudentParticipant $participant,
        Staff $inputter,
        int $actorUserId,
        ?string $disciplineCode,
        ?string $additionalNote,
        bool $canManageAllClasses = false,
    ): StudentSessionGroomingNote {
        if ((string) $participant->class_session_id !== (string) $session->id) {
            throw new \InvalidArgumentException('Student participant does not belong to the session.');
        }

        $date = $session->planned_start_at->toDateString();
        $isHomeroom = ClassHomeroomAssignment::query()
            ->where('class_id', $session->class_id)
            ->where('staff_id', $inputter->id)
            ->where('status', 'ACTIVE')
            ->whereDate('effective_from', '<=', $date)
            ->where(fn ($query) => $query->whereNull('effective_until')->orWhereDate('effective_until', '>', $date))
            ->exists();

        if (! $isHomeroom && ! $canManageAllClasses) {
            throw new AuthorizationException('Only the effective Wali Kelas may save grooming notes.');
        }

        if ($disciplineCode !== null && ! in_array($disciplineCode, self::DISCIPLINE_CODES, true)) {
            throw new \InvalidArgumentException('Invalid discipline note option.');
        }

        $additionalNote = blank($additionalNote) ? null : trim($additionalNote);

        return DB::transaction(function () use ($participant, $actorUserId, $disciplineCode, $additionalNote): StudentSessionGroomingNote {
            $note = StudentSessionGroomingNote::query()
                ->where('session_student_participant_id', $participant->id)
                ->lockForUpdate()
                ->first();
            $oldValues = ['discipline_code' => $note?->discipline_code, 'note_text' => $note?->note_text];

            if ($note === null) {
                $note = StudentSessionGroomingNote::create([
                    'session_student_participant_id' => $participant->id,
                    'discipline_code' => $disciplineCode,
                    'note_text' => $additionalNote,
                    'created_by' => $actorUserId,
                    'created_at' => now(),
                    'updated_by' => $actorUserId,
                    'updated_at' => now(),
                ]);
            } else {
                $note->update(['discipline_code' => $disciplineCode, 'note_text' => $additionalNote, 'updated_by' => $actorUserId, 'updated_at' => now()]);
            }

            $this->auditLogger->record([
                'actor_user_id' => $actorUserId,
                'action' => 'STUDENT_SESSION_GROOMING_NOTE_SAVED',
                'entity_type' => StudentSessionGroomingNote::class,
                'entity_id' => (string) $note->id,
                'version_before' => $note->wasRecentlyCreated ? 0 : 1,
                'version_after' => 1,
                'old_values' => $oldValues,
                'new_values' => ['discipline_code' => $disciplineCode, 'note_text' => $additionalNote],
                'technical_metadata' => ['class_session_id' => (string) $participant->class_session_id, 'participant_id' => (string) $participant->id],
            ]);

            return $note->fresh();
        });
    }
}
