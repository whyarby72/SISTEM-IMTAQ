<?php

namespace App\Http\Controllers\Admin;

use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Models\GradeLevel;
use App\Domains\Academic\Services\AcademicAuthorizationService;
use App\Models\User;
use App\Shared\Core\Models\AcademicYear;
use App\Shared\Core\Models\OrganizationalUnit;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class AcademicClassController
{
    public function index(Request $request): View
    {
        $this->authorizeAdmin($request);

        $currentAcademicYear = AcademicYear::query()
            ->where('status', 'ACTIVE')
            ->where('year_code', 'not like', '%-PILOT')
            ->orderByDesc('starts_on')
            ->first();

        $classes = AcademicClass::query()
            ->with(['academicYear', 'gradeLevel'])
            ->when($currentAcademicYear, fn ($query) => $query->where('academic_year_id', $currentAcademicYear->id))
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('admin.academic.classes.index-sidebar-proper', compact('classes', 'currentAcademicYear'));
    }

    public function create(Request $request): View
    {
        $this->authorizeAdmin($request);

        return view('admin.academic.classes.create-sidebar-proper', [
            'academicYears' => AcademicYear::query()->where('status', 'ACTIVE')->orderByDesc('starts_on')->get(),
            'organizationalUnits' => OrganizationalUnit::query()->where('record_status', 'ACTIVE')->orderBy('unit_name')->get(),
            'gradeLevels' => GradeLevel::query()->where('status', 'ACTIVE')->orderBy('sequence_no')->get(),
        ]);
    }

    public function edit(Request $request, AcademicClass $class): View
    {
        $this->authorizeAdmin($request);

        return view('admin.academic.classes.edit-sidebar-proper', compact('class'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $this->authorizeAdmin($request);
        $payload = $request->validate([
            'class_code' => ['required', 'string', 'max:100', 'unique:classes,class_code'],
            'academic_year_id' => ['required', 'uuid', 'exists:academic_years,id'],
            'organizational_unit_id' => ['required', 'uuid', 'exists:organizational_units,id'],
            'grade_level_id' => ['required', 'uuid', 'exists:grade_levels,id'],
            'section_code' => ['required', 'string', 'max:50'],
            'display_name' => ['required', 'string', 'max:255'],
        ]);

        AcademicClass::create([
            ...$payload,
            'status' => 'ACTIVE',
            'version_no' => 1,
        ]);

        return to_route('admin.academic.classes.index')->with('status', 'Kelas berhasil ditambahkan.');
    }

    public function update(Request $request, AcademicClass $class): RedirectResponse
    {
        $this->authorizeAdmin($request);
        $payload = $request->validate([
            'display_name' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:ACTIVE,INACTIVE'],
        ]);

        $class->update($payload);

        return to_route('admin.academic.classes.index')->with('status', 'Kelas berhasil diperbarui.');
    }

    private function authorizeAdmin(Request $request): User
    {
        $user = $request->user();
        abort_unless($user instanceof User, 403);

        $allowed = app(AcademicAuthorizationService::class)->hasAcademicFullAuthority($user, Carbon::now());
        if (! $allowed) {
            throw new AuthorizationException('Only Waka Akademik or Super Admin may manage classes.');
        }

        return $user;
    }
}
