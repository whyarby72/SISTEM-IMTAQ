<?php

namespace App\Shared\Platform\Imports\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class MonthlyStudentAttendanceSnapshotMappingService
{
    public function __construct(private readonly MonthlyStudentAttendanceSnapshotValidator $validator) {}

    /**
     * Read-only mapping dry-run. It never writes staging or production data.
     */
    public function dryRun(string $period): array
    {
        $periodDate = CarbonImmutable::createFromFormat('!Y-m', $period);
        $rows = DB::table('staging_imtaq_attendance_july_2026 as attendance')
            ->join('staging_imtaq_students_july_2026 as roster', 'roster.source_record_id', '=', 'attendance.source_record_id')
            ->where('attendance.period', $period)
            ->orderBy('attendance.source_record_id')
            ->get()
            ->map(fn ($row): array => (array) $row)
            ->all();

        $students = DB::table('students')
            ->select('id as student_id', 'full_name as name_indonesia', 'arabic_name as name_arabic')
            ->get()
            ->map(fn ($row): array => (array) $row);

        $enrollments = DB::table('student_class_enrollments as enrollment')
            ->join('classes as class', 'class.id', '=', 'enrollment.class_id')
            ->select('enrollment.student_id', 'enrollment.class_id', 'class.class_code as class_admin')
            ->where('enrollment.effective_from', '<=', $periodDate->endOfMonth()->toDateString())
            ->where(fn ($query) => $query->whereNull('enrollment.effective_until')->orWhere('enrollment.effective_until', '>=', $periodDate->startOfMonth()->toDateString()))
            ->get()
            ->map(fn ($row): array => (array) $row);

        return $this->validator->validate($rows, $students, $enrollments, $period);
    }
}
