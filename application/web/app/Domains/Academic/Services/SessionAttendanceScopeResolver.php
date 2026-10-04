<?php

namespace App\Domains\Academic\Services;

use App\Domains\Academic\Models\ClassSession;
use App\Domains\Academic\Models\SessionStudentParticipant;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;

class SessionAttendanceScopeResolver
{
    public function __construct(
        private readonly AcademicClassScopeResolver $classScope,
        private readonly AcademicAuthorizationService $authorization,
        private readonly WaliKelasContextResolver $waliContext,
    ) {}

    /**
     * Resolve the server-owned read/write scope for one physical session.
     *
     * Full Academic authority intentionally retains the complete physical
     * session. A Wali receives only the intersection of effective homeroom
     * classes and the canonical session scope.
     *
     * @return array{mode:string, effective_class_ids:array<int,string>, canonical_session_class_ids:array<int,string>, authorized_participant_ids:array<int,string>, unmapped_participant_ids:array<int,string>, ambiguous_participant_ids:array<int,string>, is_joint:bool}
     */
    public function resolve(User $user, ClassSession $session, Collection $participants): array
    {
        $canonicalClassIds = array_map('strval', $this->classScope->forSession($session));
        $isJoint = count($canonicalClassIds) > 1;
        $fullAuthority = $this->authorization->canManageAllAcademicClasses($user, $session->planned_start_at);
        $effectiveClassIds = $fullAuthority
            ? $canonicalClassIds
            : array_map('strval', $this->waliContext->effectiveClassIdsForSession($user, $session));

        if ($effectiveClassIds === []) {
            throw new AuthorizationException('Authenticated user is not authorized for this session class scope.');
        }

        if ($fullAuthority || ! $isJoint) {
            return [
                'mode' => $fullAuthority ? 'FULL_SESSION' : 'CLASS_SESSION',
                'effective_class_ids' => $effectiveClassIds,
                'canonical_session_class_ids' => $canonicalClassIds,
                'authorized_participant_ids' => $participants->modelKeys(),
                'unmapped_participant_ids' => [],
                'ambiguous_participant_ids' => [],
                'is_joint' => $isJoint,
            ];
        }

        $authorized = [];
        $unmapped = [];
        $ambiguous = [];
        foreach ($participants as $participant) {
            $matches = $this->effectiveClassMatches($session, $participant, $canonicalClassIds);
            if ($matches->count() === 0) {
                $unmapped[] = (string) $participant->getKey();
            } elseif ($matches->count() > 1) {
                $ambiguous[] = (string) $participant->getKey();
            } elseif (in_array((string) $matches->first()->class_id, $effectiveClassIds, true)) {
                $authorized[] = (string) $participant->getKey();
            }
        }

        if ($unmapped !== [] || $ambiguous !== []) {
            throw new AuthorizationException('Sesi gabungan tidak dapat dibuka karena scope roster belum terpetakan secara unik.');
        }

        return [
            'mode' => 'WALI_CLASS_PARTITION',
            'effective_class_ids' => $effectiveClassIds,
            'canonical_session_class_ids' => $canonicalClassIds,
            'authorized_participant_ids' => $authorized,
            'unmapped_participant_ids' => [],
            'ambiguous_participant_ids' => [],
            'is_joint' => true,
        ];
    }

    public function filterParticipants(Collection $participants, array $scope): Collection
    {
        $allowed = array_map('strval', $scope['authorized_participant_ids']);

        return $participants->filter(fn (SessionStudentParticipant $participant): bool => in_array((string) $participant->getKey(), $allowed, true))->values();
    }

    private function effectiveClassMatches(ClassSession $session, object $participant, array $classIds): Collection
    {
        $sessionDate = $session->planned_start_at->toDateString();

        return $participant->student->classEnrollments
            ->filter(fn ($enrollment): bool => in_array((string) $enrollment->class_id, $classIds, true)
                && $enrollment->status === 'ACTIVE'
                && $enrollment->effective_from->lte($sessionDate)
                && ($enrollment->effective_until === null || $enrollment->effective_until->gt($sessionDate)));
    }
}
