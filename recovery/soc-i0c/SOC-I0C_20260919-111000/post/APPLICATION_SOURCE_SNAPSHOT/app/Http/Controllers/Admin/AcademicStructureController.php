<?php

namespace App\Http\Controllers\Admin;

use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Models\ClassHomeroomAssignment;
use App\Domains\Academic\Models\GradeLevel;
use App\Domains\Academic\Services\AcademicAuthorizationService;
use App\Models\User;
use App\Shared\Core\Models\AcademicYear;
use App\Shared\Core\Models\OrganizationalUnit;
use App\Shared\Core\Models\Staff;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class AcademicStructureController
{
    public function index(Request $request): View
    {
        $this->authorizeAdmin($request);

        $currentAcademicYear = AcademicYear::query()->where('status', 'ACTIVE')->where('year_code', 'not like', '%-PILOT')->orderByDesc('starts_on')->first();
        $officialClassQuery = AcademicClass::query()->when($currentAcademicYear, fn ($query) => $query->where('academic_year_id', $currentAcademicYear->id));
        $officialUnitIds = (clone $officialClassQuery)->pluck('organizational_unit_id');

        return view('admin.academic.structure.index-sidebar-refined', [
            'gradeLevels' => GradeLevel::query()->with('organizationalUnit')->when($currentAcademicYear, fn ($query) => $query->whereHas('classes', fn ($classQuery) => $classQuery->where('academic_year_id', $currentAcademicYear->id)))->orderBy('sequence_no')->get(),
            'units' => OrganizationalUnit::query()->where('record_status', 'ACTIVE')->whereIn('id', $officialUnitIds)->orderBy('unit_name')->get(),
            'classes' => $officialClassQuery->with(['academicYear', 'gradeLevel', 'homeroomAssignments.staff'])->orderBy('class_code')->get(),
            'staff' => Staff::query()->where('record_status', 'ACTIVE')->where('staff_code', 'not like', 'PILOT-%')->orderBy('full_name')->get(),
            'currentAcademicYear' => $currentAcademicYear,
        ]);
    }

    public function storeGradeLevel(Request $request): RedirectResponse
    {
        $this->authorizeAdmin($request);
        $payload = $request->validate([
            'organizational_unit_id' => ['required', 'uuid', 'exists:organizational_units,id'],
            'level_code' => ['required', 'string', 'max:100'],
            'display_name' => ['required', 'string', 'max:255'],
            'sequence_no' => ['required', 'integer', 'min:1'],
        ]);
        $exists = GradeLevel::query()->where('organizational_unit_id', $payload['organizational_unit_id'])->where('level_code', $payload['level_code'])->exists();
        if ($exists) {
            return back()->withErrors(['level_code' => 'Kode tingkat sudah digunakan pada unit ini.'])->withInput();
        }
        GradeLevel::create($payload);

        return to_route('admin.academic.structure.index')->with('status', 'Tingkat berhasil ditambahkan.');
    }

    public function storeHomeroom(Request $request): RedirectResponse
    {
        $user = $this->authorizeAdmin($request);
        $this->normalizeDateInputs($request);
        $payload = $request->validate([
            'class_id' => ['required', 'uuid', 'exists:classes,id'],
            'staff_id' => ['required', 'uuid', 'exists:staff,id'],
            'effective_from' => ['required', 'date'],
            'effective_until' => ['nullable', 'date', 'after:effective_from'],
            'reason' => ['required', 'string', 'max:255'],
        ]);
        $payload += ['assigned_by_user_id' => $user->id, 'assigned_at' => now(), 'status' => 'ACTIVE', 'version_no' => 1];
        ClassHomeroomAssignment::create($payload);

        return to_route('admin.academic.structure.index')->with('status', 'Wali Kelas berhasil ditetapkan.');
    }

    public function editHomeroom(Request $request, ClassHomeroomAssignment $assignment): View
    {
        $this->authorizeAdmin($request);

        return view('admin.academic.structure.homeroom-edit-sidebar-proper', [
            'assignment' => $assignment->load('academicClass', 'staff'),
            'staff' => Staff::query()
                ->where('record_status', 'ACTIVE')
                ->where(fn ($query) => $query->where('staff_code', 'not like', 'PILOT-%')->orWhere('id', $assignment->staff_id))
                ->orderBy('full_name')
                ->get(),
        ]);
    }

    public function updateHomeroom(Request $request, ClassHomeroomAssignment $assignment): RedirectResponse
    {
        $user = $this->authorizeAdmin($request);
        $this->normalizeDateInputs($request);
        $payload = $request->validate([
            'staff_id' => ['required', 'uuid', 'exists:staff,id'],
            'effective_from' => ['required', 'date'],
            'effective_until' => ['nullable', 'date', 'after:effective_from'],
            'reason' => ['required', 'string', 'max:255'],
        ]);
        $assignment->update($payload + ['assigned_by_user_id' => $user->id, 'assigned_at' => now()]);

        return to_route('admin.academic.structure.index')->with('status', 'Wali Kelas berhasil diperbarui.');
    }

    private function normalizeDateInputs(Request $request): void
    {
        foreach (['effective_from', 'effective_until'] as $field) {
            $value = trim((string) $request->input($field, ''));
            if ($value === '') {
                continue;
            }
            try {
                $request->merge([$field => Carbon::createFromFormat('d/m/Y', $value)->format('Y-m-d')]);
            } catch (\Throwable) {
                // Keep invalid input unchanged so Laravel returns the normal validation error.
            }
        }
    }

    private function authorizeAdmin(Request $request): User
    {
        $user = $request->user();
        abort_unless($user instanceof User, 403);
        $allowed = app(AcademicAuthorizationService::class)->hasAcademicFullAuthority($user, Carbon::now());
        if (! $allowed) {
            throw new AuthorizationException('Only Waka Akademik or Super Admin may manage academic structure.');
        }

        return $user;
    }
}
