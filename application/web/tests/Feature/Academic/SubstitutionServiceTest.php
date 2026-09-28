<?php

namespace Tests\Feature\Academic;

use App\Domains\Academic\Exceptions\ScheduleConflictException;
use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Models\ClassSession;
use App\Domains\Academic\Models\GradeLevel;
use App\Domains\Academic\Models\ScheduleChange;
use App\Domains\Academic\Models\Semester;
use App\Domains\Academic\Models\SessionTeacherParticipation;
use App\Domains\Academic\Models\Subject;
use App\Domains\Academic\Models\TeachingAssignment;
use App\Domains\Academic\Services\SubstitutionService;
use App\Models\User;
use App\Shared\Core\Models\AcademicYear;
use App\Shared\Core\Models\OrganizationalUnit;
use App\Shared\Core\Models\Staff;
use App\Shared\Platform\Audit\Models\AuditLog;
use App\Shared\Platform\Authorization\Models\Permission;
use App\Shared\Platform\Authorization\Models\Role;
use App\Shared\Platform\Authorization\Models\UserRoleAssignment;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SubstitutionServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_schedule_changes_support_substitution_audit_fields(): void
    {
        $this->assertTrue(Schema::hasTable('schedule_changes'));
        foreach (['change_type', 'source_session_id', 'original_teacher_id', 'replacement_teacher_id', 'reason', 'status'] as $column) {
            $this->assertTrue(Schema::hasColumn('schedule_changes', $column));
        }
    }

    public function test_apply_substitution_preserves_primary_and_adds_expected_substitute(): void
    {
        [$session, $original, $replacement] = $this->fixtures();
        $change = app(SubstitutionService::class)->applySubstitution($session, $replacement, null, 'Original teacher unavailable');

        $this->assertInstanceOf(ScheduleChange::class, $change);
        $this->assertSame('SUBSTITUTION', $change->change_type);
        $this->assertSame('APPLIED', $change->status);
        $this->assertDatabaseHas('session_teacher_participations', ['class_session_id' => $session->id, 'teacher_staff_id' => $original->id, 'role' => 'PRIMARY']);
        $this->assertDatabaseHas('session_teacher_participations', ['class_session_id' => $session->id, 'teacher_staff_id' => $replacement->id, 'role' => 'SUBSTITUTE', 'participation_status' => 'EXPECTED']);
    }

    public function test_substitution_rejects_replacement_teacher_conflict_before_mutation(): void
    {
        [$session, , $replacement] = $this->fixtures();
        $otherClass = AcademicClass::create(['class_code' => 'CLASS-SUB-CONFLICT', 'academic_year_id' => $session->academicClass->academic_year_id, 'organizational_unit_id' => $session->academicClass->organizational_unit_id, 'grade_level_id' => $session->academicClass->grade_level_id, 'section_code' => 'B', 'display_name' => 'Kelas Konflik']);
        $otherAssignment = TeachingAssignment::create(['assignment_code' => 'TA-SUB-CONFLICT', 'semester_id' => $session->teachingAssignment->semester_id, 'class_id' => $otherClass->id, 'subject_id' => $session->subject_id, 'teacher_staff_id' => $replacement->id, 'effective_from' => '2026-07-01', 'workflow_status' => 'ACTIVE']);
        ClassSession::create(['session_code' => 'SESSION-SUB-CONFLICT', 'teaching_assignment_id' => $otherAssignment->id, 'class_id' => $otherClass->id, 'subject_id' => $session->subject_id, 'planned_start_at' => '2026-07-06 08:30:00', 'planned_end_at' => '2026-07-06 09:00:00', 'session_source' => 'SCHEDULED', 'participant_scope' => 'FULL_CLASS', 'session_status' => 'PLANNED']);

        $this->expectException(ScheduleConflictException::class);
        app(SubstitutionService::class)->applySubstitution($session, $replacement, null, 'Conflict test');
    }

    public function test_original_teacher_and_expected_substitute_overlap_are_rejected(): void
    {
        [$session, $original, $replacement] = $this->fixtures('OVERLAP');
        $this->expectException(ScheduleConflictException::class);
        app(SubstitutionService::class)->applySubstitution($session, $original, null, 'Same teacher');

        [$session, , $replacement] = $this->fixtures('SUBSTITUTE');
        [$otherSession] = $this->additionalSession($session, '08:30:00', '09:00:00');
        $otherSession->update(['session_status' => 'CANCELLED']);
        SessionTeacherParticipation::create(['class_session_id' => $otherSession->id, 'teacher_staff_id' => $replacement->id, 'role' => 'SUBSTITUTE', 'obligation_type' => 'REPLACEMENT', 'participation_status' => 'EXPECTED']);
        $otherSession->update(['session_status' => 'PLANNED']);

        $this->expectException(ScheduleConflictException::class);
        app(SubstitutionService::class)->applySubstitution($session, $replacement, null, 'Substitute overlap');
    }

    public function test_non_overlapping_adjacent_and_cancelled_sessions_do_not_conflict(): void
    {
        foreach ([['10:00:00', '11:00:00'], ['09:30:00', '10:00:00']] as [$start, $end]) {
            [$session, , $replacement] = $this->fixtures($start === '10:00:00' ? 'NONOVERLAP' : 'ADJACENT');
            $this->additionalSession($session, $start, $end, $replacement);
            $this->assertSame('APPLIED', app(SubstitutionService::class)->applySubstitution($session, $replacement, null, 'No overlap')->status);
        }

        [$session, , $replacement] = $this->fixtures('CANCELLED');
        [$otherSession] = $this->additionalSession($session, '08:30:00', '09:00:00', $replacement);
        $otherSession->update(['session_status' => 'CANCELLED']);
        $this->assertSame('APPLIED', app(SubstitutionService::class)->applySubstitution($session, $replacement, null, 'Cancelled other session')->status);
    }

    public function test_invalid_target_is_atomic_and_unauthorized_actor_is_denied(): void
    {
        [$session, , $replacement] = $this->fixtures('INVALID');
        $session->update(['session_status' => 'COMPLETED']);
        $actor = User::factory()->create();

        try {
            app(SubstitutionService::class)->applySubstitution($session, $replacement, $actor->id, 'Invalid target');
            $this->fail('Expected invalid target to be rejected.');
        } catch (ScheduleConflictException $exception) {
            $this->assertSame('SESSION_NOT_WRITABLE', $exception->conflict['conflict_type']);
        }

        $this->assertDatabaseCount('schedule_changes', 0);
        $this->assertDatabaseCount('session_teacher_participations', 1);
        $this->assertSame(0, AuditLog::where('action', 'TEACHER_SUBSTITUTION_APPLIED')->count());
        $session->update(['session_status' => 'PLANNED']);

        $this->expectException(AuthorizationException::class);
        app(SubstitutionService::class)->applySubstitution($session, $replacement, $actor->id, 'Unauthorized');
    }

    public function test_waka_and_super_admin_authority_can_apply_substitution(): void
    {
        foreach ([['WAKA_AKADEMIK', 'academic.domain.manage'], ['SUPER_ADMIN', 'platform.institution.manage']] as [$roleCode, $permissionCode]) {
            [$session, , $replacement] = $this->fixtures($roleCode);
            $actor = User::factory()->create();
            $role = Role::create(['code' => $roleCode, 'name' => $roleCode]);
            $permission = Permission::create(['code' => $permissionCode, 'name' => $permissionCode]);
            $role->permissions()->attach($permission->id);
            UserRoleAssignment::create(['user_id' => $actor->id, 'role_id' => $role->id, 'effective_from' => '2026-07-01']);

            $this->assertSame('APPLIED', app(SubstitutionService::class)->applySubstitution($session, $replacement, $actor->id, 'Authorized substitution')->status);
        }
    }

    private function additionalSession(ClassSession $source, string $start, string $end, ?Staff $teacher = null): array
    {
        $sourceClass = $source->academicClass;
        $otherClass = AcademicClass::create(['class_code' => 'CLASS-SUB-OTHER-'.str()->random(8), 'academic_year_id' => $sourceClass->academic_year_id, 'organizational_unit_id' => $sourceClass->organizational_unit_id, 'grade_level_id' => $sourceClass->grade_level_id, 'section_code' => 'X', 'display_name' => 'Kelas Lain']);
        $assignment = TeachingAssignment::create(['assignment_code' => 'TA-OTHER-'.str()->uuid(), 'semester_id' => $source->teachingAssignment->semester_id, 'class_id' => $otherClass->id, 'subject_id' => $source->subject_id, 'teacher_staff_id' => ($teacher ?? $source->teachingAssignment->teacher)->id, 'effective_from' => '2026-07-01', 'workflow_status' => 'ACTIVE']);
        $session = ClassSession::create(['session_code' => 'SESSION-OTHER-'.str()->uuid(), 'teaching_assignment_id' => $assignment->id, 'class_id' => $otherClass->id, 'subject_id' => $source->subject_id, 'planned_start_at' => '2026-07-06 '.$start, 'planned_end_at' => '2026-07-06 '.$end, 'session_source' => 'SCHEDULED', 'participant_scope' => 'FULL_CLASS', 'session_status' => 'PLANNED']);

        return [$session, $assignment];
    }

    private function fixtures(string $suffix = ''): array
    {
        $suffix = $suffix === '' ? '' : '-'.$suffix;
        $unit = OrganizationalUnit::create(['unit_code' => 'UNIT-SUB'.$suffix, 'unit_name' => 'Substitution Unit'.$suffix, 'unit_type' => 'SCHOOL']);
        $grade = GradeLevel::create(['organizational_unit_id' => $unit->id, 'level_code' => '1', 'display_name' => 'Tingkat 1', 'sequence_no' => 1]);
        $year = AcademicYear::create(['year_code' => '2026-SUB'.$suffix, 'display_name' => '2026/2027'.$suffix, 'starts_on' => '2026-07-01', 'ends_on' => '2027-06-30']);
        $semester = Semester::create(['academic_year_id' => $year->id, 'semester_code' => 'ODD', 'display_name' => 'Semester Ganjil', 'sequence_no' => 1, 'starts_on' => '2026-07-01', 'ends_on' => '2026-12-31']);
        $class = AcademicClass::create(['class_code' => 'CLASS-SUB-A'.$suffix, 'academic_year_id' => $year->id, 'organizational_unit_id' => $unit->id, 'grade_level_id' => $grade->id, 'section_code' => 'A', 'display_name' => 'Kelas 1 A'.$suffix]);
        $subject = Subject::create(['subject_code' => 'SUBJ-SUB'.$suffix, 'subject_name' => 'Substitution Subject'.$suffix]);
        $original = Staff::create(['staff_code' => 'STAFF-SUB-ORIGINAL'.$suffix, 'full_name' => 'Original Staff'.$suffix]);
        $replacement = Staff::create(['staff_code' => 'STAFF-SUB-REPLACEMENT'.$suffix, 'full_name' => 'Replacement Staff'.$suffix]);
        $assignment = TeachingAssignment::create(['assignment_code' => 'TA-SUB-001'.$suffix, 'semester_id' => $semester->id, 'class_id' => $class->id, 'subject_id' => $subject->id, 'teacher_staff_id' => $original->id, 'effective_from' => '2026-07-01', 'workflow_status' => 'ACTIVE']);
        $session = ClassSession::create(['session_code' => 'SESSION-SUB-001'.$suffix, 'teaching_assignment_id' => $assignment->id, 'class_id' => $class->id, 'subject_id' => $subject->id, 'planned_start_at' => '2026-07-06 08:00:00', 'planned_end_at' => '2026-07-06 09:30:00', 'session_source' => 'SCHEDULED', 'participant_scope' => 'FULL_CLASS', 'session_status' => 'PLANNED']);
        SessionTeacherParticipation::create(['class_session_id' => $session->id, 'teacher_staff_id' => $original->id, 'role' => 'PRIMARY', 'obligation_type' => 'TEACHING_ASSIGNMENT']);

        return [$session, $original, $replacement];
    }
}
