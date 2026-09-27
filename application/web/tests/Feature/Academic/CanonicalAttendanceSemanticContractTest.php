<?php

namespace Tests\Feature\Academic;

use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Models\AttendanceSourceCertification;
use App\Domains\Academic\Models\ClassLineageMapping;
use App\Domains\Academic\Models\ClassSession;
use App\Domains\Academic\Models\GradeLevel;
use App\Domains\Academic\Models\MonthlyAttendanceSummary;
use App\Domains\Academic\Models\SessionStudentParticipant;
use App\Domains\Academic\Models\StudentAttendance;
use App\Domains\Academic\Semantics\CanonicalAttendanceStatusMapper;
use App\Domains\Academic\Semantics\Enums\AttendanceAuthorityStatus;
use App\Domains\Academic\Services\AttendanceSourceAuthorityResolver;
use App\Domains\Academic\Services\CanonicalAttendanceSemanticService;
use App\Domains\Academic\Services\ClassLineageResolver;
use App\Shared\Core\Models\AcademicYear;
use App\Shared\Core\Models\OrganizationalUnit;
use App\Shared\Platform\Imports\Models\ImportBatch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class CanonicalAttendanceSemanticContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_status_mapper_and_completeness_keep_missing_separate_from_absent(): void
    {
        $semantic = app(CanonicalAttendanceSemanticService::class);
        $participants = collect([
            $this->participant('PRESENT', true),
            $this->participant('IZIN', true),
            $this->participant(null, true),
            $this->participant('ABSENT', false),
        ]);

        $result = $semantic->summarize($participants);

        $this->assertSame('PERMISSION', app(CanonicalAttendanceStatusMapper::class)->map('IZIN')->value);
        $this->assertSame(3, $result['eligible_opportunities']);
        $this->assertSame(2, $result['resolved_opportunities']);
        $this->assertSame(1, $result['missing_opportunities']);
        $this->assertSame(66.67, $result['completeness_rate']);
        $this->assertNull($result['official_attendance_rate']);
        $this->assertSame(0, $result['counts']['ABSENT']);
    }

    public function test_future_planned_is_not_the_same_as_historical_planned_or_cancelled(): void
    {
        $semantic = app(CanonicalAttendanceSemanticService::class);
        $future = new ClassSession(['session_status' => 'PLANNED', 'planned_start_at' => now()->addDay(), 'planned_end_at' => now()->addDay()->addHour()]);
        $historical = new ClassSession(['session_status' => 'PLANNED', 'planned_start_at' => now()->subDay(), 'planned_end_at' => now()->subDay()->addHour()]);
        $cancelled = new ClassSession(['session_status' => 'CANCELLED', 'planned_start_at' => now()->subDay(), 'planned_end_at' => now()->subDay()->addHour()]);
        $rescheduled = new ClassSession(['session_status' => 'RESCHEDULED', 'planned_start_at' => now()->subDay(), 'planned_end_at' => now()->subDay()->addHour()]);

        $this->assertSame('PLANNED_FUTURE', $semantic->sessionState($future));
        $this->assertSame('PLANNED_HISTORICAL_WORK_QUEUE', $semantic->sessionState($historical));
        $this->assertSame('CANCELLED', $semantic->sessionState($cancelled));
        $this->assertSame('RESCHEDULED_SOURCE', $semantic->sessionState($rescheduled));
    }

    public function test_late_is_present_with_late_punctuality_and_is_counted_once(): void
    {
        $semantic = app(CanonicalAttendanceSemanticService::class);
        $result = $semantic->summarize(collect([
            $this->participant('PRESENT', true),
            $this->participant('LATE', true),
            $this->participant('IZIN', true),
            $this->participant('SICK', true),
            $this->participant('ABSENT', true),
        ]));

        $this->assertFalse($result['semantic_decision_required']);
        $this->assertSame(40.0, $result['attendance_rate']);
        $this->assertSame(40.0, $result['official_attendance_rate']);
        $this->assertSame(2, $result['counts']['PRESENT']);
        $this->assertSame(1, $result['late_count']);
        $this->assertSame(5, $result['resolved_opportunities']);
        $this->assertSame(0, $result['missing_opportunities']);
        $this->assertSame(1, $result['counts']['PERMISSION']);
        $this->assertSame(1, $result['counts']['SICK']);
        $this->assertSame(1, $result['counts']['ABSENT']);
        $this->assertSame(5, $result['accounting_invariant']['eligible_opportunities']);
        $this->assertTrue($result['accounting_invariant']['is_balanced']);
    }

    public function test_excused_fails_closed_for_reconciliation(): void
    {
        $result = app(CanonicalAttendanceSemanticService::class)->summarize(collect([$this->participant('EXCUSED', true)]));

        $this->assertTrue($result['semantic_decision_required']);
        $this->assertTrue($result['reconciliation_required_count'] === 1);
        $this->assertSame(['EXCUSED' => 1], $result['reconciliation_required_statuses']);
        $this->assertSame(0, $result['resolved_opportunities']);
        $this->assertSame(0, $result['missing_opportunities']);
        $this->assertNull($result['official_attendance_rate']);
        $this->assertSame(0, $result['counts']['PRESENT']);
        $this->assertTrue($result['accounting_invariant']['is_balanced']);
    }

    public function test_partial_attendance_keeps_eligible_denominator_and_separates_missing(): void
    {
        $result = app(CanonicalAttendanceSemanticService::class)->summarize(collect([
            $this->participant('PRESENT', true),
            $this->participant('LATE', true),
            $this->participant('IZIN', true),
            $this->participant('SICK', true),
            $this->participant('ABSENT', true),
            $this->participant('EXCUSED', true),
            $this->participant(null, true),
        ]));

        $this->assertSame(7, $result['eligible_opportunities']);
        $this->assertSame(5, $result['resolved_opportunities']);
        $this->assertSame(1, $result['missing_opportunities']);
        $this->assertSame(28.57, $result['attendance_rate']);
        $this->assertSame(71.43, $result['completeness_rate']);
        $this->assertNull($result['official_attendance_rate']);
        $this->assertSame(7, $result['accounting_invariant']['eligible_opportunities']);
        $this->assertSame(5, $result['accounting_invariant']['resolved_opportunities']);
        $this->assertSame(1, $result['accounting_invariant']['missing_opportunities']);
        $this->assertSame(1, $result['accounting_invariant']['reconciliation_required_count']);
        $this->assertTrue($result['accounting_invariant']['is_balanced']);
    }

    public function test_zero_eligible_opportunities_have_not_applicable_rates(): void
    {
        $result = app(CanonicalAttendanceSemanticService::class)->summarize(collect());

        $this->assertSame(0, $result['eligible_opportunities']);
        $this->assertNull($result['attendance_rate']);
        $this->assertNull($result['official_attendance_rate']);
        $this->assertNull($result['completeness_rate']);
        $this->assertTrue($result['accounting_invariant']['is_balanced']);
    }

    public function test_reconciliation_required_live_source_is_not_authoritative(): void
    {
        [$class] = $this->academicFixture();
        $this->mockLiveMetrics([
            'eligible_opportunities' => 1,
            'resolved_opportunities' => 1,
            'missing_opportunities' => 0,
            'semantic_decision_required' => true,
            'counts' => ['PRESENT' => 1, 'PERMISSION' => 0, 'SICK' => 0, 'ABSENT' => 0],
        ]);
        AttendanceSourceCertification::create([
            'source_type' => 'LIVE_TRANSACTIONAL',
            'period' => '2026-07',
            'scope_type' => 'CLASS',
            'scope_key' => (string) $class->id,
            'metric' => 'ATTENDANCE',
            'source_reference' => 'LIVE_TRANSACTIONAL',
            'certification_status' => 'CERTIFIED',
            'certified_at' => now(),
        ]);

        $result = app(AttendanceSourceAuthorityResolver::class)->resolve('2026-07', 'CLASS', (string) $class->id);

        $this->assertSame(AttendanceAuthorityStatus::DATA_INCOMPLETE->value, $result['authority_status']);
        $this->assertNull($result['official_attendance_rate']);
    }

    public function test_published_legacy_without_certification_is_not_authoritative(): void
    {
        [$class, $batch] = $this->legacyFixture();
        MonthlyAttendanceSummary::create(['period' => '2026-07', 'class_id' => $class->id, 'attendance_group' => '3A', 'matiq_report_class' => 'Kelas 3', 'roster' => 1, 'present' => 1, 'permission' => 0, 'sick' => 0, 'absent' => 0, 'eligible' => 1, 'non_eligible' => 0, 'attendance_rate' => 1, 'import_batch_id' => $batch->id, 'source_checksum' => str_repeat('a', 64), 'status' => 'PUBLISHED']);

        $this->mockLiveMetrics(['eligible_opportunities' => 1, 'resolved_opportunities' => 1, 'missing_opportunities' => 0, 'counts' => ['PRESENT' => 1, 'PERMISSION' => 0, 'SICK' => 0, 'ABSENT' => 0, 'LATE' => 0, 'EXCUSED' => 0]]);
        $result = app(AttendanceSourceAuthorityResolver::class)->resolve('2026-07', 'CLASS', (string) $class->id);

        $this->assertSame(AttendanceAuthorityStatus::DATA_NOT_CERTIFIED->value, $result['authority_status']);
        $this->assertNull($result['official_attendance_rate']);
    }

    public function test_incomplete_live_source_is_not_official(): void
    {
        [$class] = $this->academicFixture();
        $this->mockLiveMetrics(['eligible_opportunities' => 2, 'resolved_opportunities' => 0, 'missing_opportunities' => 2, 'counts' => ['PRESENT' => 0, 'IZIN' => 0, 'SICK' => 0, 'ABSENT' => 0]]);

        $result = app(AttendanceSourceAuthorityResolver::class)->resolve('2026-07', 'CLASS', (string) $class->id);

        $this->assertSame(AttendanceAuthorityStatus::DATA_INCOMPLETE->value, $result['authority_status']);
        $this->assertNull($result['official_attendance_rate']);
    }

    public function test_two_certified_disagreeing_sources_return_source_conflict(): void
    {
        [$class, $batch] = $this->legacyFixture();
        MonthlyAttendanceSummary::create(['period' => '2026-07', 'class_id' => $class->id, 'attendance_group' => '3A', 'matiq_report_class' => 'Kelas 3', 'roster' => 1, 'present' => 1, 'permission' => 0, 'sick' => 0, 'absent' => 0, 'eligible' => 1, 'non_eligible' => 0, 'attendance_rate' => 1, 'import_batch_id' => $batch->id, 'source_checksum' => str_repeat('b', 64), 'status' => 'PUBLISHED']);
        $this->mockLiveMetrics(['eligible_opportunities' => 2, 'resolved_opportunities' => 2, 'missing_opportunities' => 0, 'counts' => ['PRESENT' => 1, 'PERMISSION' => 1, 'SICK' => 0, 'ABSENT' => 0, 'LATE' => 0, 'EXCUSED' => 0]]);
        foreach (['LEGACY_MONTHLY_SNAPSHOT', 'LIVE_TRANSACTIONAL'] as $source) {
            AttendanceSourceCertification::create(['source_type' => $source, 'period' => '2026-07', 'scope_type' => 'CLASS', 'scope_key' => (string) $class->id, 'metric' => 'ATTENDANCE', 'source_reference' => $source, 'certification_status' => 'CERTIFIED', 'certified_at' => now()]);
        }

        $result = app(AttendanceSourceAuthorityResolver::class)->resolve('2026-07', 'CLASS', (string) $class->id);

        $this->assertSame(AttendanceAuthorityStatus::SOURCE_CONFLICT->value, $result['authority_status']);
        $this->assertNull($result['official_attendance_rate']);
        $this->assertCount(2, $result['provenance']['candidate_sources']);
    }

    public function test_class_lineage_resolver_returns_only_one_approved_effective_mapping(): void
    {
        [$class] = $this->academicFixture();
        ClassLineageMapping::create(['source_class_reference' => '3A', 'target_class_id' => $class->id, 'mapping_context' => 'JULY_2026', 'effective_from' => '2026-07-01', 'status' => 'APPROVED']);

        $resolved = app(ClassLineageResolver::class)->resolve('3A', 'JULY_2026', Carbon::parse('2026-07-15'));

        $this->assertNotNull($resolved);
        $this->assertSame($class->id, $resolved->target_class_id);

        ClassLineageMapping::create(['source_class_reference' => '3A', 'target_class_id' => $class->id, 'mapping_context' => 'JULY_2026', 'effective_from' => '2026-07-01', 'status' => 'APPROVED']);
        $this->assertNull(app(ClassLineageResolver::class)->resolve('3A', 'JULY_2026', Carbon::parse('2026-07-15')));
    }

    private function participant(?string $status, bool $required): SessionStudentParticipant
    {
        $participant = new SessionStudentParticipant(['is_required' => $required, 'eligibility_status' => $required ? 'ELIGIBLE' : 'NON_ELIGIBLE']);
        if ($status !== null) {
            $attendance = new StudentAttendance(['attendance_status' => $status, 'workflow_status' => 'VALIDATED']);
            $participant->setRelation('attendance', $attendance);
        }

        return $participant;
    }

    private function mockLiveMetrics(array $result): void
    {
        $this->mock(CanonicalAttendanceSemanticService::class, function ($mock) use ($result): void {
            $mock->shouldReceive('forClassPeriod')->andReturn($result);
        });
    }

    private function academicFixture(): array
    {
        $unit = OrganizationalUnit::create(['unit_code' => 'UNIT-AI', 'unit_name' => 'AI Unit', 'unit_type' => 'SCHOOL']);
        $grade = GradeLevel::create(['organizational_unit_id' => $unit->id, 'level_code' => '3', 'display_name' => 'Tingkat 3', 'sequence_no' => 3]);
        $year = AcademicYear::create(['year_code' => '2026-AI', 'display_name' => '2026/2027', 'starts_on' => '2026-07-01', 'ends_on' => '2027-06-30']);
        $class = AcademicClass::create(['class_code' => 'CLASS-AI', 'academic_year_id' => $year->id, 'organizational_unit_id' => $unit->id, 'grade_level_id' => $grade->id, 'section_code' => 'A', 'display_name' => 'Kelas AI']);

        return [$class];
    }

    private function legacyFixture(): array
    {
        [$class] = $this->academicFixture();
        $batch = ImportBatch::create(['batch_code' => 'BATCH-AI-'.uniqid(), 'source_system' => 'TEST', 'source_period' => '2026-07', 'status' => 'RECONCILED']);

        return [$class, $batch];
    }
}
