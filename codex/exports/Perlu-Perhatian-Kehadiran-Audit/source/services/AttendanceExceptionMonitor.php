<?php

namespace App\Domains\Academic\Services;

use App\Domains\Academic\Models\ClassSession;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;

class AttendanceExceptionMonitor
{
    public function __construct(
        private readonly StudentAttendanceCompletenessChecker $checker,
        private readonly AcademicAuthorizationService $authorization,
    ) {}

    public function incompleteSessions(User $actor): Collection
    {
        $this->authorize($actor);

        return ClassSession::query()
            ->with('academicClass')
            ->whereNotIn('session_status', ['CANCELLED', 'RESCHEDULED'])
            ->where('planned_start_at', '<=', now())
            ->whereHas('academicClass.academicYear', fn ($query) => $query->where('year_code', 'not like', '%-PILOT'))
            ->orderByDesc('planned_start_at')
            ->get()
            ->map(fn (ClassSession $session): array => [
                'session' => $session,
                'finding' => $this->checker->check($session),
            ])
            ->filter(fn (array $item): bool => ! $item['finding']['is_complete'])
            ->values();
    }

    private function authorize(User $actor): void
    {
        if (! $this->authorization->hasAcademicFullAuthority($actor)) {
            throw new AuthorizationException('Only WAKA_AKADEMIK or SUPER_ADMIN may monitor attendance exceptions.');
        }
    }
}
