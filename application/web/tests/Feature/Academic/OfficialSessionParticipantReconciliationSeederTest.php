<?php

namespace Tests\Feature\Academic;

use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Models\ClassSession;
use App\Domains\Academic\Models\GradeLevel;
use App\Domains\Academic\Models\Semester;
use App\Domains\Academic\Models\SessionStudentParticipant;
use App\Domains\Academic\Models\StudentClassEnrollment;
use App\Domains\Academic\Models\Subject;
use App\Domains\Academic\Models\TeachingAssignment;
use App\Shared\Core\Models\AcademicYear;
use App\Shared\Core\Models\OrganizationalUnit;
use App\Shared\Core\Models\Staff;
use App\Shared\Core\Models\Student;
use Database\Seeders\OfficialSessionParticipantReconciliationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfficialSessionParticipantReconciliationSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_official_sessions_receive_idempotent_participants(): void
    {
        $unit = OrganizationalUnit::create(['unit_code' => 'UNIT-SESSION-RECON', 'unit_name' => 'IMTAQ ISY KARIMA', 'unit_type' => 'SCHOOL']);
        $grade = GradeLevel::create(['organizational_unit_id' => $unit->id, 'level_code' => '1', 'display_name' => 'Tingkat 1', 'sequence_no' => 1]);
        $officialYear = AcademicYear::create(['year_code' => '2026/2027', 'display_name' => 'Resmi', 'starts_on' => '2026-07-01', 'ends_on' => '2027-06-30']);
        $pilotYear = AcademicYear::create(['year_code' => '2026/2027-PILOT', 'display_name' => 'Pilot', 'starts_on' => '2026-07-01', 'ends_on' => '2027-06-30']);
        $officialClass = AcademicClass::create(['class_code' => 'IMTAQ-2026-1', 'academic_year_id' => $officialYear->id, 'organizational_unit_id' => $unit->id, 'grade_level_id' => $grade->id, 'section_code' => '1', 'display_name' => 'Kelas 1']);
        $pilotClass = AcademicClass::create(['class_code' => '1', 'academic_year_id' => $pilotYear->id, 'organizational_unit_id' => $unit->id, 'grade_level_id' => $grade->id, 'section_code' => '1', 'display_name' => 'Kelas 1 Pilot']);
        $subject = Subject::create(['subject_code' => 'SUBJ-SESSION-RECON', 'subject_name' => 'Pelajaran']);
        $staff = Staff::create(['staff_code' => 'STAFF-SESSION-RECON', 'full_name' => 'Guru']);
        $semester = Semester::create(['academic_year_id' => $officialYear->id, 'semester_code' => 'ODD', 'display_name' => 'Ganjil', 'sequence_no' => 1, 'starts_on' => '2026-07-01', 'ends_on' => '2026-12-31']);
        $assignment = TeachingAssignment::create(['assignment_code' => 'TA-SESSION-RECON', 'semester_id' => $semester->id, 'class_id' => $officialClass->id, 'subject_id' => $subject->id, 'teacher_staff_id' => $staff->id, 'effective_from' => '2026-07-01', 'workflow_status' => 'ACTIVE']);
        $officialSession = ClassSession::create(['session_code' => 'SESSION-OFFICIAL-RECON', 'teaching_assignment_id' => $assignment->id, 'class_id' => $officialClass->id, 'subject_id' => $subject->id, 'planned_start_at' => '2026-07-06 08:00:00', 'planned_end_at' => '2026-07-06 09:00:00', 'session_source' => 'SCHEDULED', 'participant_scope' => 'FULL_CLASS', 'session_status' => 'PLANNED']);
        $pilotAssignment = TeachingAssignment::create(['assignment_code' => 'TA-PILOT-RECON', 'semester_id' => $semester->id, 'class_id' => $pilotClass->id, 'subject_id' => $subject->id, 'teacher_staff_id' => $staff->id, 'effective_from' => '2026-07-01', 'workflow_status' => 'ACTIVE']);
        $pilotSession = ClassSession::create(['session_code' => 'SESSION-PILOT-RECON', 'teaching_assignment_id' => $pilotAssignment->id, 'class_id' => $pilotClass->id, 'subject_id' => $subject->id, 'planned_start_at' => '2026-07-06 10:00:00', 'planned_end_at' => '2026-07-06 11:00:00', 'session_source' => 'SCHEDULED', 'participant_scope' => 'FULL_CLASS', 'session_status' => 'PLANNED']);
        $student = Student::create(['student_code' => 'STU-SESSION-RECON', 'full_name' => 'Santri']);
        StudentClassEnrollment::create(['student_id' => $student->id, 'class_id' => $officialClass->id, 'effective_from' => '2026-07-01']);

        $this->seed(OfficialSessionParticipantReconciliationSeeder::class);
        $this->seed(OfficialSessionParticipantReconciliationSeeder::class);

        $this->assertDatabaseHas('session_student_participants', ['class_session_id' => $officialSession->id, 'student_id' => $student->id, 'participant_basis' => 'CLASS_ENROLLMENT']);
        $this->assertDatabaseMissing('session_student_participants', ['class_session_id' => $pilotSession->id]);
        $this->assertSame(1, SessionStudentParticipant::where('class_session_id', $officialSession->id)->count());
    }
}
