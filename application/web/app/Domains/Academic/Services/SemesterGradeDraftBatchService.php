<?php

namespace App\Domains\Academic\Services;

use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Models\Semester;
use App\Domains\Academic\Models\SemesterSubjectGrade;
use App\Domains\Academic\Models\StudentClassEnrollment;
use App\Domains\Academic\Models\Subject;
use App\Models\User;
use App\Shared\Core\Models\Student;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class SemesterGradeDraftBatchService
{
    public function __construct(
        private readonly SemesterGradeAuthorizationService $authorization,
        private readonly SemesterGradeEntryService $entryService,
    ) {}

    /**
     * @param  array<int, array{student_id:string, score:mixed, expected_version?:mixed}>  $rows
     * @return array{created_count:int, updated_count:int, cleared_count:int, noop_count:int}
     */
    public function save(
        User $actor,
        Semester $semester,
        AcademicClass $class,
        Subject $subject,
        array $rows,
    ): array {
        if ($rows === [] || count($rows) > 100) {
            throw ValidationException::withMessages(['rows' => 'Daftar nilai harus berisi 1 sampai 100 santri.']);
        }

        try {
            return DB::transaction(function () use ($actor, $semester, $class, $subject, $rows): array {
                try {
                    $assignment = $this->authorization->requireEnterDraft($actor, $semester, $class, $subject);
                } catch (InvalidArgumentException $exception) {
                    throw ValidationException::withMessages(['rows' => $exception->getMessage()]);
                }

                $normalizedRows = $this->normalizeRows($rows);
                $studentIds = array_column($normalizedRows, 'student_id');
                if (count($studentIds) !== count(array_unique($studentIds))) {
                    throw ValidationException::withMessages(['rows' => 'Setiap santri hanya boleh muncul satu kali dalam satu penyimpanan.']);
                }

                $eligibleIds = StudentClassEnrollment::query()
                    ->where('class_id', $class->id)
                    ->where('status', 'ACTIVE')
                    ->whereDate('effective_from', '<=', $semester->ends_on->toDateString())
                    ->where(fn ($query) => $query->whereNull('effective_until')->orWhereDate('effective_until', '>', $semester->starts_on->toDateString()))
                    ->whereIn('student_id', $studentIds)
                    ->pluck('student_id')
                    ->unique()
                    ->values();

                if ($eligibleIds->count() !== count($studentIds)) {
                    throw ValidationException::withMessages(['rows' => 'Terdapat santri di luar enrollment kelas dan semester terpilih.']);
                }

                $students = Student::query()->whereIn('id', $studentIds)->get()->keyBy('id');
                if ($students->count() !== count($studentIds)) {
                    throw ValidationException::withMessages(['rows' => 'Identitas santri tidak ditemukan.']);
                }

                $existing = SemesterSubjectGrade::query()
                    ->where('semester_id', $semester->id)
                    ->where('subject_id', $subject->id)
                    ->whereIn('student_id', $studentIds)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('student_id');

                foreach ($normalizedRows as $row) {
                    $grade = $existing->get($row['student_id']);
                    if ($grade === null && $row['expected_version'] !== null) {
                        throw ValidationException::withMessages(['rows' => 'Versi harus kosong untuk nilai baru.']);
                    }
                    if ($grade !== null) {
                        if ($grade->workflow_status !== 'DRAFT') {
                            throw ValidationException::withMessages(['rows' => 'Nilai CHECKED atau LOCKED tidak dapat diubah melalui Simpan Draft.']);
                        }
                        if ($row['expected_version'] === null) {
                            throw ValidationException::withMessages(['rows' => 'Versi wajib dikirim untuk nilai DRAFT yang sudah ada.']);
                        }
                        if ($grade->version_no !== $row['expected_version']) {
                            throw ValidationException::withMessages(['rows' => 'Nilai telah berubah. Muat ulang halaman sebelum menyimpan kembali.']);
                        }
                    }
                }

                $summary = ['created_count' => 0, 'updated_count' => 0, 'cleared_count' => 0, 'noop_count' => 0];

                foreach ($normalizedRows as $row) {
                    /** @var SemesterSubjectGrade|null $grade */
                    $grade = $existing->get($row['student_id']);
                    if ($grade === null && $row['score'] === null) {
                        $summary['noop_count']++;

                        continue;
                    }
                    if ($grade !== null && $grade->score === null && $row['score'] === null) {
                        $summary['noop_count']++;

                        continue;
                    }

                    $canonicalScore = $row['score'] === null ? null : number_format($row['score'], 2, '.', '');
                    $isCanonicalNoop = $grade !== null
                        && $grade->score === $canonicalScore
                        && $grade->grade_source === 'DIRECT_ENTRY'
                        && $grade->source_teaching_assignment_id === $assignment->id
                        && $grade->responsible_staff_id === $assignment->teacher_staff_id;
                    if ($isCanonicalNoop) {
                        $summary['noop_count']++;

                        continue;
                    }

                    $this->entryService->save(
                        $students->get($row['student_id']),
                        $semester,
                        $subject,
                        $actor,
                        [
                            'score' => $row['score'],
                            'grade_source' => 'DIRECT_ENTRY',
                            'source_teaching_assignment_id' => $assignment->id,
                            'responsible_staff_id' => $assignment->teacher_staff_id,
                        ],
                        $row['expected_version'],
                    );

                    if ($grade === null) {
                        $summary['created_count']++;
                    } elseif ($row['score'] === null) {
                        $summary['cleared_count']++;
                    } else {
                        $summary['updated_count']++;
                    }
                }

                return $summary;
            });
        } catch (QueryException $exception) {
            if ((string) $exception->getCode() === '23505') {
                throw ValidationException::withMessages(['rows' => 'Nilai dibuat oleh proses lain. Muat ulang halaman sebelum menyimpan kembali.']);
            }

            throw $exception;
        }
    }

    /**
     * @param  array<int, array{student_id:string, score:mixed, expected_version?:mixed}>  $rows
     * @return array<int, array{student_id:string, score:?float, expected_version:?int}>
     */
    private function normalizeRows(array $rows): array
    {
        return array_map(function (array $row): array {
            $studentId = (string) ($row['student_id'] ?? '');
            $score = $row['score'] ?? null;
            $score = $score === '' ? null : $score;

            if ($studentId === '') {
                throw ValidationException::withMessages(['rows' => 'Student_ID wajib diisi.']);
            }
            if ($score !== null && (! is_numeric($score) || (float) $score < 0 || (float) $score > 100)) {
                throw ValidationException::withMessages(['rows' => 'Nilai harus berada pada rentang 0 sampai 100.']);
            }

            $expectedVersion = $row['expected_version'] ?? null;
            if ($expectedVersion !== null && (! is_numeric($expectedVersion) || (int) $expectedVersion < 1)) {
                throw ValidationException::withMessages(['rows' => 'Versi nilai tidak valid.']);
            }

            return [
                'student_id' => $studentId,
                'score' => $score === null ? null : (float) $score,
                'expected_version' => $expectedVersion === null ? null : (int) $expectedVersion,
            ];
        }, $rows);
    }
}
