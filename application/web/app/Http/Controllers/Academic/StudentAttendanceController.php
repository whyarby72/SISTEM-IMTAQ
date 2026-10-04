<?php

namespace App\Http\Controllers\Academic;

use App\Domains\Academic\Exceptions\ScheduleConflictException;
use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Models\ClassSession;
use App\Domains\Academic\Models\StudentAttendance;
use App\Domains\Academic\Services\AcademicAuthorizationService;
use App\Domains\Academic\Services\AcademicClassScopeResolver;
use App\Domains\Academic\Services\AttendanceScopeLockEvaluator;
use App\Domains\Academic\Services\CancellationService;
use App\Domains\Academic\Services\CanonicalSessionOccurrenceService;
use App\Domains\Academic\Services\JointAttendanceRosterBreakdownService;
use App\Domains\Academic\Services\PostLockAttendanceCorrectionService;
use App\Domains\Academic\Services\SessionAttendanceScopeResolver;
use App\Domains\Academic\Services\SessionOccurrenceAuthorizationService;
use App\Domains\Academic\Services\SessionOccurrenceFeatureGate;
use App\Domains\Academic\Services\SessionOccurrenceWorkflowService;
use App\Domains\Academic\Services\SessionParticipantSnapshotter;
use App\Domains\Academic\Services\StudentAttendanceCompletenessChecker;
use App\Domains\Academic\Services\StudentAttendanceDraftService;
use App\Domains\Academic\Services\StudentAttendanceFinalizer;
use App\Domains\Academic\Services\StudentSessionGroomingNoteService;
use App\Domains\Academic\Services\SubstitutionService;
use App\Domains\Academic\Services\TeacherAttendanceService;
use App\Domains\Academic\Services\TeacherParticipationRecorder;
use App\Domains\Academic\Services\WaliKelasContextResolver;
use App\Models\User;
use App\Shared\Core\Models\Staff;
use App\Shared\Platform\Audit\Models\CorrectionRequest;
use App\Shared\Platform\Reports\CompletedAttendanceSessionExportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StudentAttendanceController
{
    public function __construct(private readonly AcademicClassScopeResolver $classScope) {}

    public function show(ClassSession $session, Request $request, WaliKelasContextResolver $resolver, TeacherParticipationRecorder $teacherParticipationRecorder, JointAttendanceRosterBreakdownService $rosterBreakdown, SessionAttendanceScopeResolver $attendanceScopeResolver, StudentAttendanceCompletenessChecker $completenessChecker, AttendanceScopeLockEvaluator $lockEvaluator, SessionParticipantSnapshotter $snapshotter, SessionOccurrenceFeatureGate $occurrenceGate, SessionOccurrenceAuthorizationService $occurrenceAuthorization): View
    {
        $user = $this->user($request);
        $staff = $resolver->resolve($user, $session);
        $session->load(['academicClass', 'teachingAssignment.teacher', 'teachingAssignment.subject', 'scheduleChanges.originalTeacher', 'scheduleChanges.replacementTeacher', 'teacherParticipations.teacher']);
        if ($session->session_status !== 'CANCELLED') {
            $snapshotter->ensure($session);
        }
        if (! in_array($session->session_status, ['CANCELLED', 'COMPLETED', 'RESCHEDULED'], true) && $session->teacherParticipations->isEmpty()) {
            $teacherParticipationRecorder->ensurePrimary($session);
            $session->load('teacherParticipations.teacher');
        }
        $homeroomStaff = $resolver->homeroomStaff($session);
        $participants = $session->studentParticipants()->with(['student.classEnrollments', 'attendance', 'groomingNote'])->orderBy('id')->get();
        $attendanceScopeData = $attendanceScopeResolver->resolve($user, $session, $participants);
        $participants = $attendanceScopeResolver->filterParticipants($participants, $attendanceScopeData);
        $attendanceScope = $rosterBreakdown->for($session, $participants);
        if ($attendanceScopeData['mode'] === 'WALI_CLASS_PARTITION') {
            $scopeClassIds = $attendanceScopeData['effective_class_ids'];
            $attendanceClasses = $attendanceScope['classes'];
            $attendanceScope['classes'] = $attendanceClasses->filter(fn ($class) => in_array((string) $class->id, $scopeClassIds, true))->values();
            $attendanceScope['class_label'] = $attendanceScope['classes']->pluck('display_name')->implode(' + ');
            $attendanceScope['counts'] = $attendanceScope['counts']->filter(function (array $item) use ($attendanceClasses, $scopeClassIds): bool {
                return in_array((string) $attendanceClasses->firstWhere('display_name', $item['label'])?->id, $scopeClassIds, true);
            })->values();
        }
        $completeness = $completenessChecker->checkForScope($session, $attendanceScopeData);
        $replacementTeachers = Staff::query()
            ->where('record_status', 'ACTIVE')
            ->where('staff_code', 'not like', 'PILOT-%')
            ->whereKeyNot($session->teachingAssignment?->teacher_staff_id)
            ->orderBy('full_name')
            ->get();
        $canCancel = $session->session_status === 'PLANNED' && ! $participants->contains(fn ($participant) => $participant->attendance !== null);
        $readOnly = $session->session_status === 'COMPLETED';
        $correctionCandidates = $participants->filter(fn ($participant) => $participant->attendance?->workflow_status === 'VALIDATED');
        $lockClassIds = $attendanceScopeData['mode'] === 'WALI_CLASS_PARTITION'
            ? $attendanceScopeData['effective_class_ids']
            : [(string) $session->class_id];
        $scopeIsLocked = $lockEvaluator->isLocked($lockClassIds, $session->planned_start_at);
        $canSaveDraft = ! $readOnly && $session->session_status !== 'CANCELLED' && $session->session_status !== 'RESCHEDULED' && ! $scopeIsLocked;
        $canRequestCorrection = $readOnly && $scopeIsLocked && $correctionCandidates->isNotEmpty();
        $canRecordTeacherAttendance = ! $readOnly && $session->session_status !== 'CANCELLED' && $session->teacherParticipations->isNotEmpty();
        $occurrenceFeatureEnabled = $occurrenceGate->enabled();
        $occurrenceCanonicalRegime = $occurrenceFeatureEnabled && $occurrenceGate->isCanonical($session);
        $occurrenceHistory = $occurrenceCanonicalRegime ? $session->occurrenceVersions()->with('recordedBy')->orderBy('version_no')->get() : collect();
        $effectiveOccurrence = $occurrenceCanonicalRegime ? $session->effectiveOccurrenceVersion : null;
        $canManageOccurrence = $occurrenceCanonicalRegime && $occurrenceAuthorization->canManageRoutine($user, $session, 'HELD');
        $canCorrectOccurrence = $occurrenceCanonicalRegime && $occurrenceAuthorization->hasAcademicFullAuthority($user, $session->planned_start_at) && $effectiveOccurrence !== null;

        return view('academic.attendance.show', compact('session', 'participants', 'staff', 'homeroomStaff', 'replacementTeachers', 'canCancel', 'readOnly', 'correctionCandidates', 'canRequestCorrection', 'canRecordTeacherAttendance', 'canSaveDraft', 'scopeIsLocked', 'completeness', 'attendanceScope', 'occurrenceFeatureEnabled', 'occurrenceCanonicalRegime', 'occurrenceHistory', 'effectiveOccurrence', 'canManageOccurrence', 'canCorrectOccurrence'));
    }

    public function recordOccurrence(
        ClassSession $session,
        Request $request,
        SessionOccurrenceFeatureGate $gate,
        SessionOccurrenceAuthorizationService $authorization,
        CanonicalSessionOccurrenceService $occurrence,
        SessionOccurrenceWorkflowService $workflow,
    ): RedirectResponse {
        $gate->requireEnabled();
        $user = $this->user($request);
        $gate->requireCanonicalWrite($session);
        $payload = $request->validate([
            'action' => ['required', 'string', 'in:HELD,PARTIAL_HELD,CANCELLED,RESCHEDULED'],
            'reason' => ['nullable', 'string', 'max:1000'],
            'partial_reason' => ['nullable', 'string', 'max:1000'],
            'new_start_at' => ['nullable', 'date'],
            'new_end_at' => ['nullable', 'date', 'after:new_start_at'],
        ]);
        $status = $payload['action'] === 'PARTIAL_HELD' ? 'HELD' : $payload['action'];
        $authorization->requireRoutine($user, $session, $status);

        if ($status === 'RESCHEDULED' && (blank($payload['new_start_at'] ?? null) || blank($payload['new_end_at'] ?? null))) {
            return back()->withErrors(['occurrence' => 'Tanggal mulai dan selesai jadwal ulang wajib diisi.']);
        }

        try {
            if ($status === 'CANCELLED') {
                $workflow->cancel($session, $user, $payload['reason'] ?? '');
            } elseif ($status === 'RESCHEDULED') {
                $workflow->reschedule($session, $user, $payload['new_start_at'] ?? '', $payload['new_end_at'] ?? '', $payload['reason'] ?? '');
            } else {
                $occurrence->record($session, $user, $status, [
                    'reason' => $payload['reason'] ?? null,
                    'is_partial' => $payload['action'] === 'PARTIAL_HELD',
                    'partial_reason' => $payload['partial_reason'] ?? null,
                ]);
            }
        } catch (InvalidArgumentException $exception) {
            return back()->withErrors(['occurrence' => $exception->getMessage()]);
        }

        return to_route('academic.attendance.show', $session)->with('status', 'Kejadian sesi berhasil dicatat dalam riwayat kanonik.');
    }

    public function correctOccurrence(
        ClassSession $session,
        Request $request,
        SessionOccurrenceFeatureGate $gate,
        SessionOccurrenceAuthorizationService $authorization,
        CanonicalSessionOccurrenceService $occurrence,
    ): RedirectResponse {
        $gate->requireEnabled();
        $user = $this->user($request);
        $gate->requireCanonicalWrite($session);
        $authorization->requireCorrection($user, $session);
        $payload = $request->validate([
            'occurrence_status' => ['required', 'string', 'in:SCHEDULED,HELD,CANCELLED,RESCHEDULED'],
            'reason' => ['required', 'string', 'max:1000'],
            'partial_reason' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $occurrence->correctEffectiveOccurrence($session, $user, $payload['occurrence_status'], $payload['reason'], [
                'is_partial' => $payload['occurrence_status'] === 'HELD' && filled($payload['partial_reason'] ?? null),
                'partial_reason' => $payload['partial_reason'] ?? null,
            ]);
        } catch (InvalidArgumentException $exception) {
            return back()->withErrors(['occurrence' => $exception->getMessage()]);
        }

        return to_route('academic.attendance.show', $session)->with('status', 'Koreksi kejadian sesi berhasil ditambahkan sebagai versi baru.');
    }

    public function recordTeacherAttendance(ClassSession $session, Request $request, WaliKelasContextResolver $resolver, TeacherAttendanceService $teacherAttendanceService): RedirectResponse
    {
        $user = $this->user($request);
        $inputter = $resolver->resolve($user, $session);
        $canManageAllClasses = $resolver->canManageAllClasses($user, $session->planned_start_at->toDateString());
        abort_unless(! in_array($session->session_status, ['CANCELLED', 'COMPLETED', 'RESCHEDULED'], true), 422, 'Status sesi tidak dapat menerima perubahan kehadiran guru.');
        $payload = $request->validate([
            'participation_id' => ['required', 'uuid', 'exists:session_teacher_participations,id'],
            'attendance_status' => ['required', 'string', 'in:PRESENT,ABSENT,SICK,IZIN,OTHER'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);
        $participation = $session->teacherParticipations()->whereKey($payload['participation_id'])->firstOrFail();
        if ($payload['attendance_status'] !== 'PRESENT' && blank($payload['reason'])) {
            return back()->withErrors(['teacher_attendance' => 'Alasan wajib diisi untuk status guru selain Hadir.']);
        }
        try {
            $teacherAttendanceService->record($session, $participation, $inputter, $payload['attendance_status'], $user->id, $payload['reason'] ?? null, $canManageAllClasses);
        } catch (InvalidArgumentException $exception) {
            return back()->withErrors(['teacher_attendance' => $exception->getMessage()]);
        }

        return redirect()->to(route('academic.attendance.show', $session).'#rekap-guru')->with('status', 'Kehadiran guru berhasil dicatat dan masuk rekap evaluasi.');
    }

    public function requestCorrection(ClassSession $session, Request $request, SessionAttendanceScopeResolver $attendanceScopeResolver, PostLockAttendanceCorrectionService $correctionService): RedirectResponse
    {
        $validated = $request->validate([
            'attendance_id' => ['required', 'uuid', 'exists:student_attendance,id'],
            'expected_version' => ['required', 'integer', 'min:1'],
            'attendance_status' => ['required', 'string', 'in:PRESENT,ABSENT,SICK,IZIN,LATE,EXCUSED'],
            'reason' => ['required', 'string', 'max:1000'],
        ]);
        $attendance = StudentAttendance::query()->with('participant.classSession')->findOrFail($validated['attendance_id']);
        abort_unless((string) $attendance->participant?->class_session_id === (string) $session->id, 404);
        $scope = $attendanceScopeResolver->resolveForUser($this->user($request), $session);
        $attendanceScopeResolver->assertParticipantAuthorized($scope, $attendance->participant);

        try {
            $correctionService->submit($attendance, $this->user($request), (int) $validated['expected_version'], $validated['reason'], ['attendance_status' => $validated['attendance_status']]);
        } catch (InvalidArgumentException $exception) {
            return back()->withErrors(['correction' => $exception->getMessage()]);
        }

        return to_route('academic.attendance.show', $session)->with('status', 'Permintaan koreksi berhasil diajukan untuk ditinjau Waka Akademik.');
    }

    public function directCorrection(ClassSession $session, Request $request, PostLockAttendanceCorrectionService $correctionService): RedirectResponse
    {
        $validated = $request->validate([
            'attendance_id' => ['required', 'uuid', 'exists:student_attendance,id'],
            'expected_version' => ['required', 'integer', 'min:1'],
            'attendance_status' => ['required', 'string', 'in:PRESENT,ABSENT,SICK,IZIN,LATE,EXCUSED'],
            'reason' => ['required', 'string', 'max:1000'],
        ]);
        $attendance = StudentAttendance::query()->with('participant.classSession')->findOrFail($validated['attendance_id']);
        abort_unless((string) $attendance->participant?->class_session_id === (string) $session->id, 404);

        try {
            $correctionService->wakaOverride($attendance, $this->user($request), (int) $validated['expected_version'], $validated['reason'], ['attendance_status' => $validated['attendance_status']]);
        } catch (InvalidArgumentException $exception) {
            return back()->withErrors(['correction' => $exception->getMessage()]);
        }

        return back()->with('status', 'Koreksi kehadiran berhasil diterapkan oleh Waka Akademik dan tercatat dalam audit.');
    }

    public function substitute(
        ClassSession $session,
        Request $request,
        WaliKelasContextResolver $resolver,
        SubstitutionService $substitutionService,
        SessionOccurrenceFeatureGate $occurrenceGate,
        SessionOccurrenceAuthorizationService $occurrenceAuthorization,
    ): RedirectResponse {
        $user = $this->user($request);
        $allowScopedWali = $occurrenceGate->enabled();
        abort_unless($allowScopedWali ? $occurrenceAuthorization->canManageSubstitution($user, $session) : $resolver->canManageAllClasses($user, $session->planned_start_at->toDateString()), 403, 'Anda tidak memiliki kewenangan mengganti guru pada sesi ini.');
        if ($allowScopedWali && $occurrenceAuthorization->isJoint($session) && ! $resolver->canManageAllClasses($user, $session->planned_start_at->toDateString())) {
            abort(403, 'Substitusi sesi gabungan memerlukan Waka Akademik.');
        }
        $payload = $request->validate([
            'replacement_teacher_id' => ['nullable', 'uuid', 'exists:staff,id'],
            'reason' => ['required', 'string', 'max:1000'],
        ]);
        $replacementTeacher = blank($payload['replacement_teacher_id'] ?? null)
            ? $resolver->homeroomStaff($session)
            : Staff::query()->whereKey($payload['replacement_teacher_id'])->where('record_status', 'ACTIVE')->first();
        abort_unless($replacementTeacher !== null, 422, 'Wali Kelas aktif belum tersedia sebagai guru pengganti.');

        try {
            $substitutionService->applySubstitution($session->load('teachingAssignment'), $replacementTeacher, $user->id, $payload['reason'], $allowScopedWali);
        } catch (ScheduleConflictException $exception) {
            return back()->withErrors(['replacement_teacher_id' => 'Guru pengganti memiliki benturan jadwal pada waktu sesi ini.']);
        }

        return redirect()->to(route('academic.attendance.show', $session).'#rekap-guru')->with('status', 'Guru pengganti berhasil dicatat untuk sesi ini.');
    }

    public function snapshotParticipants(
        ClassSession $session,
        Request $request,
        WaliKelasContextResolver $resolver,
        SessionParticipantSnapshotter $snapshotter,
    ): RedirectResponse {
        $user = $this->user($request);
        abort_unless($session->session_status === 'PLANNED' && $resolver->canManageAllClasses($user, $session->planned_start_at->toDateString()), 403);
        $participants = $snapshotter->snapshot($session);

        return to_route('academic.attendance.show', $session)->with('status', $participants->count().' peserta berhasil dibuat dari roster kelas pada tanggal sesi.');
    }

    public function cancel(
        ClassSession $session,
        Request $request,
        WaliKelasContextResolver $resolver,
        CancellationService $cancellationService,
        SessionOccurrenceFeatureGate $occurrenceGate,
        SessionOccurrenceAuthorizationService $occurrenceAuthorization,
        SessionOccurrenceWorkflowService $occurrenceWorkflow,
    ): RedirectResponse {
        $user = $this->user($request);
        $payload = $request->validate(['reason' => ['required', 'string', 'max:1000']]);

        try {
            if ($occurrenceGate->enabled()) {
                $occurrenceGate->requireCanonicalWrite($session);
                $occurrenceAuthorization->requireRoutine($user, $session, 'CANCELLED');
                $occurrenceWorkflow->cancel($session, $user, $payload['reason']);
            } else {
                $resolver->resolve($user, $session);
                $cancellationService->apply($session, $user->id, $payload['reason']);
            }
        } catch (InvalidArgumentException) {
            return back()->withErrors(['cancellation' => 'Sesi tidak dapat dibatalkan karena sudah memiliki data kehadiran, statusnya tidak aktif, atau sudah diproses.']);
        }

        return to_route('academic.attendance.show', $session)->with('status', 'Sesi berhasil dibatalkan dan tidak akan dihitung sebagai sesi akademik yang berlangsung.');
    }

    public function review(ClassSession $session, Request $request, AcademicAuthorizationService $authorization, JointAttendanceRosterBreakdownService $rosterBreakdown): View
    {
        $user = $this->user($request);
        $allowed = $authorization->hasAcademicFullAuthority($user, Carbon::parse($session->planned_start_at));
        abort_unless($allowed, 403, 'Hanya Waka Akademik atau Super Admin yang dapat memeriksa hasil sesi.');

        abort_unless($session->session_status === 'COMPLETED', 404, 'Hasil sesi belum disahkan.');

        $participants = $session->studentParticipants()->with(['student.classEnrollments', 'attendance', 'groomingNote'])->orderBy('id')->get();
        $attendanceScope = $rosterBreakdown->for($session, $participants);
        $staff = $user->staffLink?->staff;
        $readOnly = true;
        $correctionCandidates = $participants->filter(fn ($participant) => $participant->attendance?->workflow_status === 'VALIDATED');
        $canRequestCorrection = $correctionCandidates->isNotEmpty();
        $correctionRoute = route('academic.attendance.corrections.direct', $session);

        return view('academic.attendance.show', compact('session', 'participants', 'staff', 'readOnly', 'correctionCandidates', 'canRequestCorrection', 'correctionRoute', 'attendanceScope'));
    }

    public function exportReviewDetailCsv(ClassSession $session, Request $request, CompletedAttendanceSessionExportService $exporter): StreamedResponse
    {
        $this->authorizeReviewer($this->user($request));
        abort_unless($session->session_status === 'COMPLETED', 404, 'Hasil sesi belum disahkan.');
        $participants = $session->studentParticipants()->with(['student', 'attendance'])->orderBy('id')->get();

        return response()->streamDownload(fn () => print $exporter->detailCsv($session, $participants), 'detail-kehadiran-'.$session->session_code.'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function exportReviewDetailPdf(ClassSession $session, Request $request, CompletedAttendanceSessionExportService $exporter): Response
    {
        $this->authorizeReviewer($this->user($request));
        abort_unless($session->session_status === 'COMPLETED', 404, 'Hasil sesi belum disahkan.');
        $participants = $session->studentParticipants()->with(['student', 'attendance'])->orderBy('id')->get();

        return response($exporter->detailPdf($session, $participants), 200, ['Content-Type' => 'application/pdf', 'Content-Disposition' => 'attachment; filename="detail-kehadiran-'.$session->session_code.'.pdf"']);
    }

    public function reviews(Request $request): View
    {
        $user = $this->user($request);
        $this->authorizeReviewer($user);
        $filters = $request->validate([
            'class_id' => ['nullable', 'string'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ]);

        $sessions = $this->reviewQuery($filters)
            ->latest('planned_start_at')
            ->paginate(10)
            ->withQueryString();

        return view('academic.attendance.reviews', [
            'sessions' => $sessions,
            'classes' => AcademicClass::query()
                ->where('status', 'ACTIVE')
                ->whereHas('academicYear', fn ($query) => $query->where('year_code', 'not like', '%-PILOT'))
                ->orderBy('display_name')
                ->get(),
            'filters' => $filters,
            'correctionRequests' => CorrectionRequest::query()->where('correction_type', 'POST_LOCK_ATTENDANCE')->whereIn('status', ['PENDING', 'APPROVED'])->latest()->limit(50)->get(),
        ]);
    }

    public function reviewCorrection(CorrectionRequest $correctionRequest, Request $request, PostLockAttendanceCorrectionService $correctionService): RedirectResponse
    {
        $validated = $request->validate(['approve' => ['required', 'boolean'], 'rejection_reason' => ['nullable', 'string', 'max:1000']]);
        try {
            $correctionService->review($correctionRequest, $this->user($request), (bool) $validated['approve'], $validated['rejection_reason'] ?? null);
        } catch (InvalidArgumentException $exception) {
            return back()->withErrors(['correction' => $exception->getMessage()]);
        }

        return back()->with('status', $validated['approve'] ? 'Koreksi disetujui. Silakan terapkan koreksi.' : 'Koreksi ditolak.');
    }

    public function applyCorrection(CorrectionRequest $correctionRequest, Request $request, PostLockAttendanceCorrectionService $correctionService): RedirectResponse
    {
        try {
            $correctionService->applyApproved($correctionRequest, $this->user($request));
        } catch (InvalidArgumentException $exception) {
            return back()->withErrors(['correction' => $exception->getMessage()]);
        }

        return back()->with('status', 'Koreksi berhasil diterapkan dan tercatat dalam audit.');
    }

    public function exportReviewsCsv(Request $request, CompletedAttendanceSessionExportService $exporter): StreamedResponse
    {
        $this->authorizeReviewer($this->user($request));
        $filters = $request->validate(['class_id' => ['nullable', 'string'], 'from' => ['nullable', 'date'], 'to' => ['nullable', 'date']]);
        $csv = $exporter->csv($this->reviewQuery($filters)->latest('planned_start_at')->get());

        return response()->streamDownload(fn () => print $csv, 'sesi-kehadiran-disahkan.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function exportReviewsPdf(Request $request, CompletedAttendanceSessionExportService $exporter): Response
    {
        $this->authorizeReviewer($this->user($request));
        $filters = $request->validate(['class_id' => ['nullable', 'string'], 'from' => ['nullable', 'date'], 'to' => ['nullable', 'date']]);

        return response($exporter->pdf($this->reviewQuery($filters)->latest('planned_start_at')->get()), 200, ['Content-Type' => 'application/pdf', 'Content-Disposition' => 'attachment; filename="sesi-kehadiran-disahkan.pdf"']);
    }

    public function saveDraft(
        ClassSession $session,
        Request $request,
        WaliKelasContextResolver $resolver,
        SessionAttendanceScopeResolver $attendanceScopeResolver,
        StudentAttendanceDraftService $draftService,
        StudentSessionGroomingNoteService $groomingNoteService,
    ): RedirectResponse {
        $user = $this->user($request);
        $staff = $resolver->resolve($user, $session);
        $canManageAllClasses = $resolver->canManageAllClasses($user, $session->planned_start_at->toDateString());
        $scopeParticipants = $session->studentParticipants()->with('student.classEnrollments')->get();
        $attendanceScope = $attendanceScopeResolver->resolve($user, $session, $scopeParticipants);
        $payload = $request->validate([
            'participants' => ['required', 'array'],
            'participants.*.attendance_status' => ['nullable', 'string', 'in:PRESENT,ABSENT,SICK,IZIN,LATE,EXCUSED'],
            'participants.*.notes' => ['nullable', 'string', 'max:1000'],
            'participants.*.grooming_note' => ['nullable', 'string', 'max:1000'],
            'participants.*.discipline_code' => ['nullable', 'string', 'in:RAPI,TIDAK_BERSERAGAM,SERAGAM_TIDAK_LENGKAP,TIDAK_MEMBAWA_BUKU,TIDAK_BERPECI,CATATAN_TAMBAHAN'],
        ]);

        try {
            foreach ($payload['participants'] as $participantId => $attributes) {
                abort_unless($canManageAllClasses || in_array((string) $participantId, array_map('strval', $attendanceScope['authorized_participant_ids']), true), 403);
                $participant = $session->studentParticipants()->whereKey($participantId)->firstOrFail();
                if ($participant->attendance()->exists() || $this->hasMeaningfulInput($attributes['attendance_status'] ?? null) || $this->hasMeaningfulInput($attributes['notes'] ?? null)) {
                    $draftService->save($session, $participant, $staff, $user->id, $attributes, $canManageAllClasses, $attendanceScope['mode'] === 'WALI_CLASS_PARTITION');
                }
                if ($participant->groomingNote()->exists() || $this->hasMeaningfulInput($attributes['discipline_code'] ?? null) || $this->hasMeaningfulInput($attributes['grooming_note'] ?? null)) {
                    $groomingNoteService->save($session, $participant, $staff, $user->id, $attributes['discipline_code'] ?? null, $attributes['grooming_note'] ?? null, $canManageAllClasses, $attendanceScope['mode'] === 'WALI_CLASS_PARTITION');
                }
            }
        } catch (InvalidArgumentException $exception) {
            return to_route('academic.attendance.show', $session)->withErrors(['draft' => $exception->getMessage()]);
        }

        return to_route('academic.attendance.show', $session)->with('status', 'Data kehadiran sementara tersimpan.');
    }

    public function finalize(
        ClassSession $session,
        Request $request,
        WaliKelasContextResolver $resolver,
        SessionAttendanceScopeResolver $attendanceScopeResolver,
        StudentAttendanceFinalizer $finalizer,
        StudentAttendanceDraftService $draftService,
        StudentSessionGroomingNoteService $groomingNoteService,
    ): RedirectResponse {
        $user = $this->user($request);
        $staff = $resolver->resolve($user, $session);
        $canManageAllClasses = $resolver->canManageAllClasses($user, $session->planned_start_at->toDateString());
        $scopeParticipants = $session->studentParticipants()->with('student.classEnrollments')->get();
        $attendanceScope = $attendanceScopeResolver->resolve($user, $session, $scopeParticipants);
        abort_unless($canManageAllClasses || $attendanceScope['mode'] !== 'WALI_CLASS_PARTITION', 403, 'Finalisasi sesi gabungan menunggu kontrak scope finalisasi kelas.');
        $payload = $request->validate([
            'participants' => ['sometimes', 'array'],
            'participants.*.attendance_status' => ['nullable', 'string', 'in:PRESENT,ABSENT,SICK,IZIN,LATE,EXCUSED'],
            'participants.*.notes' => ['nullable', 'string', 'max:1000'],
            'participants.*.grooming_note' => ['nullable', 'string', 'max:1000'],
            'participants.*.discipline_code' => ['nullable', 'string', 'in:RAPI,TIDAK_BERSERAGAM,SERAGAM_TIDAK_LENGKAP,TIDAK_MEMBAWA_BUKU,TIDAK_BERPECI,CATATAN_TAMBAHAN'],
        ]);

        foreach ($payload['participants'] ?? [] as $participantId => $attributes) {
            abort_unless($canManageAllClasses || in_array((string) $participantId, array_map('strval', $attendanceScope['authorized_participant_ids']), true), 403);
            $participant = $session->studentParticipants()->whereKey($participantId)->firstOrFail();
            if ($participant->attendance()->exists() || $this->hasMeaningfulInput($attributes['attendance_status'] ?? null) || $this->hasMeaningfulInput($attributes['notes'] ?? null)) {
                $draftService->save($session, $participant, $staff, $user->id, $attributes, $canManageAllClasses);
            }
            if ($participant->groomingNote()->exists() || $this->hasMeaningfulInput($attributes['discipline_code'] ?? null) || $this->hasMeaningfulInput($attributes['grooming_note'] ?? null)) {
                $groomingNoteService->save($session, $participant, $staff, $user->id, $attributes['discipline_code'] ?? null, $attributes['grooming_note'] ?? null, $canManageAllClasses);
            }
        }
        try {
            $finalizer->finalize($session, $staff, $user->id, [], $canManageAllClasses);
        } catch (InvalidArgumentException $exception) {
            return to_route('academic.attendance.show', $session)->withErrors([
                'finalize' => match ($exception->getMessage()) {
                    'All required EXPECTED participants must have attendance status.' => 'Belum semua santri memiliki status kehadiran. Lengkapi status setiap santri terlebih dahulu.',
                    'Session has no required EXPECTED participants to finalize.' => 'Roster santri belum dibuat. Buat roster terlebih dahulu sebelum mengesahkan kehadiran.',
                    default => $exception->getMessage(),
                },
            ]);
        }

        return to_route('academic.attendance.show', $session)->with('status', 'Kehadiran berhasil disahkan.');
    }

    private function user(Request $request): User
    {
        $user = $request->user();
        abort_unless($user instanceof User, 403);

        return $user;
    }

    private function hasMeaningfulInput(mixed $value): bool
    {
        return is_string($value) ? trim($value) !== '' : $value !== null;
    }

    private function authorizeReviewer(User $user, ?AcademicAuthorizationService $authorization = null): void
    {
        $authorization ??= app(AcademicAuthorizationService::class);
        $allowed = $authorization->hasAcademicFullAuthority($user, Carbon::now());
        abort_unless($allowed, 403, 'Hanya Waka Akademik atau Super Admin yang dapat memeriksa hasil sesi.');
    }

    private function reviewQuery(array $filters)
    {
        return ClassSession::query()
            ->with(['academicClass', 'scopeGroups.academicClass', 'teachingAssignment.subject', 'studentParticipants.attendance'])
            ->where('session_status', 'COMPLETED')
            ->whereHas('academicClass.academicYear', fn ($query) => $query->where('year_code', 'not like', '%-PILOT'))
            ->whereDoesntHave('teachingAssignment', fn ($assignment) => $assignment->where('source_reference', 'SAMPLE-PILOT'))
            ->when($filters['class_id'] ?? null, fn ($query, $classId) => $this->classScope->constrainSessionQueryToClassScope($query, [(string) $classId]))
            ->when($filters['from'] ?? null, fn ($query, $from) => $query->whereDate('planned_start_at', '>=', $from))
            ->when($filters['to'] ?? null, fn ($query, $to) => $query->whereDate('planned_start_at', '<=', $to));
    }
}
