<?php

namespace App\Http\Controllers\Academic;

use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Models\Semester;
use App\Domains\Academic\Models\Subject;
use App\Domains\Academic\Services\SemesterGradeAuthorizationService;
use App\Domains\Academic\Services\SemesterGradeWorkspaceService;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SemesterGradeController
{
    public function index(Request $request, SemesterGradeAuthorizationService $authorization, SemesterGradeWorkspaceService $workspace): View
    {
        $request->validate([
            'semester_id' => ['nullable', 'uuid'],
            'class_id' => ['nullable', 'uuid'],
            'subject_id' => ['nullable', 'uuid'],
        ]);
        abort_unless($request->user() instanceof User, 403);

        $actor = $request->user();
        $semester = $request->filled('semester_id') ? Semester::query()->findOrFail($request->string('semester_id')->toString()) : null;
        $class = $request->filled('class_id') ? AcademicClass::query()->findOrFail($request->string('class_id')->toString()) : null;
        $subject = $request->filled('subject_id') ? Subject::query()->findOrFail($request->string('subject_id')->toString()) : null;

        abort_if(($class !== null || $subject !== null) && $semester === null, 422, 'Semester wajib dipilih terlebih dahulu.');
        abort_if($subject !== null && $class === null, 422, 'Kelas wajib dipilih terlebih dahulu.');
        if ($semester !== null && $class !== null) {
            abort_unless($authorization->visibleClasses($actor, $semester)->contains('id', $class->id), 403);
        }
        if ($semester !== null && $class !== null && $subject !== null) {
            abort_unless($authorization->visibleSubjects($actor, $semester, $class)->contains('id', $subject->id), 403);
        }

        $semesters = $authorization->visibleSemesters($actor);
        $classes = $semester ? $authorization->visibleClasses($actor, $semester) : collect();
        $subjects = $semester && $class ? $authorization->visibleSubjects($actor, $semester, $class) : collect();
        $gradeWorkspace = $semester && $class && $subject
            ? $workspace->forSelection($actor, $semester, $class, $subject)
            : $workspace->emptyState($actor, $semester, $class);

        return view('academic.grades.index', compact('semesters', 'classes', 'subjects', 'semester', 'class', 'subject', 'gradeWorkspace'));
    }
}
