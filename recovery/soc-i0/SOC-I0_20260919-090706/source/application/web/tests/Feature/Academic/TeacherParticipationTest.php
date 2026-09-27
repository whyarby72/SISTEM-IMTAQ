<?php

namespace Tests\Feature\Academic;

use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Models\ClassSession;
use App\Domains\Academic\Models\GradeLevel;
use App\Domains\Academic\Models\Semester;
use App\Domains\Academic\Models\SessionTeacherParticipation;
use App\Domains\Academic\Models\Subject;
use App\Domains\Academic\Models\TeachingAssignment;
use App\Domains\Academic\Services\TeacherParticipationRecorder;
use App\Shared\Core\Models\AcademicYear;
use App\Shared\Core\Models\OrganizationalUnit;
use App\Shared\Core\Models\Staff;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TeacherParticipationTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_participation_has_obligation_and_nullable_attendance_fields(): void
    {
        $this->assertTrue(Schema::hasTable('session_teacher_participations'));
        foreach (['class_session_id', 'teacher_staff_id', 'role', 'obligation_type', 'participation_status', 'attendance_status'] as $column) {
            $this->assertTrue(Schema::hasColumn('session_teacher_participations', $column));
        }
    }

    public function test_primary_obligation_is_recorded_idempotently_from_teaching_assignment(): void
    {
        [$session, $staff] = $this->fixtures();
        $recorder = app(TeacherParticipationRecorder::class);
        $first = $recorder->ensurePrimary($session);
        $second = $recorder->ensurePrimary($session);

        $this->assertSame($first->id, $second->id);
        $this->assertSame($staff->id, $first->teacher_staff_id);
        $this->assertSame('PRIMARY', $first->role);
        $this->assertSame('TEACHING_ASSIGNMENT', $first->obligation_type);
        $this->assertNull($first->attendance_status);
        $first->update(['attendance_status' => 'PRESENT']);
        $second = $recorder->ensurePrimary($session);
        $this->assertSame('PRESENT', $second->fresh()->attendance_status);
        $this->assertDatabaseCount('session_teacher_participations', 1);
    }

    public function test_substitute_role_is_not_an_attendance_status(): void
    {
        [$session, $staff] = $this->fixtures();
        $participation = SessionTeacherParticipation::create([
            'class_session_id' => $session->id, 'teacher_staff_id' => $staff->id,
            'role' => 'SUBSTITUTE', 'obligation_type' => 'REPLACEMENT',
        ]);

        $this->assertSame('SUBSTITUTE', $participation->role);
        $this->assertNull($participation->attendance_status);
    }

    private function fixtures(): array
    {
        $unit = OrganizationalUnit::create(['unit_code' => 'UNIT-PARTICIPATION', 'unit_name' => 'Participation Unit', 'unit_type' => 'SCHOOL']);
        $grade = GradeLevel::create(['organizational_unit_id' => $unit->id, 'level_code' => '1', 'display_name' => 'Tingkat 1', 'sequence_no' => 1]);
        $year = AcademicYear::create(['year_code' => '2026-PARTICIPATION', 'display_name' => '2026/2027', 'starts_on' => '2026-07-01', 'ends_on' => '2027-06-30']);
        $semester = Semester::create(['academic_year_id' => $year->id, 'semester_code' => 'ODD', 'display_name' => 'Semester Ganjil', 'sequence_no' => 1, 'starts_on' => '2026-07-01', 'ends_on' => '2026-12-31']);
        $class = AcademicClass::create(['class_code' => 'CLASS-PARTICIPATION-A', 'academic_year_id' => $year->id, 'organizational_unit_id' => $unit->id, 'grade_level_id' => $grade->id, 'section_code' => 'A', 'display_name' => 'Kelas 1 A']);
        $subject = Subject::create(['subject_code' => 'SUBJ-PARTICIPATION', 'subject_name' => 'Participation Subject']);
        $staff = Staff::create(['staff_code' => 'STAFF-PARTICIPATION-001', 'full_name' => 'Participation Staff']);
        $assignment = TeachingAssignment::create(['assignment_code' => 'TA-PARTICIPATION-001', 'semester_id' => $semester->id, 'class_id' => $class->id, 'subject_id' => $subject->id, 'teacher_staff_id' => $staff->id, 'effective_from' => '2026-07-01', 'workflow_status' => 'ACTIVE']);
        $session = ClassSession::create(['session_code' => 'SESSION-PARTICIPATION-001', 'teaching_assignment_id' => $assignment->id, 'class_id' => $class->id, 'subject_id' => $subject->id, 'planned_start_at' => '2026-07-06 08:00:00', 'planned_end_at' => '2026-07-06 09:30:00', 'session_source' => 'SCHEDULED', 'participant_scope' => 'FULL_CLASS', 'session_status' => 'PLANNED']);

        return [$session, $staff];
    }
}
