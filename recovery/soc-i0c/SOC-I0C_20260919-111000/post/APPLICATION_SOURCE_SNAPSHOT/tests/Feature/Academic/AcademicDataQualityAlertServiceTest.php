<?php

namespace Tests\Feature\Academic;

use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Models\ClassHomeroomAssignment;
use App\Domains\Academic\Models\ClassSession;
use App\Domains\Academic\Models\GradeLevel;
use App\Domains\Academic\Models\Semester;
use App\Domains\Academic\Models\SessionStudentParticipant;
use App\Domains\Academic\Models\StudentAttendance;
use App\Domains\Academic\Models\Subject;
use App\Domains\Academic\Models\TeachingAssignment;
use App\Domains\Academic\Services\AcademicDataQualityAlertService;
use App\Models\User;
use App\Shared\Core\Models\AcademicYear;
use App\Shared\Core\Models\OrganizationalUnit;
use App\Shared\Core\Models\Staff;
use App\Shared\Core\Models\Student;
use App\Shared\Platform\Alerts\Models\Alert;
use App\Shared\Platform\Alerts\Models\AlertRule;
use App\Shared\Platform\Alerts\Services\AlertService;
use App\Shared\Platform\Audit\Models\AuditLog;
use App\Shared\Platform\Authorization\Models\Role;
use App\Shared\Platform\Authorization\Models\UserRoleAssignment;
use App\Shared\Platform\Authorization\Models\UserStaffLink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademicDataQualityAlertServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_missing_attendance_raises_deduplicated_alert_and_clear_closes_it(): void
    {
        [$session, $participant, $owner] = $this->fixture();
        $rule = AlertRule::create(['rule_code' => 'ACADEMIC_ATTENDANCE_INCOMPLETE', 'name' => 'Incomplete attendance', 'category' => 'DQ']);
        $service = app(AcademicDataQualityAlertService::class);

        $first = $service->evaluateMissingAttendance($session, $rule);
        $duplicate = $service->evaluateMissingAttendance($session, $rule);
        StudentAttendance::create(['session_student_participant_id' => $participant->id, 'attendance_status' => 'PRESENT', 'workflow_status' => 'VALIDATED', 'entered_by' => $owner->id, 'entered_at' => now(), 'finalized_by' => $owner->id, 'finalized_at' => now(), 'updated_by' => $owner->id, 'updated_at' => now()]);
        $cleared = $service->evaluateMissingAttendance($session, $rule);

        $this->assertSame($first->id, $duplicate->id);
        $this->assertSame('CLOSED', $cleared->status);
        $this->assertSame(1, Alert::count());
    }

    public function test_cancelled_session_is_not_applicable_and_does_not_raise_alert(): void
    {
        [$session] = $this->fixture();
        $session->update(['session_status' => 'CANCELLED']);
        $rule = AlertRule::create(['rule_code' => 'ACADEMIC_ATTENDANCE_INCOMPLETE', 'name' => 'Incomplete attendance', 'category' => 'DQ']);

        $alert = app(AcademicDataQualityAlertService::class)->evaluateMissingAttendance($session, $rule);

        $this->assertNull($alert);
        $this->assertSame(0, Alert::count());
    }

    public function test_cancelled_session_without_owner_identity_still_returns_not_applicable(): void
    {
        [$session] = $this->fixture();
        $session->update(['session_status' => 'CANCELLED']);
        UserStaffLink::query()->delete();
        $rule = AlertRule::create(['rule_code' => 'ACADEMIC_ATTENDANCE_INCOMPLETE', 'name' => 'Incomplete attendance', 'category' => 'DQ']);

        $alert = app(AcademicDataQualityAlertService::class)->evaluateMissingAttendance($session, $rule);

        $this->assertNull($alert);
        $this->assertSame(0, Alert::count());
    }

    public function test_owner_change_reassigns_active_alert_without_creating_duplicate(): void
    {
        [$session, , $owner] = $this->fixture();
        $rule = AlertRule::create(['rule_code' => 'ACADEMIC_ATTENDANCE_INCOMPLETE', 'name' => 'Incomplete attendance', 'category' => 'DQ']);
        $service = app(AcademicDataQualityAlertService::class);
        $first = $service->evaluateMissingAttendance($session, $rule);
        $replacement = User::factory()->create();
        UserRoleAssignment::create(['user_id' => $replacement->id, 'role_id' => Role::query()->where('code', 'WALI_KELAS')->value('id'), 'effective_from' => '2026-07-01']);
        UserStaffLink::query()->where('user_id', $owner->id)->update(['user_id' => $replacement->id]);

        $reassigned = $service->evaluateMissingAttendance($session, $rule);

        $this->assertSame($first->id, $reassigned->id);
        $this->assertSame($replacement->id, $reassigned->owner_user_id);
        $this->assertSame(1, Alert::count());
        $this->assertSame(1, AuditLog::where('action', 'ALERT_OWNER_REASSIGNED')->count());
    }

    public function test_cleared_condition_auto_closes_acknowledged_alert(): void
    {
        [$session, $participant, $owner] = $this->fixture();
        $rule = AlertRule::create(['rule_code' => 'ACADEMIC_ATTENDANCE_INCOMPLETE', 'name' => 'Incomplete attendance', 'category' => 'DQ']);
        $service = app(AcademicDataQualityAlertService::class);
        $alert = $service->evaluateMissingAttendance($session, $rule);
        app(AlertService::class)->transition($alert, $owner, 'ACKNOWLEDGED');
        StudentAttendance::create(['session_student_participant_id' => $participant->id, 'attendance_status' => 'PRESENT', 'workflow_status' => 'VALIDATED', 'entered_by' => $owner->id, 'entered_at' => now(), 'finalized_by' => $owner->id, 'finalized_at' => now(), 'updated_by' => $owner->id, 'updated_at' => now()]);

        $cleared = $service->evaluateMissingAttendance($session, $rule);

        $this->assertSame('CLOSED', $cleared->status);
        $this->assertSame($owner->id, $cleared->resolved_by);
    }

    private function fixture(): array
    {
        $unit = OrganizationalUnit::create(['unit_code' => 'UNIT-DQ-ALERT', 'unit_name' => 'DQ Alert Unit', 'unit_type' => 'SCHOOL']);
        $level = GradeLevel::create(['organizational_unit_id' => $unit->id, 'level_code' => '1', 'display_name' => 'Tingkat 1', 'sequence_no' => 1]);
        $year = AcademicYear::create(['year_code' => '2026-DQ-ALERT', 'display_name' => '2026/2027', 'starts_on' => '2026-07-01', 'ends_on' => '2027-06-30']);
        $semester = Semester::create(['academic_year_id' => $year->id, 'semester_code' => 'ODD', 'display_name' => 'Ganjil', 'sequence_no' => 1, 'starts_on' => '2026-07-01', 'ends_on' => '2026-12-31']);
        $class = AcademicClass::create(['class_code' => 'CLASS-DQ-ALERT', 'academic_year_id' => $year->id, 'organizational_unit_id' => $unit->id, 'grade_level_id' => $level->id, 'section_code' => 'A', 'display_name' => 'Kelas DQ Alert']);
        $subject = Subject::create(['subject_code' => 'SUBJ-DQ-ALERT', 'subject_name' => 'DQ Alert']);
        $teacher = Staff::create(['staff_code' => 'STAFF-DQ-ALERT', 'full_name' => 'DQ Teacher']);
        $homeroom = Staff::create(['staff_code' => 'STAFF-DQ-WALI', 'full_name' => 'DQ Wali Kelas']);
        $owner = User::factory()->create();
        $role = Role::create(['code' => 'WALI_KELAS', 'name' => 'Wali Kelas']);
        UserRoleAssignment::create(['user_id' => $owner->id, 'role_id' => $role->id, 'effective_from' => '2026-07-01']);
        UserStaffLink::create(['user_id' => $owner->id, 'staff_id' => $homeroom->id, 'effective_from' => '2026-07-01']);
        ClassHomeroomAssignment::create(['class_id' => $class->id, 'staff_id' => $homeroom->id, 'effective_from' => '2026-07-01']);
        $assignment = TeachingAssignment::create(['assignment_code' => 'TA-DQ-ALERT', 'semester_id' => $semester->id, 'class_id' => $class->id, 'subject_id' => $subject->id, 'teacher_staff_id' => $teacher->id, 'effective_from' => '2026-07-01', 'workflow_status' => 'ACTIVE']);
        $session = ClassSession::create(['session_code' => 'SESSION-DQ-ALERT', 'teaching_assignment_id' => $assignment->id, 'class_id' => $class->id, 'subject_id' => $subject->id, 'planned_start_at' => '2026-07-06 08:00:00', 'planned_end_at' => '2026-07-06 09:00:00', 'session_source' => 'SCHEDULED', 'participant_scope' => 'FULL_CLASS', 'session_status' => 'COMPLETED']);
        $participant = SessionStudentParticipant::create(['class_session_id' => $session->id, 'student_id' => Student::create(['student_code' => 'STU-DQ-ALERT', 'full_name' => 'DQ Student'])->id, 'participant_basis' => 'CLASS_ENROLLMENT']);

        return [$session, $participant, $owner];
    }
}
