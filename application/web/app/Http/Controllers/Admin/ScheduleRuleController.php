<?php

namespace App\Http\Controllers\Admin;

use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Models\ScheduleRule;
use App\Domains\Academic\Models\Semester;
use App\Domains\Academic\Models\Subject;
use App\Domains\Academic\Models\TeachingAssignment;
use App\Domains\Academic\Services\AcademicAuthorizationService;
use App\Domains\Academic\Services\ClassSessionGenerator;
use App\Domains\Academic\Services\ScheduleRuleArchiveService;
use App\Domains\Academic\Services\ScheduleRuleRevisionService;
use App\Domains\Academic\Services\TeacherSchedulePublicationService;
use App\Models\User;
use App\Shared\Core\Models\Staff;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ScheduleRuleController
{
    public function index(Request $request): View
    {
        $this->authorizeAdmin($request);
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'class_id' => ['nullable', 'uuid'],
            'subject_id' => ['nullable', 'uuid'],
            'teacher_id' => ['nullable', 'uuid'],
            'semester_id' => ['nullable', 'uuid'],
            'weekday' => ['nullable', 'integer', 'in:1,2,3,4,5,6,7'],
            'status' => ['nullable', 'string', 'in:DRAFT,ACTIVE,VALIDATED,APPROVED,PUBLISHED,ARCHIVED'],
            'view' => ['nullable', 'in:list,weekly'],
        ]);
        $ruleQuery = ScheduleRule::query()
            ->with(['teachingAssignment.academicClass.academicYear', 'teachingAssignment.subject', 'teachingAssignment.teacher'])
            ->whereHas('teachingAssignment.academicClass.academicYear', fn ($query) => $query->where('year_code', 'not like', '%-PILOT'))
            ->whereDoesntHave('teachingAssignment.teacher', fn ($query) => $query->where('staff_code', 'like', 'PILOT-%'))
            ->when(($filters['status'] ?? null) !== 'ARCHIVED', fn ($query) => $query->where('workflow_status', '!=', 'ARCHIVED'))
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where(function ($searchQuery) use ($search): void {
                $searchQuery->whereHas('teachingAssignment', fn ($assignment) => $assignment->where('assignment_code', 'ilike', '%'.$search.'%'))
                    ->orWhereHas('teachingAssignment.academicClass', fn ($class) => $class->where('display_name', 'ilike', '%'.$search.'%'))
                    ->orWhereHas('teachingAssignment.subject', fn ($subject) => $subject->where('subject_name', 'ilike', '%'.$search.'%'))
                    ->orWhereHas('teachingAssignment.teacher', fn ($teacher) => $teacher->where('full_name', 'ilike', '%'.$search.'%'));
            }))
            ->when($filters['class_id'] ?? null, fn ($query, $classId) => $query->whereHas('teachingAssignment', fn ($assignment) => $assignment->where('class_id', $classId)))
            ->when($filters['subject_id'] ?? null, fn ($query, $subjectId) => $query->whereHas('teachingAssignment', fn ($assignment) => $assignment->where('subject_id', $subjectId)))
            ->when($filters['teacher_id'] ?? null, fn ($query, $teacherId) => $query->whereHas('teachingAssignment', fn ($assignment) => $assignment->where('teacher_staff_id', $teacherId)))
            ->when($filters['semester_id'] ?? null, fn ($query, $semesterId) => $query->whereHas('teachingAssignment', fn ($assignment) => $assignment->where('semester_id', $semesterId)))
            ->when($filters['weekday'] ?? null, fn ($query, $weekday) => $query->where('weekday', $weekday))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('workflow_status', $status))
            ->orderByRaw('CASE weekday WHEN 6 THEN 1 WHEN 7 THEN 2 WHEN 1 THEN 3 WHEN 2 THEN 4 WHEN 3 THEN 5 WHEN 4 THEN 6 ELSE 7 END')
            ->orderBy('start_time')->orderBy('effective_from');

        $weeklyRules = (clone $ruleQuery)->get();
        $toMinutes = static function ($time): int {
            [$hours, $minutes] = array_map('intval', explode(':', substr((string) $time, 0, 5)));

            return ($hours * 60) + $minutes;
        };
        $weeklyTimes = $weeklyRules->flatMap(fn ($rule) => [$toMinutes($rule->start_time), $toMinutes($rule->end_time)]);
        $timeStart = $weeklyTimes->isEmpty() ? 7 * 60 : max(0, $weeklyTimes->min() - 30);
        $timeEnd = $weeklyTimes->isEmpty() ? 17 * 60 : min(24 * 60, $weeklyTimes->max() + 30);
        $timeSpan = max(60, $timeEnd - $timeStart);
        $rules = ($filters['view'] ?? 'list') === 'weekly'
            ? $weeklyRules
            : $ruleQuery->paginate(20)->withQueryString();

        $officialSemester = Semester::query()->where('semester_code', 'S1-2026-2027')->first();
        $officialStatuses = $officialSemester
            ? ScheduleRule::query()->whereHas('teachingAssignment', fn ($query) => $query->where('semester_id', $officialSemester->id))->pluck('workflow_status')->unique()->values()->all()
            : [];

        return view('admin.academic.schedules.index', [
            'rules' => $rules,
            'weeklyRules' => $weeklyRules,
            'timeStart' => $timeStart,
            'timeEnd' => $timeEnd,
            'timeSpan' => $timeSpan,
            'officialSemester' => $officialSemester,
            'officialStatuses' => $officialStatuses,
            'filters' => $filters,
            'viewMode' => $filters['view'] ?? 'list',
            'classes' => AcademicClass::query()->where('status', 'ACTIVE')->whereHas('academicYear', fn ($query) => $query->where('year_code', 'not like', '%-PILOT'))->orderBy('display_name')->get(),
            'subjects' => Subject::query()->where('status', 'ACTIVE')->orderBy('subject_name')->get(),
            'teachers' => Staff::query()->where('record_status', 'ACTIVE')->where('staff_code', 'not like', 'PILOT-%')->whereHas('teachingAssignments.scheduleRules')->orderBy('full_name')->get(),
            'semesters' => Semester::query()->where('status', 'ACTIVE')->whereHas('academicYear', fn ($query) => $query->where('year_code', 'not like', '%-PILOT'))->with('academicYear')->orderBy('sequence_no')->orderBy('starts_on')->get(),
        ]);
    }

    public function validateOfficial(Request $request, TeacherSchedulePublicationService $publication): RedirectResponse
    {
        $user = $this->authorizeAdmin($request);
        $semester = Semester::query()->where('semester_code', 'S1-2026-2027')->firstOrFail();
        $count = $publication->validate($semester, $user);

        return to_route('admin.academic.schedules.index')->with('status', $count.' jadwal berhasil divalidasi.');
    }

    public function publishOfficial(Request $request, TeacherSchedulePublicationService $publication): RedirectResponse
    {
        $user = $this->authorizeAdmin($request);
        $semester = Semester::query()->where('semester_code', 'S1-2026-2027')->firstOrFail();
        $count = $publication->publish($semester, $user);

        return to_route('admin.academic.schedules.index')->with('status', $count.' jadwal berhasil diterbitkan.');
    }

    public function create(Request $request): View
    {
        $this->authorizeAdmin($request);

        return view('admin.academic.schedules.create', [
            'semesters' => Semester::query()->where('status', 'ACTIVE')->whereHas('academicYear', fn ($query) => $query->where('year_code', 'not like', '%-PILOT'))->with('academicYear')->orderBy('sequence_no')->orderBy('starts_on')->get(),
            'classes' => AcademicClass::query()->where('status', 'ACTIVE')->whereHas('academicYear', fn ($query) => $query->where('year_code', 'not like', '%-PILOT'))->orderBy('display_name')->get(),
            'subjects' => Subject::query()->where('status', 'ACTIVE')->orderBy('subject_name')->get(),
            'teachers' => Staff::query()->where('record_status', 'ACTIVE')->where('staff_code', 'not like', 'PILOT-%')->orderBy('full_name')->get(),
            'assignments' => TeachingAssignment::query()->with(['academicClass', 'subject', 'teacher', 'semester'])->whereIn('workflow_status', ['ACTIVE', 'VALIDATED', 'APPROVED', 'PUBLISHED'])
                ->whereHas('academicClass.academicYear', fn ($query) => $query->where('year_code', 'not like', '%-PILOT'))
                ->where(function ($query): void {
                    $query->whereDoesntHave('scheduleRules')
                        ->orWhereHas('scheduleRules', fn ($rules) => $rules->where('workflow_status', '!=', 'ARCHIVED'));
                })
                ->orderByDesc('effective_from')->orderBy('assignment_code')->get()
                ->unique(fn (TeachingAssignment $assignment): string => implode('|', [
                    $assignment->class_id,
                    $assignment->subject_id,
                    $assignment->teacher_staff_id,
                ]))
                ->values(),
        ]);
    }

    public function createTeachingAssignment(Request $request): View
    {
        $this->authorizeAdmin($request);

        return view('admin.academic.teaching-assignments.create', [
            'semesters' => Semester::query()->where('status', 'ACTIVE')->whereHas('academicYear', fn ($query) => $query->where('year_code', 'not like', '%-PILOT'))->with('academicYear')->orderBy('sequence_no')->orderBy('starts_on')->get(),
            'classes' => AcademicClass::query()->where('status', 'ACTIVE')->whereHas('academicYear', fn ($query) => $query->where('year_code', 'not like', '%-PILOT'))->orderBy('display_name')->get(),
            'subjects' => Subject::query()->where('status', 'ACTIVE')->orderBy('subject_name')->get(),
            'teachers' => Staff::query()->where('record_status', 'ACTIVE')->where('staff_code', 'not like', 'PILOT-%')->orderBy('full_name')->get(),
        ]);
    }

    public function storeTeachingAssignment(Request $request): RedirectResponse
    {
        $user = $this->authorizeAdmin($request);
        $payload = $request->validate([
            'semester_id' => ['required', 'uuid', 'exists:semesters,id'],
            'class_id' => ['required', 'uuid', 'exists:classes,id'],
            'subject_id' => ['required', 'uuid', 'exists:subjects,id'],
            'teacher_staff_id' => ['required', 'uuid', 'exists:staff,id'],
            'effective_from' => ['required', 'date'],
            'effective_until' => ['nullable', 'date', 'after:effective_from'],
        ]);
        $official = AcademicClass::query()->whereKey($payload['class_id'])
            ->where('status', 'ACTIVE')->whereHas('academicYear', fn ($query) => $query->where('year_code', 'not like', '%-PILOT'))->exists();
        abort_unless($official, 404);
        abort_unless(Subject::query()->whereKey($payload['subject_id'])->where('status', 'ACTIVE')->exists(), 404);
        abort_unless(Staff::query()->whereKey($payload['teacher_staff_id'])->where('record_status', 'ACTIVE')->where('staff_code', 'not like', 'PILOT-%')->exists(), 404);

        $assignment = TeachingAssignment::create([
            'assignment_code' => 'TA-MANUAL-'.str()->upper(str()->random(10)),
            'semester_id' => $payload['semester_id'],
            'class_id' => $payload['class_id'],
            'subject_id' => $payload['subject_id'],
            'teacher_staff_id' => $payload['teacher_staff_id'],
            'effective_from' => $payload['effective_from'],
            'effective_until' => $payload['effective_until'] ?? null,
            'workflow_status' => 'APPROVED',
            'source_reference' => 'ADMIN-MANUAL',
            'created_by_user_id' => $user->id,
            'version_no' => 1,
        ]);

        return to_route('admin.academic.schedules.create')->with('status', 'Penugasan mengajar berhasil dibuat. Silakan pilih penugasan tersebut untuk membuat jadwal.');
    }

    public function edit(Request $request, ScheduleRule $schedule): View
    {
        $this->authorizeAdmin($request);

        return view('admin.academic.schedules.edit-sidebar-proper', [
            'schedule' => $schedule->load(['teachingAssignment.teacher', 'teachingAssignment.academicClass', 'teachingAssignment.subject', 'weekNumbers']),
            'teachers' => Staff::query()->where('record_status', 'ACTIVE')->where('staff_code', 'not like', 'PILOT-%')->orderBy('full_name')->get(),
            'subjects' => Subject::query()->where('status', 'ACTIVE')->orderBy('subject_name')->get(),
        ]);
    }

    public function store(Request $request, ClassSessionGenerator $generator): RedirectResponse
    {
        $user = $this->authorizeAdmin($request);
        $payload = $request->validate([
            'teaching_assignment_id' => ['nullable', 'uuid', 'exists:teaching_assignments,id', 'required_without_all:semester_id,class_id,subject_id,teacher_staff_id'],
            'semester_id' => ['nullable', 'uuid', 'exists:semesters,id'],
            'class_id' => ['nullable', 'uuid', 'exists:classes,id'],
            'subject_id' => ['nullable', 'uuid', 'exists:subjects,id'],
            'teacher_staff_id' => ['nullable', 'uuid', 'exists:staff,id'],
            'weekday' => ['required', 'integer', 'between:1,7'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'recurrence_type' => ['required', 'in:EVERY_WEEK,ODD_WEEK,EVEN_WEEK,WEEK_OF_MONTH'],
            'week_numbers' => ['nullable', 'array'],
            'week_numbers.*' => ['integer', 'between:1,5'],
            'effective_from' => ['required', 'date'],
            'effective_until' => ['required', 'date'],
        ]);

        if (empty($payload['teaching_assignment_id'])) {
            $payload = array_merge($payload, $request->validate([
                'semester_id' => ['required', 'uuid', 'exists:semesters,id'],
                'class_id' => ['required', 'uuid', 'exists:classes,id'],
                'subject_id' => ['required', 'uuid', 'exists:subjects,id'],
                'teacher_staff_id' => ['required', 'uuid', 'exists:staff,id'],
            ]));
            abort_unless(Semester::query()->whereKey($payload['semester_id'])->where('status', 'ACTIVE')->exists(), 404);
            abort_unless(AcademicClass::query()->whereKey($payload['class_id'])->where('status', 'ACTIVE')->whereHas('academicYear', fn ($query) => $query->where('year_code', 'not like', '%-PILOT'))->exists(), 404);
            abort_unless(Subject::query()->whereKey($payload['subject_id'])->where('status', 'ACTIVE')->exists(), 404);
            abort_unless(Staff::query()->whereKey($payload['teacher_staff_id'])->where('record_status', 'ACTIVE')->where('staff_code', 'not like', 'PILOT-%')->exists(), 404);

            $assignment = TeachingAssignment::query()
                ->where('semester_id', $payload['semester_id'])
                ->where('class_id', $payload['class_id'])
                ->where('subject_id', $payload['subject_id'])
                ->where('teacher_staff_id', $payload['teacher_staff_id'])
                ->whereIn('workflow_status', ['ACTIVE', 'VALIDATED', 'APPROVED', 'PUBLISHED'])
                ->orderByDesc('effective_from')
                ->first();

            if (! $assignment) {
                $assignment = TeachingAssignment::create([
                    'assignment_code' => 'TA-MANUAL-'.str()->upper(str()->random(10)),
                    'semester_id' => $payload['semester_id'],
                    'class_id' => $payload['class_id'],
                    'subject_id' => $payload['subject_id'],
                    'teacher_staff_id' => $payload['teacher_staff_id'],
                    'effective_from' => $payload['effective_from'],
                    'effective_until' => $payload['effective_until'],
                    'workflow_status' => 'APPROVED',
                    'source_reference' => 'ADMIN-MANUAL',
                    'created_by_user_id' => $user->id,
                    'version_no' => 1,
                ]);
            }

            $payload['teaching_assignment_id'] = $assignment->id;
        }

        $assignmentIsOfficial = TeachingAssignment::query()->whereKey($payload['teaching_assignment_id'])
            ->whereHas('academicClass.academicYear', fn ($query) => $query->where('year_code', 'not like', '%-PILOT'))
            ->exists();
        abort_unless($assignmentIsOfficial, 404);

        $rule = ScheduleRule::create([
            ...collect($payload)->except('week_numbers')->all(),
            'workflow_status' => 'APPROVED',
            'version_no' => 1,
        ]);
        $rule->weekNumbers()->createMany(array_map(
            static fn (int $week): array => ['week_no' => $week],
            array_values(array_unique(array_map('intval', $payload['week_numbers'] ?? [])))
        ));
        $generator->generate($rule, $payload['effective_from'], $payload['effective_until']);

        return to_route('admin.academic.schedules.index')->with('status', 'Jadwal berhasil ditambahkan dan sesi dibuat.');
    }

    public function update(Request $request, ScheduleRule $schedule, ScheduleRuleRevisionService $revisionService, ClassSessionGenerator $generator): RedirectResponse
    {
        $user = $this->authorizeAdmin($request);
        $hasSessions = $schedule->sessions()->exists();
        foreach (['effective_from', 'revision_from', 'effective_until'] as $dateField) {
            if ($request->filled($dateField)) {
                $request->merge([$dateField => $this->normalizeDateInput($request->input($dateField))]);
            }
        }
        if ($hasSessions && ! $request->filled('revision_from')) {
            throw ValidationException::withMessages(['schedule' => 'Jadwal yang sudah memiliki sesi tidak dapat diedit. Buat aturan jadwal baru agar histori sesi tetap aman.']);
        }
        $rules = [
            'weekday' => ['required', 'integer', 'between:1,7'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'recurrence_type' => ['required', 'in:EVERY_WEEK,ODD_WEEK,EVEN_WEEK,WEEK_OF_MONTH'],
            'week_numbers' => ['nullable', 'array'],
            'week_numbers.*' => ['integer', 'between:1,5'],
            'effective_until' => ['required', 'date', 'after:effective_from'],
        ];
        if (! $hasSessions) {
            $rules['effective_from'] = ['required', 'date'];
            $rules['effective_until'][] = 'after:effective_from';
        } else {
            $rules['effective_until'][] = 'after:'.$schedule->effective_from->toDateString();
            $rules['teacher_staff_id'] = ['required', 'uuid', 'exists:staff,id'];
            $rules['subject_id'] = ['required', 'uuid', 'exists:subjects,id'];
        }
        $payload = $request->validate($rules);

        if ($hasSessions) {
            $teacher = Staff::query()->whereKey($payload['teacher_staff_id'])->where('record_status', 'ACTIVE')->where('staff_code', 'not like', 'PILOT-%')->first();
            abort_unless($teacher !== null, 404);
            $subject = Subject::query()->whereKey($payload['subject_id'])->where('status', 'ACTIVE')->first();
            abort_unless($subject !== null, 404);
            $request->validate([
                'revision_from' => ['required', 'date', 'after_or_equal:'.$schedule->effective_from->toDateString(), 'before_or_equal:effective_until'],
                'revision_reason' => ['required', 'string', 'max:500'],
            ]);
            $revisionService->revise($schedule, [...$payload, 'revision_from' => $request->input('revision_from'), 'teacher_staff_id' => $teacher->id, 'subject_id' => $subject->id], $user->id, $request->input('revision_reason'), $generator);

            return to_route('admin.academic.schedules.index')->with('status', 'Revisi jadwal berhasil dibuat mulai '.$request->input('revision_from').'. Sesi historis tetap aman.');
        }

        $schedule->update(collect($payload)->except('week_numbers')->all());
        $schedule->weekNumbers()->delete();
        $schedule->weekNumbers()->createMany(array_map(
            static fn (int $week): array => ['week_no' => $week],
            array_values(array_unique(array_map('intval', $payload['week_numbers'] ?? [])))
        ));

        return to_route('admin.academic.schedules.index')->with('status', 'Jadwal berhasil diperbarui.');
    }

    public function destroy(Request $request, ScheduleRule $schedule): RedirectResponse
    {
        $this->authorizeAdmin($request);
        if ($schedule->sessions()->exists()) {
            throw ValidationException::withMessages(['schedule' => 'Jadwal yang sudah memiliki sesi tidak dapat dihapus. Gunakan Revisi Jadwal agar histori tetap aman.']);
        }

        $schedule->delete();

        return to_route('admin.academic.schedules.index')->with('status', 'Jadwal berhasil dihapus karena belum memiliki sesi.');
    }

    public function archive(Request $request, ScheduleRule $schedule, ScheduleRuleArchiveService $archiveService): RedirectResponse
    {
        $user = $this->authorizeAdmin($request);
        $payload = $request->validate(['archive_reason' => ['required', 'string', 'max:500']]);
        $result = $archiveService->archive($schedule, $user->id, $payload['archive_reason']);

        return to_route('admin.academic.schedules.index')->with('status', 'Jadwal diarsipkan. '.$result['cancelled'].' sesi kosong dibatalkan; '.$result['preserved'].' sesi berhistori dipertahankan.');
    }

    private function authorizeAdmin(Request $request): User
    {
        $user = $request->user();
        abort_unless($user instanceof User, 403);
        $allowed = app(AcademicAuthorizationService::class)->hasAcademicFullAuthority($user, Carbon::now());
        if (! $allowed) {
            throw new AuthorizationException('Only Waka Akademik or Super Admin may manage schedules.');
        }

        return $user;
    }

    private function normalizeDateInput(string $value): string
    {
        $value = trim($value);
        try {
            if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $value) === 1) {
                return Carbon::createFromFormat('d/m/Y', $value)->format('Y-m-d');
            }

            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable) {
            return $value;
        }
    }
}
