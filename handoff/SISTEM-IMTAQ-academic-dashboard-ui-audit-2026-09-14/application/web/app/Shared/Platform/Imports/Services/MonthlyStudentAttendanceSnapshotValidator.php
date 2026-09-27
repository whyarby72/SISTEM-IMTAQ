<?php

namespace App\Shared\Platform\Imports\Services;

use Illuminate\Support\Collection;

class MonthlyStudentAttendanceSnapshotValidator
{
    /**
     * Validate a monthly student aggregate without creating rows or session facts.
     * Candidates must already be verified canonical Student/enrollment mappings.
     */
    public function validate(array $rows, Collection $students, Collection $enrollments, string $period): array
    {
        $errors = [];
        $results = [];
        $seen = [];

        foreach ($rows as $row) {
            $sourceId = (string) ($row['source_record_id'] ?? '');
            $key = $period.'|'.$sourceId;
            if ($sourceId === '' || isset($seen[$key])) {
                $errors[] = ['source_record_id' => $sourceId, 'code' => 'DUPLICATE_OR_MISSING_SOURCE_RECORD'];

                continue;
            }
            $seen[$key] = true;

            $eligible = (int) ($row['present'] ?? 0) + (int) ($row['permission'] ?? 0) + (int) ($row['sick'] ?? 0) + (int) ($row['absent'] ?? 0);
            if ((int) ($row['eligible'] ?? -1) !== $eligible) {
                $errors[] = ['source_record_id' => $sourceId, 'code' => 'ELIGIBLE_FORMULA_MISMATCH'];

                continue;
            }
            if (array_key_exists('attendance_date', $row) || array_key_exists('session_id', $row)) {
                $errors[] = ['source_record_id' => $sourceId, 'code' => 'SESSION_EXPANSION_NOT_ALLOWED'];

                continue;
            }

            $student = $students->first(fn ($candidate): bool => $candidate['name_indonesia'] === ($row['name_indonesia'] ?? null) && $candidate['name_arabic'] === ($row['name_arabic'] ?? null));
            if ($student === null) {
                $errors[] = ['source_record_id' => $sourceId, 'code' => 'CANONICAL_STUDENT_MAPPING_REQUIRED'];

                continue;
            }
            $enrollment = $enrollments->first(fn ($candidate): bool => (string) $candidate['student_id'] === (string) $student['student_id'] && $candidate['class_admin'] === ($row['class_admin'] ?? null));
            if ($enrollment === null) {
                $errors[] = ['source_record_id' => $sourceId, 'code' => 'CLASS_ENROLLMENT_MAPPING_REQUIRED'];

                continue;
            }

            $results[] = ['source_record_id' => $sourceId, 'student_id' => $student['student_id'], 'class_id' => $enrollment['class_id'], 'eligible' => $eligible];
        }

        return ['valid' => $errors === [], 'row_count' => count($rows), 'mapped_count' => count($results), 'errors' => $errors, 'rows' => $results];
    }
}
