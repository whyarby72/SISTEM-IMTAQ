<?php

namespace App\Http\Controllers\Admin;

use App\Domains\Academic\Models\Subject;
use App\Domains\Academic\Services\AcademicAuthorizationService;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class SubjectController
{
    public function index(Request $request): View
    {
        $this->authorizeAdmin($request);

        return view('admin.academic.subjects.index-refined', [
            'subjects' => Subject::query()->orderBy('subject_name')->paginate(20),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorizeAdmin($request);

        return view('admin.academic.subjects.create-proper');
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $this->authorizeAdmin($request);
        $payload = $request->validate([
            'subject_code' => ['required', 'string', 'max:100', 'unique:subjects,subject_code'],
            'subject_name' => ['required', 'string', 'max:255'],
        ]);

        Subject::create([
            ...$payload,
            'status' => 'ACTIVE',
            'version_no' => 1,
        ]);

        return to_route('admin.academic.subjects.index')->with('status', 'Mata pelajaran berhasil ditambahkan.');
    }

    public function edit(Request $request, Subject $subject): View
    {
        $this->authorizeAdmin($request);

        return view('admin.academic.subjects.edit-sidebar', compact('subject'));
    }

    public function update(Request $request, Subject $subject): RedirectResponse
    {
        $this->authorizeAdmin($request);
        $payload = $request->validate([
            'subject_name' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:ACTIVE,INACTIVE'],
        ]);
        $subject->update($payload);

        return to_route('admin.academic.subjects.index')->with('status', 'Mata pelajaran berhasil diperbarui.');
    }

    private function authorizeAdmin(Request $request): User
    {
        $user = $request->user();
        abort_unless($user instanceof User, 403);
        $allowed = app(AcademicAuthorizationService::class)->hasAcademicFullAuthority($user, Carbon::now());
        if (! $allowed) {
            throw new AuthorizationException('Only Waka Akademik or Super Admin may manage subjects.');
        }

        return $user;
    }
}
