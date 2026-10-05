<?php

namespace App\Domains\Academic\Services;

use App\Domains\Academic\Models\ClassSession;
use App\Domains\Academic\Models\SessionStudentParticipant;
use App\Shared\Platform\Presentation\AcademicBusinessTime;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;

class SessionParticipantEffectiveClassResolver
{
    public function __construct(private readonly AcademicClassScopeResolver $classScope) {}

    public function resolve(ClassSession $session, SessionStudentParticipant $participant): string
    {
        $participant->loadMissing('student.classEnrollments');
        $matches = $this->matches($session, $participant);

        if ($matches->count() !== 1) {
            throw new AuthorizationException('Peserta tidak memiliki tepat satu kelas efektif dalam scope sesi.');
        }

        return (string) $matches->first()->class_id;
    }

    public function matches(ClassSession $session, SessionStudentParticipant $participant): Collection
    {
        $participant->loadMissing('student.classEnrollments');
        $sessionDate = AcademicBusinessTime::date($session->planned_start_at);
        $canonicalClassIds = array_map('strval', $this->classScope->forSession($session));

        return $participant->student?->classEnrollments
            ->filter(fn ($enrollment): bool => in_array((string) $enrollment->class_id, $canonicalClassIds, true)
                && $enrollment->status === 'ACTIVE'
                && $enrollment->effective_from->lte($sessionDate)
                && ($enrollment->effective_until === null || $enrollment->effective_until->gt($sessionDate)))
            ?? collect();
    }
}
