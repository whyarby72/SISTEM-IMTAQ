<?php

namespace App\Domains\Academic\AI;

use App\Domains\Academic\AI\Contracts\ResolveStudentRequest;
use App\Shared\Core\Models\Student;

final class StudentIdentityResolver
{
    public function resolve(ResolveStudentRequest $request): array
    {
        $students = Student::query()
            ->with(['classEnrollments' => fn ($query) => $query->where('status', 'ACTIVE')->with('academicClass')])
            ->where(function ($query) use ($request): void {
                $term = '%'.mb_strtolower($request->query).'%';
                $query->whereRaw('LOWER(full_name) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(COALESCE(arabic_name, \'\')) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(COALESCE(nickname, \'\')) LIKE ?', [$term]);
            })
            ->when($request->classId, fn ($query) => $query->whereHas('classEnrollments', fn ($enrollment) => $enrollment
                ->where('status', 'ACTIVE')->where('class_id', $request->classId)))
            ->orderBy('full_name')
            ->limit(25)
            ->get();

        if ($students->isEmpty()) {
            return ['status' => 'NOT_FOUND', 'student' => null, 'candidates' => []];
        }

        if ($students->count() > 1) {
            return [
                'status' => 'AMBIGUOUS',
                'student' => null,
                'candidates' => $students->map(fn (Student $student): array => $this->candidate($student))->values()->all(),
            ];
        }

        return ['status' => 'RESOLVED', 'student' => $this->candidate($students->first()), 'candidates' => []];
    }

    private function candidate(Student $student): array
    {
        return [
            'student_id' => (string) $student->id,
            'display_name' => $student->full_name,
            'class_context' => $student->classEnrollments->map(fn ($enrollment): array => [
                'class_id' => (string) $enrollment->class_id,
                'class_name' => $enrollment->academicClass?->display_name,
                'effective_from' => $enrollment->effective_from?->toDateString(),
                'effective_until' => $enrollment->effective_until?->toDateString(),
            ])->values()->all(),
        ];
    }
}
