<?php

namespace Tests\Feature\Academic;

use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Models\ClassSession;
use App\Domains\Academic\Models\GradeLevel;
use App\Domains\Academic\Models\ScheduleRule;
use App\Domains\Academic\Models\Semester;
use App\Domains\Academic\Models\SessionOccurrenceVersion;
use App\Domains\Academic\Models\Subject;
use App\Domains\Academic\Models\TeachingAssignment;
use App\Shared\Core\Models\AcademicYear;
use App\Shared\Core\Models\OrganizationalUnit;
use App\Shared\Core\Models\Staff;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;
use LogicException;
use Tests\TestCase;

class SessionOccurrencePersistenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_persistence_schema_has_one_occurrence_history_per_class_session_and_effective_pointer(): void
    {
        $this->assertTrue(Schema::hasTable('session_occurrence_versions'));
        $this->assertTrue(Schema::hasColumn('class_sessions', 'effective_occurrence_version_id'));
        $this->assertTrue(Schema::hasColumn('session_occurrence_versions', 'is_partial'));
        $this->assertTrue(Schema::hasColumn('session_occurrence_versions', 'replacement_class_session_id'));
    }

    public function test_allowed_vocabulary_is_accepted_and_invalid_vocabulary_is_rejected(): void
    {
        [$session] = $this->fixtures();
        foreach (SessionOccurrenceVersion::STATUSES as $index => $status) {
            $version = SessionOccurrenceVersion::create(['class_session_id' => $session->id, 'version_no' => $index + 1, 'occurrence_status' => $status]);
            $this->assertSame($status, $version->occurrence_status);
            $version->deleteQuietly();
        }

        $this->expectException(InvalidArgumentException::class);
        SessionOccurrenceVersion::create(['class_session_id' => $session->id, 'version_no' => 99, 'occurrence_status' => 'COMPLETED']);
    }

    public function test_versions_are_unique_per_session_and_history_is_preserved(): void
    {
        [$session] = $this->fixtures();
        $first = SessionOccurrenceVersion::create(['class_session_id' => $session->id, 'version_no' => 1, 'occurrence_status' => 'SCHEDULED']);
        $second = SessionOccurrenceVersion::create(['class_session_id' => $session->id, 'version_no' => 2, 'occurrence_status' => 'HELD', 'supersedes_version_id' => $first->id]);

        $this->assertCount(2, $session->occurrenceVersions()->get());
        $this->assertSame($first->id, $second->supersedes->id);
        $this->expectException(QueryException::class);
        SessionOccurrenceVersion::create(['class_session_id' => $session->id, 'version_no' => 2, 'occurrence_status' => 'CANCELLED']);
    }

    public function test_effective_pointer_returns_the_pointed_version_for_the_same_session(): void
    {
        [$session] = $this->fixtures();
        $version = SessionOccurrenceVersion::create(['class_session_id' => $session->id, 'version_no' => 1, 'occurrence_status' => 'SCHEDULED']);
        DB::table('class_sessions')->where('id', $session->id)->update(['effective_occurrence_version_id' => $version->id]);

        $this->assertSame($version->id, $session->fresh()->effectiveOccurrenceVersion->id);
    }

    public function test_partial_is_metadata_and_joint_grain_keeps_one_history_for_one_session(): void
    {
        [$session] = $this->fixtures();
        SessionOccurrenceVersion::create(['class_session_id' => $session->id, 'version_no' => 1, 'occurrence_status' => 'HELD', 'is_partial' => true, 'partial_reason' => 'Kegiatan selesai lebih awal']);

        $this->assertSame(1, SessionOccurrenceVersion::where('class_session_id', $session->id)->count());
        $this->assertTrue((bool) $session->occurrenceVersions()->first()->is_partial);
        $this->assertSame('HELD', $session->occurrenceVersions()->first()->occurrence_status);
    }

    public function test_legacy_class_session_has_no_effective_occurrence_and_versions_are_immutable(): void
    {
        [$session] = $this->fixtures();
        $this->assertNull($session->effective_occurrence_version_id);
        $version = SessionOccurrenceVersion::create(['class_session_id' => $session->id, 'version_no' => 1, 'occurrence_status' => 'SCHEDULED']);

        $this->expectException(LogicException::class);
        $version->update(['reason' => 'should not mutate history']);
    }

    public function test_migration_b_semantics_remain_inactive_and_migration_a_constraints_remain_present(): void
    {
        $this->assertSame(0, DB::table('attendance_source_certifications')->count());
        $this->assertSame(0, DB::table('class_lineage_mappings')->count());
        $this->assertSame(0, DB::table('session_student_participants')->whereNotNull('eligibility_status')->count());
        $this->assertTrue(Schema::hasColumn('session_student_participants', 'non_eligible_reason'));
    }

    private function fixtures(): array
    {
        $unit = OrganizationalUnit::create(['unit_code' => 'UNIT-OCCURRENCE', 'unit_name' => 'Occurrence Unit', 'unit_type' => 'SCHOOL']);
        $grade = GradeLevel::create(['organizational_unit_id' => $unit->id, 'level_code' => '1', 'display_name' => 'Tingkat 1', 'sequence_no' => 1]);
        $year = AcademicYear::create(['year_code' => '2026-OCCURRENCE', 'display_name' => '2026/2027', 'starts_on' => '2026-07-01', 'ends_on' => '2027-06-30']);
        $semester = Semester::create(['academic_year_id' => $year->id, 'semester_code' => 'ODD', 'display_name' => 'Semester Ganjil', 'sequence_no' => 1, 'starts_on' => '2026-07-01', 'ends_on' => '2026-12-31']);
        $class = AcademicClass::create(['class_code' => 'CLASS-OCCURRENCE-A', 'academic_year_id' => $year->id, 'organizational_unit_id' => $unit->id, 'grade_level_id' => $grade->id, 'section_code' => 'A', 'display_name' => 'Kelas 1 A']);
        $subject = Subject::create(['subject_code' => 'SUBJ-OCCURRENCE', 'subject_name' => 'Occurrence Subject']);
        $staff = Staff::create(['staff_code' => 'STAFF-OCCURRENCE-001', 'full_name' => 'Occurrence Staff']);
        $assignment = TeachingAssignment::create(['assignment_code' => 'TA-OCCURRENCE-001', 'semester_id' => $semester->id, 'class_id' => $class->id, 'subject_id' => $subject->id, 'teacher_staff_id' => $staff->id, 'effective_from' => '2026-07-01', 'workflow_status' => 'ACTIVE']);
        $rule = ScheduleRule::create(['teaching_assignment_id' => $assignment->id, 'weekday' => 1, 'start_time' => '08:00', 'end_time' => '09:30', 'recurrence_type' => 'EVERY_WEEK', 'effective_from' => '2026-07-01', 'workflow_status' => 'ACTIVE']);
        $session = ClassSession::create(['session_code' => 'SESSION-OCCURRENCE-001', 'teaching_assignment_id' => $assignment->id, 'schedule_rule_id' => $rule->id, 'class_id' => $class->id, 'subject_id' => $subject->id, 'planned_start_at' => '2026-07-06 08:00:00', 'planned_end_at' => '2026-07-06 09:30:00', 'session_source' => 'SCHEDULED', 'participant_scope' => 'FULL_CLASS', 'session_status' => 'PLANNED']);

        return [$session];
    }
}
