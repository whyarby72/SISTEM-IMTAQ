<?php

namespace App\Http\Controllers\Admin;

use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Services\AcademicAuthorizationService;
use App\Models\User;
use App\Shared\Core\Models\AcademicYear;
use App\Shared\Core\Models\Student;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class StudentController
{
    public function index(Request $request): View
    {
        $this->authorizeAdmin($request);

        $search = trim((string) $request->input('q', ''));
        $classId = $request->input('class_id');
        $currentAcademicYear = AcademicYear::query()->where('status', 'ACTIVE')->where('year_code', 'not like', '%-PILOT')->orderByDesc('starts_on')->first();
        $officialClassIds = AcademicClass::query()
            ->where('status', 'ACTIVE')
            ->when($currentAcademicYear, fn ($query) => $query->where('academic_year_id', $currentAcademicYear->id))
            ->pluck('id');

        $students = Student::query()
            ->with(['classEnrollments.academicClass', 'classEnrollments' => fn ($query) => $query->where('status', 'ACTIVE')->whereIn('class_id', $officialClassIds)])
            ->whereHas('classEnrollments', fn ($query) => $query
                ->where('status', 'ACTIVE')
                ->whereIn('class_id', $officialClassIds)
                ->when($classId, fn ($query) => $query->where('class_id', $classId)))
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                $query->where('full_name', 'like', "%{$search}%")
                    ->orWhere('arabic_name', 'like', "%{$search}%");
            }))
            ->orderBy('full_name')
            ->paginate(50)
            ->withQueryString();

        $classes = AcademicClass::query()
            ->whereIn('id', $officialClassIds)
            ->orderBy('display_name')
            ->get(['id', 'display_name']);

        return view('admin.academic.students.index-sidebar-proper', compact('students', 'classes', 'search', 'classId', 'currentAcademicYear'));
    }

    public function create(Request $request): View
    {
        $this->authorizeAdmin($request);
        $currentAcademicYear = AcademicYear::query()->where('status', 'ACTIVE')->where('year_code', 'not like', '%-PILOT')->orderByDesc('starts_on')->first();
        $classes = AcademicClass::query()->where('status', 'ACTIVE')->when($currentAcademicYear, fn ($query) => $query->where('academic_year_id', $currentAcademicYear->id))->orderBy('display_name')->get(['id', 'display_name']);

        return view('admin.academic.students.create-sidebar-proper', compact('classes'));
    }

    public function store(Request $request)
    {
        $this->authorizeAdmin($request);
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'arabic_name' => ['nullable', 'string', 'max:255'],
            'entry_year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'nis' => ['nullable', 'string', 'max:50', 'unique:students,nis'],
            'nisn' => ['nullable', 'string', 'max:20', 'unique:students,nisn'],
            'class_id' => ['required', 'uuid', 'exists:classes,id'],
        ]);

        $student = Student::create(collect($validated)->only(['full_name', 'arabic_name', 'entry_year', 'nis', 'nisn'])->all());
        $student->classEnrollments()->create([
            'class_id' => $validated['class_id'],
            'effective_from' => now()->startOfYear()->toDateString(),
            'status' => 'ACTIVE',
            'reason' => 'ADMIN-MASTER-SANTRI',
            'source_reference' => 'ADMIN-MASTER-SANTRI:'.$student->id,
        ]);

        return redirect()->route('admin.academic.students.index')->with('status', 'Santri baru berhasil ditambahkan.');
    }

    public function edit(Request $request, Student $student): View
    {
        $this->authorizeAdmin($request);
        $student->load('classEnrollments.academicClass');
        $currentAcademicYear = AcademicYear::query()->where('status', 'ACTIVE')->where('year_code', 'not like', '%-PILOT')->orderByDesc('starts_on')->first();
        $classes = AcademicClass::query()->where('status', 'ACTIVE')->when($currentAcademicYear, fn ($query) => $query->where('academic_year_id', $currentAcademicYear->id))->orderBy('display_name')->get(['id', 'display_name']);

        return view('admin.academic.students.edit-sidebar-proper', compact('student', 'classes'));
    }

    public function update(Request $request, Student $student)
    {
        $this->authorizeAdmin($request);

        $validated = $request->validate([
            'full_name' => ['sometimes', 'required', 'string', 'max:255'],
            'arabic_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'entry_year' => ['sometimes', 'nullable', 'integer', 'min:2000', 'max:2100'],
            'nis' => ['nullable', 'string', 'max:50', 'unique:students,nis,'.$student->id],
            'nisn' => ['nullable', 'string', 'max:20', 'unique:students,nisn,'.$student->id],
            'class_id' => ['sometimes', 'required', 'uuid', 'exists:classes,id'],
            'class_effective_from' => ['sometimes', 'required', 'date'],
        ]);

        DB::transaction(function () use ($student, $validated): void {
            $student->update([
                'full_name' => $validated['full_name'] ?? $student->full_name,
                'arabic_name' => array_key_exists('arabic_name', $validated) ? $validated['arabic_name'] : $student->arabic_name,
                'entry_year' => array_key_exists('entry_year', $validated) ? $validated['entry_year'] : $student->entry_year,
                'nis' => $validated['nis'] !== null ? trim($validated['nis']) : $student->nis,
                'nisn' => $validated['nisn'] !== null ? trim($validated['nisn']) : $student->nisn,
            ]);

            if (array_key_exists('class_id', $validated)) {
                $effectiveFrom = Carbon::parse($validated['class_effective_from'])->startOfDay();
                $current = $student->classEnrollments()->where('status', 'ACTIVE')->latest('effective_from')->first();
                if ($current?->class_id !== $validated['class_id']) {
                    if ($current && $effectiveFrom->lte($current->effective_from)) {
                        throw ValidationException::withMessages(['class_effective_from' => 'Tanggal mulai kelas baru harus setelah tanggal mulai kelas saat ini.']);
                    }
                    $current?->update(['effective_until' => $effectiveFrom->toDateString()]);
                    $student->classEnrollments()->create([
                        'class_id' => $validated['class_id'], 'effective_from' => $effectiveFrom->toDateString(),
                        'status' => 'ACTIVE', 'reason' => 'ADMIN-CLASS-CHANGE',
                        'source_reference' => 'ADMIN-CLASS-CHANGE:'.$student->id.':'.$effectiveFrom->toDateString(),
                    ]);
                }
            }
        });

        return redirect()->route('admin.academic.students.index')->with('status', 'Nomor identitas santri berhasil disimpan.');
    }

    private function authorizeAdmin(Request $request): void
    {
        $user = $request->user();
        abort_unless($user instanceof User, 403);
        $allowed = app(AcademicAuthorizationService::class)->hasAcademicFullAuthority($user, Carbon::now());
        if (! $allowed) {
            throw new AuthorizationException('Only Waka Akademik or Super Admin may view students.');
        }
    }
}
