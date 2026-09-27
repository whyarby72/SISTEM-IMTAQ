<?php

namespace App\Http\Controllers\Admin;

use App\Domains\Academic\Services\AcademicAuthorizationService;
use App\Models\User;
use App\Shared\Core\Models\Staff;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class StaffController
{
    public function index(Request $request): View
    {
        $this->authorizeAdmin($request);

        $pilotStaffCount = Staff::query()
            ->where('staff_code', 'like', 'PILOT-%')
            ->count();

        $staff = Staff::query()
            ->where('staff_code', 'not like', 'PILOT-%')
            ->orderBy('full_name')
            ->paginate(20);

        return view('admin.academic.staff.index', compact('staff', 'pilotStaffCount'));
    }

    public function create(Request $request): View
    {
        $this->authorizeAdmin($request);

        return view('admin.academic.staff.create');
    }

    public function edit(Request $request, Staff $staff): View
    {
        $this->authorizeAdmin($request);

        return view('admin.academic.staff.edit', compact('staff'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAdmin($request);
        $payload = $request->validate([
            'staff_code' => ['required', 'string', 'max:100', 'unique:staff,staff_code'],
            'full_name' => ['required', 'string', 'max:255'],
            'active_from' => ['nullable', 'date'],
            'active_until' => ['nullable', 'date', 'after:active_from'],
        ]);

        Staff::create([
            ...$payload,
            'record_status' => 'ACTIVE',
            'version_no' => 1,
        ]);

        return to_route('admin.academic.staff.index')->with('status', 'Guru/staf berhasil ditambahkan.');
    }

    public function update(Request $request, Staff $staff): RedirectResponse
    {
        $this->authorizeAdmin($request);
        $payload = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'record_status' => ['required', 'in:ACTIVE,INACTIVE'],
            'active_from' => ['nullable', 'date'],
            'active_until' => ['nullable', 'date', 'after:active_from'],
        ]);

        $staff->update($payload);

        return to_route('admin.academic.staff.index')->with('status', 'Guru/staf berhasil diperbarui.');
    }

    private function authorizeAdmin(Request $request): void
    {
        $user = $request->user();
        abort_unless($user instanceof User, 403);

        $allowed = app(AcademicAuthorizationService::class)->hasAcademicFullAuthority($user, Carbon::now());
        if (! $allowed) {
            throw new AuthorizationException('Only Waka Akademik or Super Admin may manage staff.');
        }
    }
}
