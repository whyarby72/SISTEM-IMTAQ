<?php

namespace App\Domains\Academic\Services;

use App\Domains\Academic\Models\ClassSession;
use App\Models\User;
use DateTimeInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Carbon;

class AcademicTodaySessionService
{
    public function __construct(private readonly AcademicAuthorizationService $authorization) {}

    public function forUser(User $user, ?DateTimeInterface $now = null): array
    {
        $clock = Carbon::parse($now ?? now())->setTimezone(config('app.timezone', 'Asia/Jakarta'));
        if (! $this->authorization->hasAcademicFullAuthority($user, $clock)) {
            throw new AuthorizationException('Only Waka Akademik or Super Admin may read Today academic sessions.');
        }

        $startOfToday = $clock->copy()->startOfDay();
        $startOfNextDay = $startOfToday->copy()->addDay();
        $sessions = ClassSession::query()
            ->with(['academicClass', 'teachingAssignment.subject', 'scopeGroups.academicClass'])
            ->where('planned_start_at', '>=', $startOfToday)
            ->where('planned_start_at', '<', $startOfNextDay)
            ->orderBy('planned_start_at')
            ->get();

        $items = $sessions->map(fn (ClassSession $session): ?array => $this->item($session, $clock))->filter()->values();
        $activeItems = $items->filter(fn (array $item): bool => $item['is_active'])->values();

        return [
            'date' => $startOfToday->toDateString(),
            'timezone' => $clock->getTimezone()->getName(),
            'active_total' => $activeItems->count(),
            'upcoming_count' => $activeItems->where('operational_state', 'UPCOMING')->count(),
            'in_progress_count' => $activeItems->where('operational_state', 'IN_PROGRESS')->count(),
            'overdue_unfinished_count' => $activeItems->where('operational_state', 'OVERDUE_UNFINISHED')->count(),
            'completed_count' => $activeItems->where('operational_state', 'COMPLETED')->count(),
            'cancelled_count' => $items->where('operational_state', 'CANCELLED')->count(),
            'rescheduled_source_count' => $items->where('operational_state', 'RESCHEDULED')->count(),
            'items' => $activeItems->map(fn (array $item): array => $this->withoutInternalState($item))->all(),
        ];
    }

    private function item(ClassSession $session, Carbon $clock): ?array
    {
        $state = $this->operationalState($session, $clock);
        if ($state === null) {
            return null;
        }

        $classes = collect([$session->academicClass])
            ->merge($session->scopeGroups->map(fn ($group) => $group->academicClass))
            ->filter()
            ->unique('id')
            ->values();

        return [
            'class_session_id' => (string) $session->id,
            'planned_start_at' => $session->planned_start_at,
            'planned_end_at' => $session->planned_end_at,
            'raw_session_status' => $session->session_status,
            'session_source' => $session->session_source,
            'operational_state' => $state,
            'subject' => [
                'id' => $session->teachingAssignment?->subject?->id,
                'name' => $session->teachingAssignment?->subject?->subject_name,
            ],
            'associated_classes' => $classes->map(fn ($class): array => [
                'id' => (string) $class->id,
                'name' => $class->display_name,
            ])->all(),
            'is_active' => ! in_array($state, ['CANCELLED', 'RESCHEDULED'], true),
        ];
    }

    private function operationalState(ClassSession $session, Carbon $clock): ?string
    {
        return match ($session->session_status) {
            'CANCELLED' => 'CANCELLED',
            'RESCHEDULED' => 'RESCHEDULED',
            'COMPLETED' => 'COMPLETED',
            'PLANNED', 'CONFIRMED' => $session->planned_end_at->lessThanOrEqualTo($clock)
                ? 'OVERDUE_UNFINISHED'
                : ($session->planned_start_at->lessThanOrEqualTo($clock) ? 'IN_PROGRESS' : 'UPCOMING'),
            default => null,
        };
    }

    private function withoutInternalState(array $item): array
    {
        unset($item['is_active']);

        return $item;
    }
}
