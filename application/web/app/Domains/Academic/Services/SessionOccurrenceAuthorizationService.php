<?php

namespace App\Domains\Academic\Services;

use App\Domains\Academic\Models\ClassSession;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class SessionOccurrenceAuthorizationService
{
    public function __construct(
        private readonly AcademicAuthorizationService $authorization,
        private readonly WaliKelasContextResolver $waliResolver,
    ) {}

    public function canManageRoutine(User $user, ClassSession $session, string $action): bool
    {
        if ($this->authorization->hasAcademicFullAuthority($user, $session->planned_start_at)) {
            return true;
        }

        if (! $this->authorization->hasEffectiveRole($user, 'WALI_KELAS', $session->planned_start_at)) {
            return false;
        }

        if (in_array($action, ['CANCELLED', 'RESCHEDULED'], true) && $this->isJoint($session)) {
            return false;
        }

        try {
            $this->waliResolver->resolve($user, $session);

            return true;
        } catch (AuthorizationException) {
            return false;
        }
    }

    public function requireRoutine(User $user, ClassSession $session, string $action): void
    {
        if (! $this->canManageRoutine($user, $session, $action)) {
            throw new AuthorizationException('Anda tidak memiliki kewenangan untuk mengelola kejadian sesi ini.');
        }
    }

    public function requireCorrection(User $user, ClassSession $session): void
    {
        if (! $this->authorization->hasAcademicFullAuthority($user, $session->planned_start_at)) {
            throw new AuthorizationException('Koreksi kejadian sesi hanya dapat dilakukan Waka Akademik atau Super Admin.');
        }
    }

    public function hasAcademicFullAuthority(User $user, $at = null): bool
    {
        return $this->authorization->hasAcademicFullAuthority($user, $at);
    }

    public function canManageSubstitution(User $user, ClassSession $session): bool
    {
        if ($this->authorization->hasAcademicFullAuthority($user, $session->planned_start_at)) {
            return true;
        }

        return ! $this->isJoint($session) && $this->canManageRoutine($user, $session, 'SUBSTITUTION');
    }

    public function isJoint(ClassSession $session): bool
    {
        return $session->scopeGroups()->count() > 1;
    }
}
