<?php

namespace Tests\Feature\Academic;

use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Models\ClassHomeroomAssignment;
use App\Domains\Academic\Models\ClassSession;
use App\Domains\Academic\Models\ClassSessionGroup;
use App\Domains\Academic\Models\GradeLevel;
use App\Domains\Academic\Models\Semester;
use App\Domains\Academic\Models\SessionStudentParticipant;
use App\Domains\Academic\Models\SessionTeacherParticipation;
use App\Domains\Academic\Models\StudentAttendance;
use App\Domains\Academic\Models\StudentClassEnrollment;
use App\Domains\Academic\Models\Subject;
use App\Domains\Academic\Models\TeachingAssignment;
use App\Domains\Academic\Services\AcademicDashboardExportService;
use App\Domains\Academic\Services\AcademicRoleDashboardService;
use App\Models\User;
use App\Shared\Core\Models\AcademicYear;
use App\Shared\Core\Models\OrganizationalUnit;
use App\Shared\Core\Models\Staff;
use App\Shared\Core\Models\Student;
use App\Shared\Core\Models\StudentStatusHistory;
use App\Shared\Platform\Authorization\Models\Permission;
use App\Shared\Platform\Authorization\Models\Role;
use App\Shared\Platform\Authorization\Models\UserRoleAssignment;
use App\Shared\Platform\Authorization\Models\UserStaffLink;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AcademicRoleDashboardServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_wali_sees_only_assigned_class_and_waka_sees_all_classes(): void
    {
        [$first, $second, $wali, $waka] = $this->fixture(true);
        $service = app(AcademicRoleDashboardService::class);
        $from = Carbon::parse('2026-07-01');
        $to = Carbon::parse('2026-07-31')->endOfDay();

        $this->assertCount(1, $service->forUser($wali, $from, $to)['classes']);
        $this->assertSame($first->id, $service->forUser($wali, $from, $to)['classes']->first()['class']->id);
        $this->assertCount(2, $service->forUser($waka, $from, $to)['classes']);
    }

    public function test_waka_dashboard_excludes_classes_from_pilot_academic_year(): void
    {
        [, , , $waka] = $this->fixture(true);

        $classes = app(AcademicRoleDashboardService::class)
            ->forUser($waka, Carbon::parse('2026-07-01'), Carbon::parse('2026-07-31')->endOfDay())['classes'];

        $this->assertCount(2, $classes);
        $this->assertTrue($classes->every(fn (array $item) => ! str_ends_with($item['class']->academicYear->year_code, '-PILOT')));
    }

    public function test_wali_dashboard_excludes_assigned_class_from_pilot_academic_year(): void
    {
        [$first, , $wali] = $this->fixture(true);
        $pilotClass = AcademicClass::query()->where('class_code', 'DASH-PILOT')->firstOrFail();
        $waliStaff = UserStaffLink::query()->where('user_id', $wali->id)->value('staff_id');
        ClassHomeroomAssignment::create([
            'class_id' => $pilotClass->id,
            'staff_id' => $waliStaff,
            'effective_from' => '2026-07-01',
            'status' => 'ACTIVE',
        ]);

        $classes = app(AcademicRoleDashboardService::class)
            ->forUser($wali, Carbon::parse('2026-07-01'), Carbon::parse('2026-07-31')->endOfDay())['classes'];

        $this->assertSame([$first->id], $classes->pluck('class.id')->all());
    }

    public function test_waka_dashboard_includes_official_class_without_direct_session(): void
    {
        [$first, , , $waka] = $this->fixture();
        AcademicClass::create([
            'class_code' => 'DASH-C',
            'academic_year_id' => $first->academic_year_id,
            'organizational_unit_id' => $first->organizational_unit_id,
            'grade_level_id' => $first->grade_level_id,
            'section_code' => 'C',
            'display_name' => 'Kelas C',
        ]);

        $classes = app(AcademicRoleDashboardService::class)
            ->forUser($waka, Carbon::parse('2026-07-01'), Carbon::parse('2026-07-31')->endOfDay())['classes'];

        $this->assertCount(3, $classes);
        $this->assertSame('Kelas C', $classes->last()['class']->display_name);
    }

    public function test_grade_level_dashboard_aggregates_classes_by_canonical_grade_level_id(): void
    {
        [$first, $second, , $waka] = $this->fixture();
        $summary = app(AcademicRoleDashboardService::class)->forUser($waka, Carbon::parse('2026-07-01'), Carbon::parse('2026-07-31')->endOfDay())['grade_levels'];

        $this->assertCount(1, $summary);
        $this->assertSame($first->grade_level_id, $summary->first()['grade_level_id']);
        $this->assertSame(2, $summary->first()['class_count']);
        $this->assertEqualsCanonicalizing([$first->id, $second->id], $summary->first()['class_ids']->all());
    }

    public function test_overview_counts_active_students_and_academic_teachers_without_fabricating_attendance_rate(): void
    {
        [$first, , , $waka] = $this->fixture();
        $student = Student::create(['student_code' => 'DASH-STUDENT', 'full_name' => 'Dashboard Student']);
        StudentStatusHistory::create(['student_id' => $student->id, 'status' => 'ACTIVE', 'effective_from' => '2026-07-01']);
        StudentClassEnrollment::create(['student_id' => $student->id, 'class_id' => $first->id, 'effective_from' => '2026-07-01', 'status' => 'ACTIVE']);

        $overview = app(AcademicRoleDashboardService::class)
            ->forUser($waka, Carbon::parse('2026-07-01'), Carbon::parse('2026-07-31')->endOfDay())['overview'];

        $this->assertSame(1, $overview['active_student_count']);
        $this->assertSame(1, $overview['active_teacher_count']);
        $this->assertSame(2, $overview['active_class_count']);
        $this->assertNull($overview['attendance']['physical_presence_rate']);
        $this->assertNull($overview['attendance']['completeness_rate']);
    }

    public function test_attendance_trend_uses_daily_sum_numerator_and_denominator(): void
    {
        [$first, , , $waka] = $this->fixture();
        $student = Student::create(['student_code' => 'DASH-TREND', 'full_name' => 'Trend Student']);
        $session = ClassSession::where('class_id', $first->id)->firstOrFail();
        $participant = SessionStudentParticipant::create(['class_session_id' => $session->id, 'student_id' => $student->id, 'participant_basis' => 'ROSTER']);
        StudentAttendance::create(['session_student_participant_id' => $participant->id, 'attendance_status' => 'PRESENT', 'workflow_status' => 'VALIDATED', 'entered_by' => $waka->id, 'entered_at' => '2026-07-10 09:00:00', 'updated_by' => $waka->id, 'updated_at' => '2026-07-10 09:00:00']);

        $trend = app(AcademicRoleDashboardService::class)
            ->forUser($waka, Carbon::parse('2026-07-01'), Carbon::parse('2026-07-31')->endOfDay(), null, 30)['attendance_trend'];

        $this->assertCount(30, $trend);
        $trendDay = collect($trend)->firstWhere('date', '2026-07-10');
        $this->assertSame(1, $trendDay['eligible_opportunities']);
        $this->assertSame(1, $trendDay['resolved_opportunities']);
        $this->assertSame(100.0, $trendDay['physical_presence_rate']);
        $this->assertSame(100.0, $trendDay['completeness_rate']);
        $emptyDay = collect($trend)->firstWhere('date', '2026-07-02');
        $this->assertNull($emptyDay['physical_presence_rate']);
    }

    public function test_overview_physical_presence_uses_canonical_eligible_denominator(): void
    {
        [$first, , , $waka] = $this->fixture();
        $session = ClassSession::where('class_id', $first->id)->firstOrFail();
        $this->populateSession($session, $waka, 76, 4, 20);

        $overview = app(AcademicRoleDashboardService::class)
            ->forUser($waka, Carbon::parse('2026-07-01'), Carbon::parse('2026-07-31')->endOfDay())['overview']['attendance'];

        $this->assertSame(100, $overview['eligible_opportunities']);
        $this->assertSame(80, $overview['resolved_opportunities']);
        $this->assertSame(76.0, $overview['physical_presence_rate']);
        $this->assertSame(80.0, $overview['completeness_rate']);
        $this->assertSame(76.0, $overview['attendance_rate']);
    }

    public function test_grade_level_aggregation_is_count_weighted_over_resolved_opportunities(): void
    {
        [$first, $second, , $waka] = $this->fixture();
        $firstSession = ClassSession::where('class_id', $first->id)->firstOrFail();
        $secondSession = ClassSession::where('class_id', $second->id)->firstOrFail();
        $this->populateSession($firstSession, $waka, 45, 5, 0);
        $this->populateSession($secondSession, $waka, 25, 5, 20);

        $gradeAttendance = app(AcademicRoleDashboardService::class)
            ->forUser($waka, Carbon::parse('2026-07-01'), Carbon::parse('2026-07-31')->endOfDay())['grade_levels']
            ->first()['attendance'];

        $this->assertSame(100, $gradeAttendance['eligible_opportunities']);
        $this->assertSame(80, $gradeAttendance['resolved_opportunities']);
        $this->assertSame(70.0, $gradeAttendance['physical_presence_rate']);
        $this->assertSame(80.0, $gradeAttendance['completeness_rate']);
    }

    public function test_attendance_trend_uses_canonical_eligible_denominator(): void
    {
        [$first, , , $waka] = $this->fixture();
        $session = ClassSession::where('class_id', $first->id)->firstOrFail();
        $this->populateSession($session, $waka, 9, 1, 10);

        Carbon::setTestNow('2026-07-31 12:00:00');
        try {
            $trend = app(AcademicRoleDashboardService::class)
                ->forUser($waka, Carbon::parse('2026-07-01'), Carbon::parse('2026-07-31')->endOfDay(), null, 30)['attendance_trend'];
        } finally {
            Carbon::setTestNow();
        }

        $trendDay = collect($trend)->firstWhere('date', '2026-07-10');
        $this->assertSame(20, $trendDay['eligible_opportunities']);
        $this->assertSame(10, $trendDay['resolved_opportunities']);
        $this->assertSame(45.0, $trendDay['physical_presence_rate']);
        $this->assertSame(50.0, $trendDay['completeness_rate']);
    }

    public function test_dashboard_rates_are_null_for_physical_presence_when_no_opportunity_is_resolved(): void
    {
        [$first, , , $waka] = $this->fixture();
        $session = ClassSession::where('class_id', $first->id)->firstOrFail();
        $this->participant($session, 0);

        $attendance = app(AcademicRoleDashboardService::class)
            ->forUser($waka, Carbon::parse('2026-07-01'), Carbon::parse('2026-07-31')->endOfDay())['overview']['attendance'];

        $this->assertSame(1, $attendance['eligible_opportunities']);
        $this->assertSame(0, $attendance['resolved_opportunities']);
        $this->assertSame(0.0, $attendance['physical_presence_rate']);
        $this->assertSame(0.0, $attendance['completeness_rate']);
    }

    public function test_today_attendance_distinguishes_finalized_due_and_missing_sessions(): void
    {
        [$first, $second, , $waka] = $this->fixture();
        $student = Student::create(['student_code' => 'DASH-TODAY', 'full_name' => 'Today Student']);
        $session = ClassSession::where('class_id', $first->id)->firstOrFail();
        $participant = SessionStudentParticipant::create(['class_session_id' => $session->id, 'student_id' => $student->id, 'participant_basis' => 'ROSTER']);
        StudentAttendance::create(['session_student_participant_id' => $participant->id, 'attendance_status' => 'PRESENT', 'workflow_status' => 'VALIDATED', 'entered_by' => $waka->id, 'entered_at' => '2026-07-10 09:00:00', 'finalized_by' => $waka->id, 'finalized_at' => '2026-07-10 09:05:00', 'updated_by' => $waka->id, 'updated_at' => '2026-07-10 09:05:00']);
        $this->assertNotNull($second);

        Carbon::setTestNow(Carbon::parse('2026-07-10 12:00:00', 'Asia/Jakarta'));
        try {
            $today = app(AcademicRoleDashboardService::class)
                ->forUser($waka, Carbon::parse('2026-07-01', 'Asia/Jakarta'), Carbon::parse('2026-07-31 23:59:59', 'Asia/Jakarta'))['today_attendance'];
        } finally {
            Carbon::setTestNow();
        }

        $this->assertSame(2, $today['due_sessions']);
        $this->assertSame(1, $today['finalized_sessions']);
        $this->assertSame(1, $today['due_not_finalized_sessions']);
        $this->assertSame(0, $today['in_progress_sessions']);
        $this->assertSame(0, $today['upcoming_sessions']);
        $this->assertSame(50.0, $today['completion_rate']);
    }

    public function test_dashboard_route_returns_authorized_academic_view(): void
    {
        [$first, , $wali] = $this->fixture();

        $this->actingAs($wali)
            ->get('/academic/dashboard?from=2026-07-01&to=2026-07-31')
            ->assertOk()
            ->assertSee('Ringkasan Akademik')
            ->assertSee($first->display_name)
            ->assertSee('aria-label="Beranda"', false)
            ->assertSee('aria-current="page"', false)
            ->assertSee('aria-controls="waka-nav"', false)
            ->assertSee('aria-label="Laporan Juli 2026"', false)
            ->assertSee('academic/dashboard?from=2026-07-01&amp;to=2026-07-31', false)
            ->assertSee("event.key !== 'Escape'", false)
            ->assertDontSee('Peran: Wali Kelas')
            ->assertDontSee('Perlu Perhatian Kehadiran')
            ->assertSee('Belum ada data wajib yang dapat dihitung');
    }

    public function test_waka_dashboard_hides_ai_assistant_when_feature_gate_is_off(): void
    {
        [, , , $waka] = $this->fixture();
        config()->set('academic.ai.assistant_enabled', false);

        $this->actingAs($waka)
            ->get(route('academic.dashboard', ['from' => '2026-07-01', 'to' => '2026-07-31']))
            ->assertOk()
            ->assertDontSee('Asisten Akademik')
            ->assertDontSee('data-ai-form', false);
    }

    public function test_waka_dashboard_renders_ai_assistant_as_read_only_same_origin_question_form_when_enabled(): void
    {
        [, , , $waka] = $this->fixture();
        config()->set('academic.ai.assistant_enabled', true);

        $this->actingAs($waka)
            ->get(route('academic.dashboard', ['from' => '2026-07-01', 'to' => '2026-07-31']))
            ->assertOk()
            ->assertSee('Asisten Akademik')
            ->assertSee('Baca saja')
            ->assertSee('action="'.route('academic.ai-assistant.query').'"', false)
            ->assertSee('name="question"', false)
            ->assertSee('maxlength="4000"', false)
            ->assertSee('Catatan data:', false)
            ->assertSee('fetch(endpoint', false)
            ->assertSee('X-CSRF-TOKEN', false)
            ->assertDontSee('localStorage', false)
            ->assertDontSee('sessionStorage', false)
            ->assertDontSee('openai.com', false)
            ->assertDontSee('toolsInvoked', false);
    }

    public function test_dashboard_presents_resolved_zero_as_canonical_zero_presence_and_zero_completeness(): void
    {
        [$first, , , $waka] = $this->fixture();
        $session = ClassSession::where('class_id', $first->id)->firstOrFail();
        $this->participant($session, 0);

        $response = $this->actingAs($waka)->get(route('academic.dashboard', ['from' => '2026-07-01', 'to' => '2026-07-31']));

        $response->assertOk()
            ->assertSee('0%')
            ->assertSee('0 dari 1 data wajib sudah tervalidasi · 1 belum tervalidasi')
            ->assertSee('<span class="waka-kpi-label">Kehadiran fisik</span><span class="waka-kpi-icon" aria-hidden="true">✓</span></div><strong class="waka-kpi-value">0%', false);
    }

    public function test_waka_dashboard_exposes_only_supported_quick_actions(): void
    {
        [, , , $waka] = $this->fixture();

        $this->actingAs($waka)
            ->get(route('academic.dashboard', ['from' => '2026-07-01', 'to' => '2026-07-31']))
            ->assertOk()
            ->assertSee('Aksi Cepat')
            ->assertSee('Kontrol kehadiran')
            ->assertSee(route('academic.attendance.exceptions'), false)
            ->assertSee('Laporan bulanan')
            ->assertSee(route('academic.monthly-reports.index'), false)
            ->assertSee('Unduh rekap CSV')
            ->assertSee(str_replace('&', '&amp;', route('academic.dashboard.export', ['from' => '2026-07-01', 'to' => '2026-07-31'])), false)
            ->assertDontSee('Pengingat &amp; Tindak Lanjut')
            ->assertDontSee('Agenda Akademik');
    }

    public function test_dashboard_distinguishes_no_eligible_data_from_resolved_zero(): void
    {
        [, , , $waka] = $this->fixture();

        $response = $this->actingAs($waka)->get(route('academic.dashboard', ['from' => '2026-07-01', 'to' => '2026-07-31']));

        $response->assertOk()
            ->assertSee('Belum ada data wajib yang dapat dihitung')
            ->assertDontSee('Belum ada data kehadiran tervalidasi');
    }

    public function test_dashboard_explains_physical_and_completeness_denominators(): void
    {
        [$first, , , $waka] = $this->fixture();
        $session = ClassSession::where('class_id', $first->id)->firstOrFail();
        $this->populateSession($session, $waka, 76, 4, 20);

        $this->actingAs($waka)
            ->get(route('academic.dashboard', ['from' => '2026-07-01', 'to' => '2026-07-31']))
            ->assertOk()
            ->assertSee('76%')
            ->assertSee('Hadir + terlambat dari 80 data kehadiran tervalidasi')
            ->assertSee('80 dari 100 data wajib sudah tervalidasi · 20 belum tervalidasi');
    }

    public function test_dashboard_class_card_uses_attendance_record_wording(): void
    {
        [$first, , , $waka] = $this->fixture();
        $session = ClassSession::where('class_id', $first->id)->firstOrFail();
        $this->populateSession($session, $waka, 20, 0, 1);

        $this->actingAs($waka)
            ->get(route('academic.dashboard', ['from' => '2026-07-01', 'to' => '2026-07-31']))
            ->assertOk()
            ->assertSee('20 dari 21 tervalidasi')
            ->assertSee('1 belum tervalidasi')
            ->assertDontSee('20/21 sesi selesai');
    }

    public function test_dashboard_trend_displays_physical_and_completeness_together(): void
    {
        [$first, , , $waka] = $this->fixture();
        $session = ClassSession::where('class_id', $first->id)->firstOrFail();
        $this->populateSession($session, $waka, 9, 1, 10);

        Carbon::setTestNow('2026-07-31 12:00:00');
        try {
            $this->actingAs($waka)
                ->get(route('academic.dashboard', ['from' => '2026-07-01', 'to' => '2026-07-31', 'trend_days' => 30]))
                ->assertOk()
                ->assertSee('Kehadiran')
                ->assertSee('45%')
                ->assertSee('Kelengkapan')
                ->assertSee('50%')
                ->assertSee('10 dari 20 data tervalidasi · 10 belum tervalidasi');
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_dashboard_trend_presents_zero_presence_when_eligible_data_is_unresolved(): void
    {
        [$first, , , $waka] = $this->fixture();
        $session = ClassSession::where('class_id', $first->id)->firstOrFail();
        $this->participant($session, 0);

        Carbon::setTestNow('2026-07-31 12:00:00');
        try {
            $this->actingAs($waka)
                ->get(route('academic.dashboard', ['from' => '2026-07-01', 'to' => '2026-07-31', 'trend_days' => 30]))
                ->assertOk()
                ->assertSee('Kehadiran')
                ->assertSee('0%')
                ->assertSee('Kelengkapan')
                ->assertSee('0%')
                ->assertSee('0 dari 1 data tervalidasi · 1 belum tervalidasi');
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_wali_dashboard_exposes_sessions_for_attendance_entry(): void
    {
        [$first, , $wali] = $this->fixture();

        $dashboard = app(AcademicRoleDashboardService::class)
            ->forUser($wali, Carbon::parse('2026-07-01'), Carbon::parse('2026-07-31')->endOfDay());

        $this->assertCount(1, $dashboard['attendance_sessions']);
        $this->assertSame($first->id, $dashboard['attendance_sessions']->first()->class_id);
        $this->assertSame('Belum diisi', $dashboard['attendance_sessions']->first()->attendance_label);
        $this->assertSame('Isi kehadiran', $dashboard['attendance_sessions']->first()->attendance_action);

        $this->actingAs($wali)
            ->get(route('academic.dashboard', ['from' => '2026-07-01', 'to' => '2026-07-31']))
            ->assertOk()
            ->assertSee('Pengisian Kehadiran')
            ->assertSee('Belum diisi')
            ->assertSee('Belum lengkap')
            ->assertSee('Sudah disahkan')
            ->assertSee('Dashboard')
            ->assertSee('Akademik')
            ->assertSee('Kehadiran fisik')
            ->assertSee('Kelengkapan data')
            ->assertSee('Santri aktif')
            ->assertSee('Guru aktif')
            ->assertSee('Jumat, 10/07/2026')
            ->assertSee('01 Jul 2026–31 Jul 2026 · Rentang manual')
            ->assertSee('Status Operasional')
            ->assertSee('Aksi Cepat')
            ->assertSee('Laporan bulanan')
            ->assertSee('Unduh rekap CSV')
            ->assertDontSee('Pengingat &amp; Tindak Lanjut')
            ->assertDontSee('Agenda Akademik')
            ->assertDontSee('Kontrol kehadiran')
            ->assertSee('Pengesahan sesi')
            ->assertSee('Belum ada data kehadiran guru')
            ->assertSee('penugasan tercatat')
            ->assertSee('role="progressbar"', false)
            ->assertSee('aria-label="Kelengkapan kehadiran Kelas A"', false)
            ->assertSee('aria-label="Tren kehadiran santri 18/07/2026"', false)
            ->assertSee('id="pemantauan"', false)
            ->assertSee('id="tren-kehadiran"', false)
            ->assertSee('.waka-main-grid>div>section:first-child .waka-card-heading>.waka-link{display:none}', false)
            ->assertSee(route('academic.attendance.show', $dashboard['attendance_sessions']->first()), false);
    }

    public function test_wali_joint_dashboard_partitions_trend_and_operational_roster_by_authorized_class(): void
    {
        [$first, $second, $wali, $waka] = $this->fixture();
        $session = ClassSession::where('class_id', $first->id)->firstOrFail();
        ClassSessionGroup::create(['class_session_id' => $session->id, 'class_id' => $first->id, 'scope_role' => 'JOINT_SCOPE']);
        ClassSessionGroup::create(['class_session_id' => $session->id, 'class_id' => $second->id, 'scope_role' => 'JOINT_SCOPE']);
        $firstStudent = Student::create(['student_code' => 'DASH-JOINT-A', 'full_name' => 'Joint A']);
        $secondStudent = Student::create(['student_code' => 'DASH-JOINT-B', 'full_name' => 'Joint B']);
        foreach ([[$firstStudent, $first], [$secondStudent, $second]] as [$student, $class]) {
            StudentClassEnrollment::create(['student_id' => $student->id, 'class_id' => $class->id, 'effective_from' => '2026-07-01', 'status' => 'ACTIVE']);
        }
        foreach ([[$firstStudent, 'PRESENT'], [$secondStudent, 'ABSENT']] as [$student, $status]) {
            $participant = SessionStudentParticipant::create(['class_session_id' => $session->id, 'student_id' => $student->id, 'participant_basis' => 'CLASS_ENROLLMENT']);
            StudentAttendance::create(['session_student_participant_id' => $participant->id, 'attendance_status' => $status, 'workflow_status' => 'VALIDATED', 'entered_by' => $waka->id, 'entered_at' => now(), 'updated_by' => $waka->id, 'updated_at' => now()]);
        }

        $from = Carbon::parse('2026-07-01');
        $to = Carbon::parse('2026-07-31')->endOfDay();
        $waliDashboard = app(AcademicRoleDashboardService::class)->forUser($wali, $from, $to, null, 30);
        $waliTrend = collect($waliDashboard['attendance_trend'])->firstWhere('date', '2026-07-10');

        $this->assertSame(1, $waliDashboard['attendance_sessions']->first()->student_participants_count);
        $this->assertSame(1, $waliTrend['eligible_opportunities']);
        $this->assertSame(100.0, $waliTrend['physical_presence_rate']);

        $wakaDashboard = app(AcademicRoleDashboardService::class)->forUser($waka, $from, $to, null, 30);
        $wakaTrend = collect($wakaDashboard['attendance_trend'])->firstWhere('date', '2026-07-10');
        $this->assertSame(2, $wakaTrend['eligible_opportunities']);
        $this->assertSame(50.0, $wakaTrend['physical_presence_rate']);
    }

    public function test_dashboard_accepts_supported_trend_window(): void
    {
        [, , , $waka] = $this->fixture();

        $this->actingAs($waka)
            ->get('/academic/dashboard?from=2026-07-01&to=2026-07-31&trend_days=7')
            ->assertOk()
            ->assertSee('7 hari')
            ->assertSee('14 hari')
            ->assertSee('30 hari');
    }

    public function test_dashboard_accepts_academic_year_month_selection(): void
    {
        [, , , $waka] = $this->fixture();
        Carbon::setTestNow('2026-07-09 12:00:00');
        try {
            $this->actingAs($waka)
                ->get('/academic/dashboard?month=2026-07')
                ->assertOk()
                ->assertSee('Akademik')
                ->assertSee('Juli 2026')
                ->assertSee('Pilih bulan untuk memakai satu bulan penuh; kosongkan untuk rentang tanggal manual.')
                ->assertSee('Bulan penuh')
                ->assertSee('31 Jul 2026');
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_dashboard_month_selection_overrides_stale_date_range(): void
    {
        [, , , $waka] = $this->fixture();
        Carbon::setTestNow('2026-07-09 12:00:00');
        try {
            $this->actingAs($waka)
                ->get('/academic/dashboard?month=2026-08&from=2026-07-01&to=2026-07-31')
                ->assertRedirect('/academic/dashboard?month=2026-08');

            $this->actingAs($waka)
                ->get('/academic/dashboard?month=2026-08')
                ->assertOk()
                ->assertSee('Agustus 2026')
                ->assertSee('31 Agu 2026')
                ->assertDontSee('Periode: 01 Jul 2026–31 Jul 2026');
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_dashboard_export_uses_month_over_conflicting_date_filters(): void
    {
        [, , , $waka] = $this->fixture();
        $export = \Mockery::mock(AcademicDashboardExportService::class);
        $export->shouldReceive('csv')
            ->once()
            ->withArgs(function (User $actor, Carbon $from, Carbon $to, ?Semester $semester): bool {
                return $from->toDateString() === '2026-07-01'
                    && $to->toDateString() === '2026-07-31'
                    && $semester === null;
            })
            ->andReturn("role,class\nWAKA_AKADEMIK,Kelas A\n");
        $this->app->instance(AcademicDashboardExportService::class, $export);

        Carbon::setTestNow('2026-07-09 12:00:00');
        try {
            $this->actingAs($waka)
                ->get(route('academic.dashboard.export', ['month' => '2026-07', 'from' => '2026-09-01', 'to' => '2026-09-30']))
                ->assertDownload('academic-dashboard.csv');
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_july_dashboard_uses_live_transaction_sources(): void
    {
        [, , , $waka] = $this->fixture();

        $dashboard = app(AcademicRoleDashboardService::class)
            ->forUser($waka, Carbon::parse('2026-07-01'), Carbon::parse('2026-07-31')->endOfDay());

        $this->assertSame('daily_transactions', $dashboard['attendance_trend_source']);
        $this->assertSame('live_sessions', $dashboard['attendance_status_source']);
        $this->assertCount(14, $dashboard['attendance_trend']);
    }

    public function test_application_timezone_matches_academic_operating_timezone(): void
    {
        $this->assertSame('Asia/Jakarta', config('app.timezone'));
    }

    public function test_live_attendance_metrics_exclude_sessions_that_have_not_ended(): void
    {
        [$first, , , $waka, $assignment] = $this->fixture();
        $student = Student::create(['student_code' => 'DASH-DUE', 'full_name' => 'Due Student']);
        StudentStatusHistory::create(['student_id' => $student->id, 'status' => 'ACTIVE', 'effective_from' => '2026-07-01']);
        StudentClassEnrollment::create(['student_id' => $student->id, 'class_id' => $first->id, 'effective_from' => '2026-07-01', 'status' => 'ACTIVE']);
        $pastSession = ClassSession::where('class_id', $first->id)->firstOrFail();
        $pastParticipant = SessionStudentParticipant::create(['class_session_id' => $pastSession->id, 'student_id' => $student->id, 'participant_basis' => 'ROSTER']);
        StudentAttendance::create(['session_student_participant_id' => $pastParticipant->id, 'attendance_status' => 'PRESENT', 'workflow_status' => 'VALIDATED', 'entered_by' => $waka->id, 'entered_at' => '2026-07-10 09:00:00', 'finalized_by' => $waka->id, 'finalized_at' => '2026-07-10 09:05:00', 'updated_by' => $waka->id, 'updated_at' => '2026-07-10 09:05:00']);
        ClassSession::create(['session_code' => 'DASH-FUTURE', 'teaching_assignment_id' => $assignment->id, 'class_id' => $first->id, 'subject_id' => $pastSession->subject_id, 'planned_start_at' => Carbon::parse('2026-07-11 08:00:00', 'Asia/Jakarta')->utc(), 'planned_end_at' => Carbon::parse('2026-07-11 09:00:00', 'Asia/Jakarta')->utc(), 'session_source' => 'SCHEDULED', 'participant_scope' => 'FULL_CLASS', 'session_status' => 'PLANNED']);

        $pastSession->update([
            'planned_start_at' => Carbon::parse('2026-07-10 08:00:00', 'Asia/Jakarta')->utc(),
            'planned_end_at' => Carbon::parse('2026-07-10 09:00:00', 'Asia/Jakarta')->utc(),
        ]);
        Carbon::setTestNow(Carbon::parse('2026-07-10 12:00:00', 'Asia/Jakarta'));
        try {
            $dashboard = app(AcademicRoleDashboardService::class)
                ->forUser($waka, Carbon::parse('2026-07-01', 'Asia/Jakarta'), Carbon::parse('2026-07-31 23:59:59', 'Asia/Jakarta'));
        } finally {
            Carbon::setTestNow();
        }

        $firstClass = $dashboard['classes']->firstWhere('class.id', $first->id);
        $this->assertSame(1, $firstClass['attendance']['eligible_opportunities']);
        $this->assertSame(1, $firstClass['attendance']['resolved_opportunities']);
        $this->assertSame(1, $dashboard['today_attendance']['upcoming_sessions']);
    }

    public function test_wali_live_status_includes_session_reaching_class_through_scope_group(): void
    {
        [$first, $second, $wali] = $this->fixture();
        $assignment = TeachingAssignment::where('class_id', $second->id)->firstOrFail();
        $session = ClassSession::create(['session_code' => 'DASH-JOINT-WALI', 'teaching_assignment_id' => $assignment->id, 'class_id' => $second->id, 'subject_id' => $assignment->subject_id, 'planned_start_at' => Carbon::parse('2026-07-10 10:00:00', 'Asia/Jakarta')->utc(), 'planned_end_at' => Carbon::parse('2026-07-10 11:00:00', 'Asia/Jakarta')->utc(), 'session_source' => 'SCHEDULED', 'participant_scope' => 'FULL_CLASS', 'session_status' => 'COMPLETED']);
        ClassSessionGroup::create(['class_session_id' => $session->id, 'class_id' => $first->id, 'scope_role' => 'JOINT_SCOPE']);

        Carbon::setTestNow(Carbon::parse('2026-07-10 12:00:00', 'Asia/Jakarta'));
        try {
            $dashboard = app(AcademicRoleDashboardService::class)
                ->forUser($wali, Carbon::parse('2026-07-01', 'Asia/Jakarta'), Carbon::parse('2026-07-31 23:59:59', 'Asia/Jakarta'));
        } finally {
            Carbon::setTestNow();
        }

        $this->assertSame(2, $dashboard['today_attendance']['due_sessions']);
    }

    public function test_dashboard_reports_live_teacher_attendance_for_completed_sessions(): void
    {
        [$first, , , $waka] = $this->fixture();
        $session = ClassSession::where('class_id', $first->id)->firstOrFail();
        SessionTeacherParticipation::create(['class_session_id' => $session->id, 'teacher_staff_id' => $session->teachingAssignment->teacher_staff_id, 'role' => 'PRIMARY', 'obligation_type' => 'TEACHING_ASSIGNMENT', 'attendance_status' => 'PRESENT']);

        Carbon::setTestNow('2026-07-10 12:00:00');
        try {
            $dashboard = app(AcademicRoleDashboardService::class)
                ->forUser($waka, Carbon::parse('2026-07-01'), Carbon::parse('2026-07-31')->endOfDay());
        } finally {
            Carbon::setTestNow();
        }

        $this->assertSame(1, $dashboard['teacher_attendance']['eligible_participations']);
        $this->assertSame(1, $dashboard['teacher_attendance']['present']);
        $this->assertSame(100.0, $dashboard['teacher_attendance']['presence_rate']);
        $this->assertSame(100.0, $dashboard['teacher_attendance']['completion_rate']);
    }

    public function test_dashboard_two_class_fixture_stays_within_query_count_baseline(): void
    {
        [, , , $waka] = $this->fixture();
        $queries = 0;
        DB::listen(static function () use (&$queries): void {
            $queries++;
        });

        app(AcademicRoleDashboardService::class)->forUser($waka, Carbon::parse('2026-07-01'), Carbon::parse('2026-07-31')->endOfDay());

        $this->assertLessThanOrEqual(50, $queries);
    }

    public function test_super_admin_can_view_all_academic_classes(): void
    {
        [$first, $second] = $this->fixture();
        $superAdmin = User::factory()->create();
        $role = Role::create(['code' => 'SUPER_ADMIN', 'name' => 'Super Admin']);
        UserRoleAssignment::create(['user_id' => $superAdmin->id, 'role_id' => $role->id]);

        $dashboard = app(AcademicRoleDashboardService::class)->forUser($superAdmin, Carbon::parse('2026-07-01'), Carbon::parse('2026-07-31')->endOfDay());

        $this->assertSame('SUPER_ADMIN', $dashboard['role']);
        $this->assertEqualsCanonicalizing([$first->id, $second->id], $dashboard['classes']->pluck('class.id')->all());
    }

    public function test_unrelated_role_is_denied(): void
    {
        $fixture = $this->fixture();
        $first = $fixture[0];
        $guru = User::factory()->create();
        $role = Role::create(['code' => 'GURU', 'name' => 'Guru']);
        UserRoleAssignment::create(['user_id' => $guru->id, 'role_id' => $role->id]);
        $this->expectException(AuthorizationException::class);
        app(AcademicRoleDashboardService::class)->forUser($guru, Carbon::parse('2026-07-01'), Carbon::parse('2026-07-31'));
    }

    public function test_wali_scope_ends_exclusively_at_effective_until(): void
    {
        [$first, , $wali] = $this->fixture();
        $assignment = ClassHomeroomAssignment::where('class_id', $first->id)->firstOrFail();
        $assignment->update(['effective_until' => '2026-07-15']);

        $classes = app(AcademicRoleDashboardService::class)->forUser($wali, Carbon::parse('2026-07-15'), Carbon::parse('2026-07-31'))['classes'];

        $this->assertCount(0, $classes);
    }

    public function test_wali_role_is_evaluated_at_requested_period_start(): void
    {
        [, , $wali] = $this->fixture();
        UserRoleAssignment::where('user_id', $wali->id)->update(['effective_until' => '2026-07-15']);

        $this->expectException(AuthorizationException::class);
        app(AcademicRoleDashboardService::class)->forUser($wali, Carbon::parse('2026-07-16'), Carbon::parse('2026-07-31'));
    }

    public function test_export_requires_explicit_permission_and_reuses_dashboard_metrics(): void
    {
        [$first, , $wali] = $this->fixture();
        $role = UserRoleAssignment::where('user_id', $wali->id)->firstOrFail()->role;
        $permission = Permission::create(['code' => 'academic.dashboard.export', 'name' => 'Export Academic dashboard']);
        $role->permissions()->attach($permission);

        $csv = app(AcademicDashboardExportService::class)->csv($wali, Carbon::parse('2026-07-01'), Carbon::parse('2026-07-31')->endOfDay());

        $this->assertStringContainsString('attendance_completeness_pct', $csv);
        $this->assertStringContainsString($first->display_name, $csv);
        $semesterCsv = app(AcademicDashboardExportService::class)->csv($wali, Carbon::parse('2026-07-01'), Carbon::parse('2026-07-31')->endOfDay(), Semester::firstOrFail());
        $this->assertStringContainsString('Ganjil', $semesterCsv);
        $this->assertStringContainsString($first->display_name, $semesterCsv);
    }

    public function test_export_without_permission_is_denied(): void
    {
        [, , $wali] = $this->fixture();
        $this->expectException(AuthorizationException::class);
        app(AcademicDashboardExportService::class)->csv($wali, Carbon::parse('2026-07-01'), Carbon::parse('2026-07-31')->endOfDay());
    }

    private function fixture(bool $withPilotClass = false): array
    {
        $unit = OrganizationalUnit::create(['unit_code' => 'UNIT-DASH', 'unit_name' => 'Dashboard Unit', 'unit_type' => 'SCHOOL']);
        $level = GradeLevel::create(['organizational_unit_id' => $unit->id, 'level_code' => '1', 'display_name' => 'Tingkat 1', 'sequence_no' => 1]);
        $year = AcademicYear::create(['year_code' => '2026-DASH', 'display_name' => '2026/2027', 'starts_on' => '2026-07-01', 'ends_on' => '2027-06-30']);
        $semester = Semester::create(['academic_year_id' => $year->id, 'semester_code' => 'ODD', 'display_name' => 'Ganjil', 'sequence_no' => 1, 'starts_on' => '2026-07-01', 'ends_on' => '2026-12-31']);
        $subject = Subject::create(['subject_code' => 'DASH-SUBJ', 'subject_name' => 'Dashboard']);
        $teacher = Staff::create(['staff_code' => 'DASH-TEACHER', 'full_name' => 'Teacher']);
        $first = AcademicClass::create(['class_code' => 'DASH-A', 'academic_year_id' => $year->id, 'organizational_unit_id' => $unit->id, 'grade_level_id' => $level->id, 'section_code' => 'A', 'display_name' => 'Kelas A']);
        $second = AcademicClass::create(['class_code' => 'DASH-B', 'academic_year_id' => $year->id, 'organizational_unit_id' => $unit->id, 'grade_level_id' => $level->id, 'section_code' => 'B', 'display_name' => 'Kelas B']);
        $assignment = TeachingAssignment::create(['assignment_code' => 'DASH-TA', 'semester_id' => $semester->id, 'class_id' => $first->id, 'subject_id' => $subject->id, 'teacher_staff_id' => $teacher->id, 'effective_from' => '2026-07-01', 'workflow_status' => 'ACTIVE']);
        TeachingAssignment::create(['assignment_code' => 'DASH-TA-B', 'semester_id' => $semester->id, 'class_id' => $second->id, 'subject_id' => $subject->id, 'teacher_staff_id' => $teacher->id, 'effective_from' => '2026-07-01', 'workflow_status' => 'ACTIVE']);
        foreach ([[$first, $assignment], [$second, TeachingAssignment::where('assignment_code', 'DASH-TA-B')->firstOrFail()]] as [$class, $classAssignment]) {
            ClassSession::create(['session_code' => 'DASH-SESSION-'.$class->class_code, 'teaching_assignment_id' => $classAssignment->id, 'class_id' => $class->id, 'subject_id' => $subject->id, 'planned_start_at' => Carbon::parse('2026-07-10 08:00:00', 'Asia/Jakarta')->utc(), 'planned_end_at' => Carbon::parse('2026-07-10 09:00:00', 'Asia/Jakarta')->utc(), 'session_source' => 'SCHEDULED', 'participant_scope' => 'FULL_CLASS', 'session_status' => 'COMPLETED']);
        }
        if ($withPilotClass) {
            $pilotYear = AcademicYear::create(['year_code' => '2026-DASH-PILOT', 'display_name' => '2026/2027 Pilot', 'starts_on' => '2026-07-01', 'ends_on' => '2027-06-30']);
            $pilotSemester = Semester::create(['academic_year_id' => $pilotYear->id, 'semester_code' => 'ODD-PILOT', 'display_name' => 'Ganjil Pilot', 'sequence_no' => 1, 'starts_on' => '2026-07-01', 'ends_on' => '2026-12-31']);
            $pilotClass = AcademicClass::create(['class_code' => 'DASH-PILOT', 'academic_year_id' => $pilotYear->id, 'organizational_unit_id' => $unit->id, 'grade_level_id' => $level->id, 'section_code' => 'P', 'display_name' => 'Kelas Pilot']);
            $pilotAssignment = TeachingAssignment::create(['assignment_code' => 'DASH-TA-PILOT', 'semester_id' => $pilotSemester->id, 'class_id' => $pilotClass->id, 'subject_id' => $subject->id, 'teacher_staff_id' => $teacher->id, 'effective_from' => '2026-07-01', 'workflow_status' => 'ACTIVE']);
            ClassSession::create(['session_code' => 'DASH-SESSION-PILOT', 'teaching_assignment_id' => $pilotAssignment->id, 'class_id' => $pilotClass->id, 'subject_id' => $subject->id, 'planned_start_at' => Carbon::parse('2026-07-10 08:00:00', 'Asia/Jakarta')->utc(), 'planned_end_at' => Carbon::parse('2026-07-10 09:00:00', 'Asia/Jakarta')->utc(), 'session_source' => 'SCHEDULED', 'participant_scope' => 'FULL_CLASS', 'session_status' => 'COMPLETED']);
        }
        $wali = User::factory()->create();
        $waka = User::factory()->create();
        $waliRole = Role::create(['code' => 'WALI_KELAS', 'name' => 'Wali']);
        $wakaRole = Role::create(['code' => 'WAKA_AKADEMIK', 'name' => 'Waka']);
        UserRoleAssignment::create(['user_id' => $wali->id, 'role_id' => $waliRole->id]);
        UserRoleAssignment::create(['user_id' => $waka->id, 'role_id' => $wakaRole->id]);
        UserStaffLink::create(['user_id' => $wali->id, 'staff_id' => $teacher->id, 'effective_from' => '2026-07-01']);
        ClassHomeroomAssignment::create(['class_id' => $first->id, 'staff_id' => $teacher->id, 'effective_from' => '2026-07-01', 'status' => 'ACTIVE']);

        return [$first, $second, $wali, $waka, $assignment];
    }

    private function participant(ClassSession $session, int $index): SessionStudentParticipant
    {
        $student = Student::create(['student_code' => "DASH-METRIC-{$session->id}-{$index}", 'full_name' => "Dashboard Metric {$session->id}-{$index}"]);

        return SessionStudentParticipant::create([
            'class_session_id' => $session->id,
            'student_id' => $student->id,
            'participant_basis' => 'ROSTER',
            'participant_status' => 'EXPECTED',
            'is_required' => true,
        ]);
    }

    private function populateSession(ClassSession $session, User $actor, int $present, int $absent, int $missing): void
    {
        $index = 0;
        foreach (array_merge(array_fill(0, $present, 'PRESENT'), array_fill(0, $absent, 'ABSENT')) as $status) {
            $participant = $this->participant($session, $index++);
            StudentAttendance::create([
                'session_student_participant_id' => $participant->id,
                'attendance_status' => $status,
                'workflow_status' => 'VALIDATED',
                'entered_by' => $actor->id,
                'entered_at' => now(),
                'finalized_by' => $actor->id,
                'finalized_at' => now(),
                'updated_by' => $actor->id,
                'updated_at' => now(),
            ]);
        }

        for ($index = $index; $index < $present + $absent + $missing; $index++) {
            $this->participant($session, $index);
        }
    }
}
