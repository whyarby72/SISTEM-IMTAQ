<?php

namespace Tests\Feature\Academic;

use App\Domains\Academic\Models\ClassSession;
use App\Domains\Academic\Services\SessionOccurrenceCutover;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class SessionOccurrenceCutoverTest extends TestCase
{
    public function test_gate_off_keeps_every_session_in_legacy_regime(): void
    {
        config(['academic.session_occurrence_enabled' => false, 'academic.session_occurrence_cutover_at' => null]);

        $this->assertSame(SessionOccurrenceCutover::LEGACY, app(SessionOccurrenceCutover::class)->regime($this->makeSessionModel('2026-09-19 08:00:00')));
    }

    public function test_gate_on_without_cutover_fails_closed(): void
    {
        config(['academic.session_occurrence_enabled' => true, 'academic.session_occurrence_cutover_at' => null]);

        $cutover = app(SessionOccurrenceCutover::class);
        $this->assertSame(SessionOccurrenceCutover::UNCONFIGURED, $cutover->regime($this->makeSessionModel('2026-09-19 08:00:00')));

        $this->expectException(AuthorizationException::class);
        $cutover->requireCanonicalWrite($this->makeSessionModel('2026-09-19 08:00:00'));
    }

    public function test_boundary_is_classified_by_planned_start_datetime(): void
    {
        config(['academic.session_occurrence_enabled' => true, 'academic.session_occurrence_cutover_at' => '2026-09-19 10:00:00']);
        $cutover = app(SessionOccurrenceCutover::class);

        $this->assertSame(SessionOccurrenceCutover::LEGACY, $cutover->regime($this->makeSessionModel('2026-09-19 09:59:59')));
        $this->assertSame(SessionOccurrenceCutover::CANONICAL, $cutover->regime($this->makeSessionModel('2026-09-19 10:00:00')));
        $this->assertSame(SessionOccurrenceCutover::CANONICAL, $cutover->regime($this->makeSessionModel('2026-09-19 10:00:01')));
    }

    private function makeSessionModel(string $plannedStart): ClassSession
    {
        $start = Carbon::parse($plannedStart);

        return new ClassSession([
            'planned_start_at' => $start,
            'planned_end_at' => $start->copy()->addHour(),
            'session_status' => 'COMPLETED',
        ]);
    }
}
