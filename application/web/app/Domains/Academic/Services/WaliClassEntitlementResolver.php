<?php

namespace App\Domains\Academic\Services;

use App\Domains\Academic\Models\ClassHomeroomAssignment;
use App\Models\User;
use App\Shared\Platform\Presentation\AcademicBusinessTime;
use Illuminate\Support\Carbon;

/**
 * Resolves the date windows in which a current Wali may read a class.
 *
 * Windows use the academic business date and an end-exclusive upper bound.
 * This is intentionally narrower than a general authorization framework: it
 * only composes the existing user/staff, role, and homeroom contracts for the
 * Academic dashboard read model.
 */
class WaliClassEntitlementResolver
{
    /**
     * @return array{class_windows:array<string,array<int,array{authorized_from:string,authorized_until:string}>>,class_ids:array<int,string>}
     */
    public function forPeriod(User $user, Carbon $from, Carbon $to): array
    {
        $periodFrom = $this->businessDate($from);
        $periodUntil = $this->businessDate($to)->addDay();
        $link = $user->staffLink()->first();

        if ($link === null) {
            return ['class_windows' => [], 'class_ids' => []];
        }

        $roles = $user->roleAssignments()
            ->whereHas('role', fn ($query) => $query->where('code', 'WALI_KELAS'))
            ->get(['effective_from', 'effective_until']);
        if ($roles->isEmpty()) {
            return ['class_windows' => [], 'class_ids' => []];
        }

        $assignments = ClassHomeroomAssignment::query()
            ->with('academicClass.academicYear')
            ->where('staff_id', $link->staff_id)
            ->where('status', 'ACTIVE')
            ->whereHas('academicClass.academicYear', fn ($query) => $query->where('year_code', 'not like', '%-PILOT'))
            ->get(['id', 'class_id', 'effective_from', 'effective_until']);

        $classWindows = [];
        foreach ($assignments as $assignment) {
            foreach ($roles as $role) {
                $windowFrom = $this->maxDate(
                    $periodFrom,
                    $this->nullableDate($link->effective_from),
                    $this->nullableDate($role->effective_from),
                    $this->nullableDate($assignment->effective_from),
                );
                $windowUntil = $this->minDate(
                    $periodUntil,
                    $this->nullableDate($link->effective_until),
                    $this->nullableDate($role->effective_until),
                    $this->nullableDate($assignment->effective_until),
                );

                if ($windowFrom->gte($windowUntil)) {
                    continue;
                }

                $classId = (string) $assignment->class_id;
                $classWindows[$classId][] = [
                    'authorized_from' => $windowFrom->toDateString(),
                    'authorized_until' => $windowUntil->toDateString(),
                ];
            }
        }

        foreach ($classWindows as &$windows) {
            usort($windows, static fn (array $left, array $right): int => $left['authorized_from'] <=> $right['authorized_from']);
        }
        unset($windows);

        return [
            'class_windows' => $classWindows,
            'class_ids' => array_keys($classWindows),
        ];
    }

    /** @param array<string,array<int,array{authorized_from:string,authorized_until:string}>> $entitlements */
    public function windowsForClass(array $entitlements, string $classId): array
    {
        return $entitlements['class_windows'][$classId] ?? [];
    }

    /** @param array<int,array{authorized_from:string,authorized_until:string}> $windows */
    public function containsDate(array $windows, string|Carbon $date): bool
    {
        $businessDate = $date instanceof Carbon ? $date->setTimezone(AcademicBusinessTime::timezone())->toDateString() : $date;

        foreach ($windows as $window) {
            if ($window['authorized_from'] <= $businessDate && $businessDate < $window['authorized_until']) {
                return true;
            }
        }

        return false;
    }

    /**
     * Constrain a ClassSession query to class scope and business-date windows.
     * The stored planned timestamp is UTC; window boundaries are converted
     * from Asia/Jakarta before binding to SQL.
     *
     * @param  array<string,array<int,array{authorized_from:string,authorized_until:string}>>  $classWindows
     */
    public function constrainSessionQuery($query, array $classWindows, ?Carbon $from = null, ?Carbon $to = null): void
    {
        if ($classWindows === []) {
            $query->whereRaw('1 = 0');

            return;
        }

        $periodFrom = $from === null ? null : $this->businessDate($from);
        $periodUntil = $to === null ? null : $this->businessDate($to)->addDay();
        $query->where(function ($scopeQuery) use ($classWindows, $periodFrom, $periodUntil): void {
            foreach ($classWindows as $classId => $windows) {
                foreach ($windows as $window) {
                    $startDate = Carbon::parse($window['authorized_from'], AcademicBusinessTime::timezone())->startOfDay();
                    $untilDate = Carbon::parse($window['authorized_until'], AcademicBusinessTime::timezone())->startOfDay();
                    if ($periodFrom !== null && $startDate->lessThan($periodFrom)) {
                        $startDate = $periodFrom->copy();
                    }
                    if ($periodUntil !== null && $untilDate->greaterThan($periodUntil)) {
                        $untilDate = $periodUntil->copy();
                    }
                    if ($startDate->gte($untilDate)) {
                        continue;
                    }
                    $start = $startDate->utc();
                    $until = $untilDate->utc();
                    $scopeQuery->orWhere(function ($windowQuery) use ($classId, $start, $until): void {
                        $windowQuery
                            ->where(function ($classQuery) use ($classId): void {
                                $classQuery
                                    ->where('class_id', $classId)
                                    ->orWhereHas('scopeGroups', fn ($groupQuery) => $groupQuery->where('class_id', $classId));
                            })
                            ->where('planned_start_at', '>=', $start)
                            ->where('planned_start_at', '<', $until);
                    });
                }
            }
        });
    }

    private function businessDate(Carbon $date): Carbon
    {
        return Carbon::parse($date->copy()->setTimezone(AcademicBusinessTime::timezone())->toDateString(), AcademicBusinessTime::timezone())->startOfDay();
    }

    private function nullableDate(mixed $date): ?Carbon
    {
        return $date === null ? null : $this->businessDate(Carbon::parse((string) $date));
    }

    private function maxDate(mixed ...$dates): Carbon
    {
        return collect($dates)->filter()->sortBy(fn (Carbon $date) => $date->getTimestamp())->last()->copy();
    }

    private function minDate(mixed ...$dates): Carbon
    {
        return collect($dates)->filter()->sortByDesc(fn (Carbon $date) => $date->getTimestamp())->last()->copy();
    }
}
