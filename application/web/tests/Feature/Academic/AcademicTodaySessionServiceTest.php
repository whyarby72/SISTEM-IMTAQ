<?php

namespace Tests\Feature\Academic;

use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Models\ClassSession;
use App\Domains\Academic\Models\ClassSessionGroup;
use App\Domains\Academic\Models\GradeLevel;
use App\Domains\Academic\Models\Semester;
use App\Domains\Academic\Models\Subject;
use App\Domains\Academic\Models\TeachingAssignment;
use App\Domains\Academic\Services\AcademicTodaySessionService;
use App\Models\User;
use App\Shared\Core\Models\AcademicYear;
use App\Shared\Core\Models\OrganizationalUnit;
use App\Shared\Core\Models\Staff;
use App\Shared\Platform\Authorization\Models\Role;
use App\Shared\Platform\Authorization\Models\UserRoleAssignment;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AcademicTodaySessionServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_today_window_and_open_state_classification_are_deterministic(): void
    {
        [$class, $subject, $user] = $this->fixture();
        $this->makeSession($class, $subject, Carbon::parse('2026-09-12 10:00:00', 'Asia/Jakarta'), Carbon::parse('2026-09-12 11:00:00', 'Asia/Jakarta'), 'PLANNED');
        $this->makeSession($class, $subject, Carbon::parse('2026-09-12 12:00:00', 'Asia/Jakarta'), Carbon::parse('2026-09-12 13:00:00', 'Asia/Jakarta'), 'CONFIRMED');
        $this->makeSession($class, $subject, Carbon::parse('2026-09-12 13:00:00', 'Asia/Jakarta'), Carbon::parse('2026-09-12 14:00:00', 'Asia/Jakarta'), 'PLANNED');

        $result = app(AcademicTodaySessionService::class)->forUser($user, Carbon::parse('2026-09-12 10:30:00', 'Asia/Jakarta'));

        $this->assertSame('2026-09-12', $result['date']);
        $this->assertSame('Asia/Jakarta', $result['timezone']);
        $this->assertSame(3, $result['active_total']);
        $this->assertSame(['IN_PROGRESS', 'UPCOMING', 'UPCOMING'], collect($result['items'])->pluck('operational_state')->all());
    }

    public function test_completed_and_overdue_states_are_distinguished(): void
    {
        [$class, $subject, $user] = $this->fixture();
        $this->makeSession($class, $subject, Carbon::parse('2026-09-12 07:00:00', 'Asia/Jakarta'), Carbon::parse('2026-09-12 08:00:00', 'Asia/Jakarta'), 'COMPLETED');
        $this->makeSession($class, $subject, Carbon::parse('2026-09-12 08:00:00', 'Asia/Jakarta'), Carbon::parse('2026-09-12 09:00:00', 'Asia/Jakarta'), 'PLANNED');

        $result = app(AcademicTodaySessionService::class)->forUser($user, Carbon::parse('2026-09-12 10:00:00', 'Asia/Jakarta'));

        $this->assertSame(2, $result['active_total']);
        $this->assertSame(1, $result['completed_count']);
        $this->assertSame(1, $result['overdue_unfinished_count']);
    }

    public function test_cancelled_and_rescheduled_source_are_separate_from_active_total(): void
    {
        [$class, $subject, $user] = $this->fixture();
        $this->makeSession($class, $subject, '2026-09-12 08:00:00', '2026-09-12 09:00:00', 'CANCELLED');
        $this->makeSession($class, $subject, '2026-09-12 09:00:00', '2026-09-12 10:00:00', 'RESCHEDULED');

        $result = app(AcademicTodaySessionService::class)->forUser($user, Carbon::parse('2026-09-12 10:00:00'));

        $this->assertSame(0, $result['active_total']);
        $this->assertSame(1, $result['cancelled_count']);
        $this->assertSame(1, $result['rescheduled_source_count']);
        $this->assertCount(0, $result['items']);
    }

    public function test_rescheduled_source_vocabulary_does_not_exclude_active_replacement(): void
    {
        [$class, $subject, $user] = $this->fixture();
        $this->makeSession($class, $subject, '2026-09-12 08:00:00', '2026-09-12 09:00:00', 'PLANNED', 'RESCHEDULED');

        $result = app(AcademicTodaySessionService::class)->forUser($user, Carbon::parse('2026-09-12 07:00:00'));

        $this->assertSame(1, $result['active_total']);
        $this->assertSame('UPCOMING', $result['items'][0]['operational_state']);
        $this->assertSame('RESCHEDULED', $result['items'][0]['session_source']);
    }

    public function test_extra_and_ad_hoc_sessions_are_active(): void
    {
        [$class, $subject, $user] = $this->fixture();
        $this->makeSession($class, $subject, '2026-09-12 08:00:00', '2026-09-12 09:00:00', 'PLANNED', 'EXTRA');
        $this->makeSession($class, $subject, '2026-09-12 09:00:00', '2026-09-12 10:00:00', 'COMPLETED', 'AD_HOC');

        $result = app(AcademicTodaySessionService::class)->forUser($user, Carbon::parse('2026-09-12 10:00:00'));

        $this->assertSame(2, $result['active_total']);
        $this->assertSame(['EXTRA', 'AD_HOC'], collect($result['items'])->pluck('session_source')->all());
    }

    public function test_joint_session_is_one_institutional_item_with_deduplicated_classes(): void
    {
        [$class, $subject, $user] = $this->fixture();
        $secondClass = $this->secondClass();
        $session = $this->makeSession($class, $subject, '2026-09-12 08:00:00', '2026-09-12 09:00:00', 'PLANNED');
        ClassSessionGroup::create(['class_session_id' => $session->id, 'class_id' => $class->id, 'scope_role' => 'JOINT_SCOPE']);
        ClassSessionGroup::create(['class_session_id' => $session->id, 'class_id' => $secondClass->id, 'scope_role' => 'JOINT_SCOPE']);

        $result = app(AcademicTodaySessionService::class)->forUser($user, Carbon::parse('2026-09-12 07:00:00'));

        $this->assertSame(1, $result['active_total']);
        $this->assertCount(1, $result['items']);
        $this->assertSame([$class->id, $secondClass->id], collect($result['items'][0]['associated_classes'])->pluck('id')->all());
    }

    public function test_today_boundaries_are_start_inclusive_and_next_day_exclusive(): void
    {
        [$class, $subject, $user] = $this->fixture();
        $this->makeSession($class, $subject, '2026-09-12 00:00:00', '2026-09-12 01:00:00', 'PLANNED');
        $this->makeSession($class, $subject, '2026-09-13 00:00:00', '2026-09-13 01:00:00', 'PLANNED');

        $result = app(AcademicTodaySessionService::class)->forUser($user, Carbon::parse('2026-09-12 12:00:00'));

        $this->assertSame(1, $result['active_total']);
        $this->assertSame('2026-09-12 00:00:00', $result['items'][0]['planned_start_at']->toDateTimeString());
    }

    public function test_completed_keeps_completed_precedence_when_planned_end_is_in_future(): void
    {
        [$class, $subject, $user] = $this->fixture();
        $this->makeSession($class, $subject, '2026-09-12 08:00:00', '2026-09-12 12:00:00', 'COMPLETED');

        $result = app(AcademicTodaySessionService::class)->forUser($user, Carbon::parse('2026-09-12 10:00:00'));

        $this->assertSame('COMPLETED', $result['items'][0]['operational_state']);
        $this->assertSame(1, $result['completed_count']);
    }

    public function test_planned_and_confirmed_share_timestamp_classification_but_keep_raw_status(): void
    {
        [$class, $subject, $user] = $this->fixture();
        $secondClass = $this->secondClass();
        $this->makeSession($class, $subject, Carbon::parse('2026-09-12 08:00:00', 'Asia/Jakarta'), Carbon::parse('2026-09-12 11:00:00', 'Asia/Jakarta'), 'PLANNED');
        $this->makeSession($secondClass, $subject, Carbon::parse('2026-09-12 08:00:00', 'Asia/Jakarta'), Carbon::parse('2026-09-12 11:00:00', 'Asia/Jakarta'), 'CONFIRMED');

        $result = app(AcademicTodaySessionService::class)->forUser($user, Carbon::parse('2026-09-12 09:00:00', 'Asia/Jakarta'));

        $this->assertSame(['PLANNED', 'CONFIRMED'], collect($result['items'])->pluck('raw_session_status')->all());
        $this->assertSame(['IN_PROGRESS', 'IN_PROGRESS'], collect($result['items'])->pluck('operational_state')->all());
    }

    public function test_completed_session_remains_in_active_operational_feed(): void
    {
        [$class, $subject, $user] = $this->fixture();
        $this->makeSession($class, $subject, '2026-09-12 08:00:00', '2026-09-12 09:00:00', 'COMPLETED');

        $result = app(AcademicTodaySessionService::class)->forUser($user, Carbon::parse('2026-09-12 12:00:00'));

        $this->assertSame(1, $result['active_total']);
        $this->assertSame('COMPLETED', $result['items'][0]['operational_state']);
    }

    public function test_cancelled_and_rescheduled_raw_status_take_precedence_over_timestamps(): void
    {
        [$class, $subject, $user] = $this->fixture();
        $this->makeSession($class, $subject, '2026-09-12 10:00:00', '2026-09-12 11:00:00', 'CANCELLED');
        $this->makeSession($class, $subject, '2026-09-12 10:00:00', '2026-09-12 11:00:00', 'RESCHEDULED');

        $result = app(AcademicTodaySessionService::class)->forUser($user, Carbon::parse('2026-09-12 10:30:00'));

        $this->assertSame(0, $result['active_total']);
        $this->assertSame(1, $result['cancelled_count']);
        $this->assertSame(1, $result['rescheduled_source_count']);
    }

    public function test_unknown_raw_status_is_not_silently_treated_as_active(): void
    {
        [$class, $subject, $user] = $this->fixture();
        $this->expectException(QueryException::class);
        $this->makeSession($class, $subject, Carbon::parse('2026-09-12 10:00:00', 'Asia/Jakarta'), Carbon::parse('2026-09-12 11:00:00', 'Asia/Jakarta'), 'UNKNOWN');
    }

    public function test_empty_today_has_zero_counts_and_no_items(): void
    {
        [, , $user] = $this->fixture();

        $result = app(AcademicTodaySessionService::class)->forUser($user, Carbon::parse('2026-09-12 09:00:00'));

        $this->assertSame(0, $result['active_total']);
        $this->assertSame(0, $result['completed_count']);
        $this->assertCount(0, $result['items']);
    }

    public function test_item_contains_canonical_subject_and_class_metadata(): void
    {
        [$class, $subject, $user] = $this->fixture();
        $this->makeSession($class, $subject, '2026-09-12 10:00:00', '2026-09-12 11:00:00', 'PLANNED');

        $item = app(AcademicTodaySessionService::class)->forUser($user, Carbon::parse('2026-09-12 09:00:00'))['items'][0];

        $this->assertSame(['id' => $subject->id, 'name' => $subject->subject_name], $item['subject']);
        $this->assertSame([['id' => $class->id, 'name' => $class->display_name]], $item['associated_classes']);
    }

    public function test_waka_and_super_admin_are_authorized_but_wali_is_not_institution_wide(): void
    {
        [$class, $subject, $waka] = $this->fixture();
        $superAdmin = $this->userWithRole('SUPER_ADMIN');
        $wali = $this->userWithRole('WALI_KELAS');
        $this->makeSession($class, $subject, '2026-09-12 08:00:00', '2026-09-12 09:00:00', 'PLANNED');

        $service = app(AcademicTodaySessionService::class);
        $this->assertSame(1, $service->forUser($waka, Carbon::parse('2026-09-12 07:00:00'))['active_total']);
        $this->assertSame(1, $service->forUser($superAdmin, Carbon::parse('2026-09-12 07:00:00'))['active_total']);
        $this->expectException(AuthorizationException::class);
        $service->forUser($wali, Carbon::parse('2026-09-12 07:00:00'));
    }

    private function fixture(): array
    {
        $unit = OrganizationalUnit::create(['unit_code' => 'UNIT-TODAY', 'unit_name' => 'Today Unit', 'unit_type' => 'SCHOOL']);
        $grade = GradeLevel::create(['organizational_unit_id' => $unit->id, 'level_code' => '1', 'display_name' => 'Tingkat 1', 'sequence_no' => 1]);
        $year = AcademicYear::create(['year_code' => '2026-TODAY', 'display_name' => '2026/2027', 'starts_on' => '2026-07-01', 'ends_on' => '2027-06-30']);
        $semester = Semester::create(['academic_year_id' => $year->id, 'semester_code' => 'ODD-TODAY', 'display_name' => 'Ganjil', 'sequence_no' => 1, 'starts_on' => '2026-07-01', 'ends_on' => '2026-12-31']);
        $class = AcademicClass::create(['class_code' => 'TODAY-A', 'academic_year_id' => $year->id, 'organizational_unit_id' => $unit->id, 'grade_level_id' => $grade->id, 'section_code' => 'A', 'display_name' => 'Kelas Today A']);
        $subject = Subject::create(['subject_code' => 'TODAY-SUBJ', 'subject_name' => 'Today Subject']);
        $teacher = Staff::create(['staff_code' => 'TODAY-TEACHER', 'full_name' => 'Today Teacher']);
        $assignment = TeachingAssignment::create(['assignment_code' => 'TODAY-TA', 'semester_id' => $semester->id, 'class_id' => $class->id, 'subject_id' => $subject->id, 'teacher_staff_id' => $teacher->id, 'effective_from' => '2026-07-01', 'workflow_status' => 'ACTIVE']);
        $waka = $this->userWithRole('WAKA_AKADEMIK');

        return [$class, $subject, $waka, $assignment];
    }

    private function secondClass(): AcademicClass
    {
        $base = AcademicClass::query()->where('class_code', 'TODAY-A')->firstOrFail();
        $class = AcademicClass::create(['class_code' => 'TODAY-B', 'academic_year_id' => $base->academic_year_id, 'organizational_unit_id' => $base->organizational_unit_id, 'grade_level_id' => $base->grade_level_id, 'section_code' => 'B', 'display_name' => 'Kelas Today B']);
        $baseAssignment = TeachingAssignment::query()->where('class_id', $base->id)->firstOrFail();
        TeachingAssignment::create(['assignment_code' => 'TODAY-TA-B', 'semester_id' => $baseAssignment->semester_id, 'class_id' => $class->id, 'subject_id' => $baseAssignment->subject_id, 'teacher_staff_id' => $baseAssignment->teacher_staff_id, 'effective_from' => '2026-07-01', 'workflow_status' => 'ACTIVE']);

        return $class;
    }

    private function makeSession(AcademicClass $class, Subject $subject, Carbon|string $start, Carbon|string $end, string $status, string $source = 'SCHEDULED'): ClassSession
    {
        $assignment = TeachingAssignment::query()->where('class_id', $class->id)->firstOrFail();
        $start = is_string($start) ? Carbon::parse($start, 'Asia/Jakarta') : $start;
        $end = is_string($end) ? Carbon::parse($end, 'Asia/Jakarta') : $end;

        return ClassSession::create(['session_code' => 'TODAY-'.str()->uuid(), 'teaching_assignment_id' => $assignment->id, 'class_id' => $class->id, 'subject_id' => $subject->id, 'planned_start_at' => $start, 'planned_end_at' => $end, 'session_source' => $source, 'participant_scope' => 'FULL_CLASS', 'session_status' => $status]);
    }

    private function userWithRole(string $roleCode): User
    {
        $user = User::factory()->create();
        $role = Role::firstOrCreate(['code' => $roleCode], ['name' => $roleCode]);
        UserRoleAssignment::create(['user_id' => $user->id, 'role_id' => $role->id]);

        return $user;
    }
}
