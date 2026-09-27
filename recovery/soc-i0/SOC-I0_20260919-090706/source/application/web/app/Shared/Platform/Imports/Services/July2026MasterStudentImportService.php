<?php

namespace App\Shared\Platform\Imports\Services;

use App\Domains\Academic\Models\AcademicClass;
use App\Domains\Academic\Models\StudentClassEnrollment;
use App\Shared\Core\Models\Student;
use Illuminate\Support\Facades\DB;

class July2026MasterStudentImportService
{
    public function import(string $path): array
    {
        $handle = fopen($path, 'rb');
        if ($handle === false) {
            throw new \InvalidArgumentException('Roster file cannot be opened.');
        }

        $header = fgetcsv($handle);
        if ($header === false || array_map(fn ($value) => trim((string) $value, "\xEF\xBB\xBF \t\r\n"), $header) !== ['No', 'Nama Indonesia', 'Nama Arab', 'Kelas']) {
            fclose($handle);
            throw new \InvalidArgumentException('Roster header does not match the approved four-column format.');
        }

        $rows = [];
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) !== 4) {
                fclose($handle);
                throw new \InvalidArgumentException('Roster contains a malformed row.');
            }
            $rows[] = ['no' => (int) trim($row[0]), 'name_indonesia' => $row[1], 'name_arabic' => $row[2], 'class_code' => trim($row[3])];
        }
        fclose($handle);

        if (count($rows) !== 84 || collect($rows)->pluck('no')->sort()->values()->all() !== range(1, 84)) {
            throw new \InvalidArgumentException('Roster must contain exactly No 1 through 84.');
        }

        $expectedCounts = ['1' => 20, '2A' => 19, '2B' => 10, '3A' => 15, '3B' => 20];
        if (collect($rows)->countBy('class_code')->all() !== $expectedCounts) {
            throw new \InvalidArgumentException('Roster class distribution does not match the approved distribution.');
        }

        return DB::transaction(function () use ($rows, $expectedCounts): array {
            $classes = AcademicClass::query()->whereIn('class_code', array_keys($expectedCounts))->where('status', 'ACTIVE')->get()->keyBy('class_code');
            if ($classes->count() !== count($expectedCounts)) {
                throw new \InvalidArgumentException('All five target classes must exist and be active before import.');
            }

            $created = 0;
            $existing = 0;
            foreach ($rows as $row) {
                $sourceReference = 'IMTAQ-MASTER-2026-07:JUL26-'.str_pad((string) $row['no'], 3, '0', STR_PAD_LEFT);
                $enrollment = StudentClassEnrollment::query()->where('source_reference', $sourceReference)->first();
                if ($enrollment !== null) {
                    $student = $enrollment->student;
                    if ($student->full_name !== $row['name_indonesia'] || $student->arabic_name !== $row['name_arabic'] || $enrollment->class_id !== $classes[$row['class_code']]->id) {
                        throw new \InvalidArgumentException("Existing source reference conflicts: {$sourceReference}.");
                    }
                    $existing++;

                    continue;
                }

                $student = Student::create([
                    'student_code' => null,
                    'full_name' => $row['name_indonesia'],
                    'arabic_name' => $row['name_arabic'],
                    'entry_year' => 2026 - ((int) preg_replace('/\D/', '', $row['class_code']) - 1),
                ]);
                StudentClassEnrollment::create([
                    'student_id' => $student->id,
                    'class_id' => $classes[$row['class_code']]->id,
                    'effective_from' => '2026-07-01',
                    'status' => 'ACTIVE',
                    'reason' => 'IMPORT-MASTER-SANTRI-JULI-2026',
                    'source_reference' => $sourceReference,
                ]);
                $created++;
            }

            return ['supplied' => count($rows), 'created' => $created, 'already_imported' => $existing, 'student_id_generated' => 0, 'attendance_imported' => 0];
        });
    }
}
